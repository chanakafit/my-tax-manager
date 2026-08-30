#!/usr/bin/env bash
# Runs ON the server (piped in by deploy/deploy.sh, with config.sh prepended).
# Pulls the target branch, backs up the DB, rebuilds only if infra changed,
# runs composer + migrations, clears caches, health-checks, and auto-rolls
# back (code + database) if anything fails.
#
# Env knobs: FORCE=1 (redeploy same SHA / discard local changes)
set -euo pipefail
exec 0</dev/null   # detach stdin so `docker compose exec -T` children can't drain the script

REMOTE_DIR="${REMOTE_DIR:-/var/www/fin}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
BRANCH="${BRANCH:-main}"
STATE_DIR="${STATE_DIR:-/var/www/.fin-deploy}"
KEEP_BACKUPS="${KEEP_BACKUPS:-10}"
HEALTHCHECK_URL="${HEALTHCHECK_URL:-https://fin.chanakalk.com/}"
FORCE="${FORCE:-0}"

DC="docker compose -f $COMPOSE_FILE"
PHP="$DC exec -T -w /var/www/html php"

c_blue=$'\033[1;34m'; c_yellow=$'\033[1;33m'; c_red=$'\033[1;31m'; c_off=$'\033[0m'
log()  { printf '%s[deploy]%s %s\n' "$c_blue"   "$c_off" "$*"; }
warn() { printf '%s[deploy]%s %s\n' "$c_yellow" "$c_off" "$*"; }
die()  { printf '%s[deploy] ERROR:%s %s\n' "$c_red" "$c_off" "$*" >&2; exit 1; }

cd "$REMOTE_DIR" || die "cannot cd $REMOTE_DIR"

# --- single-deploy lock ---
exec 9>"/tmp/fin-deploy.lock"
flock -n 9 || die "another deploy is already running"

# --- preflight ---
[ -f .env.prod ]        || die "missing $REMOTE_DIR/.env.prod"
command -v docker >/dev/null || die "docker not found"
$DC ps >/dev/null       || die "compose stack not reachable"
mkdir -p "$STATE_DIR/backups"

env_val() { grep -E "^$1=" .env.prod | head -1 | cut -d= -f2- | tr -d '\r' | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'\$//"; }
MYSQL_DATABASE="$(env_val MYSQL_DATABASE)"
MYSQL_ROOT_PASSWORD="$(env_val MYSQL_ROOT_PASSWORD)"
[ -n "$MYSQL_DATABASE" ]      || die "MYSQL_DATABASE not set in .env.prod"
[ -n "$MYSQL_ROOT_PASSWORD" ] || die "MYSQL_ROOT_PASSWORD not set in .env.prod"

# refuse to run over uncommitted tracked changes unless forced
dirty="$(git status --porcelain --untracked-files=no)"
if [ -n "$dirty" ] && [ "$FORCE" != "1" ]; then
    die "server working tree has local changes (FORCE=1 to discard):
$dirty"
fi

PREV_SHA="$(git rev-parse HEAD)"
git fetch --quiet origin "$BRANCH" || die "git fetch failed"
NEW_SHA="$(git rev-parse "origin/$BRANCH")"

log "current:  ${PREV_SHA:0:12}"
log "target:   ${NEW_SHA:0:12}  (origin/$BRANCH)"
if [ "$PREV_SHA" = "$NEW_SHA" ] && [ "$FORCE" != "1" ]; then
    log "already up to date — nothing to do (FORCE=1 to redeploy)"
    exit 0
fi
[ "$PREV_SHA" = "$NEW_SHA" ] || git --no-pager log --oneline "$PREV_SHA..$NEW_SHA" | sed 's/^/          /'

changed_files="$(git diff --name-only "$PREV_SHA" "$NEW_SHA" || true)"

# --- database backup (always, before touching anything) ---
BACKUP="$STATE_DIR/backups/db-$(date +%Y%m%d-%H%M%S)-${PREV_SHA:0:7}.sql.gz"
log "backing up database -> $BACKUP"
{ $DC exec -T -e MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mariadb \
    mysqldump -u root --single-transaction --quick --routines --triggers --events "$MYSQL_DATABASE" 2>/dev/null \
    | gzip > "$BACKUP"; } || true
if [ ! -s "$BACKUP" ] || ! gzip -t "$BACKUP" 2>/dev/null; then
    rm -f "$BACKUP"
    die "database backup failed or empty — aborting before any change"
fi
# prune old backups (keep newest $KEEP_BACKUPS)
{ ls -1t "$STATE_DIR"/backups/db-*.sql.gz 2>/dev/null | tail -n +"$((KEEP_BACKUPS + 1))" | xargs -r rm -f; } || true

# --- from here on, failures roll back ---
rollback() {
    set +e
    warn "deploy failed — rolling back code to ${PREV_SHA:0:12} and restoring database"
    git reset --hard "$PREV_SHA" --quiet
    gunzip -c "$BACKUP" | $DC exec -T -e MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mariadb mysql -u root "$MYSQL_DATABASE" \
        && warn "database restored from $BACKUP" \
        || warn "DB RESTORE FAILED — restore manually: gunzip -c $BACKUP | docker compose -f $COMPOSE_FILE exec -T mariadb mysql -u root -p $MYSQL_DATABASE"
    $DC up -d --force-recreate php nginx >/dev/null 2>&1
    $PHP php yii cache/flush-all >/dev/null 2>&1
    warn "rollback complete — still on ${PREV_SHA:0:12}"
    exit 1
}
trap rollback ERR

git reset --hard "$NEW_SHA" --quiet
mkdir -p "$STATE_DIR"
echo "$PREV_SHA" > "$STATE_DIR/last-release"

# --- rebuild image only when infra files changed, else just sync containers ---
if grep -qE '^(local/php/|php/crontab$|docker-compose\.prod\.yml$)' <<<"$changed_files"; then
    log "infra files changed — rebuilding php image"
    $DC build php
    $DC up -d
else
    log "no infra changes — syncing containers (code is bind-mounted, no restart needed)"
    $DC up -d
fi

# wait for php service to report running
for _ in $(seq 1 30); do
    [ "$($DC ps --format '{{.Service}} {{.State}}' | awk '$1=="php"{print $2}')" = "running" ] && break
    sleep 2
done

# --- composer, only if dependencies changed ---
if grep -qE '^php/composer\.(json|lock)$' <<<"$changed_files"; then
    log "composer dependencies changed — installing (--no-dev)"
    $PHP composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
else
    log "composer.lock unchanged — skipping composer install"
fi

# --- migrations ---
log "running database migrations"
$PHP php yii migrate/up --interactive=0

# --- caches + compiled assets ---
log "flushing caches and published asset bundles"
$PHP php yii cache/flush-all || true
$PHP sh -c 'rm -rf runtime/cache/* web/assets/* 2>/dev/null' || true

trap - ERR

# --- health check ---
log "health check: $HEALTHCHECK_URL"
code=""
for _ in $(seq 1 10); do
    code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "$HEALTHCHECK_URL" || true)"
    case "$code" in 200|302) break ;; esac
    sleep 3
done
case "$code" in
    200|302) : ;;
    *) warn "health check failed (HTTP ${code:-none})"; rollback ;;
esac

# --- container sanity ---
bad="$($DC ps --format '{{.Service}} {{.State}}' | awk '$2!="running"{print $1}')"
[ -z "$bad" ] || warn "services not running: $bad"

log "✔ deployed ${NEW_SHA:0:12}  (was ${PREV_SHA:0:12})"
log "  db backup: $BACKUP"
log "  rollback:  deploy/rollback.sh"
