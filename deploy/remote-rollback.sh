#!/usr/bin/env bash
# Runs ON the server (piped in by deploy/rollback.sh, config.sh prepended).
# Rolls the checkout back to a previous commit. Optionally restores the DB.
#
#   arg 1        commit to roll back to (default: deploy/last-release state file)
#   RESTORE_DB=1 also restore the most recent database backup
set -euo pipefail
exec 0</dev/null   # detach stdin so `docker compose exec -T` children can't drain the script

REMOTE_DIR="${REMOTE_DIR:-/var/www/fin}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
STATE_DIR="${STATE_DIR:-/var/www/.fin-deploy}"
HEALTHCHECK_URL="${HEALTHCHECK_URL:-https://fin.chanakalk.com/}"
RESTORE_DB="${RESTORE_DB:-0}"

DC="docker compose -f $COMPOSE_FILE"
PHP="$DC exec -T -w /var/www/html php"
cd "$REMOTE_DIR"

TARGET="${1:-}"
[ -n "$TARGET" ] || TARGET="$(cat "$STATE_DIR/last-release" 2>/dev/null || true)"
[ -n "$TARGET" ] || { echo "no rollback target given and no $STATE_DIR/last-release" >&2; exit 1; }
git rev-parse --verify --quiet "$TARGET^{commit}" >/dev/null || { echo "unknown commit: $TARGET" >&2; exit 1; }

CURRENT="$(git rev-parse HEAD)"
echo "[rollback] code: ${CURRENT:0:12} -> ${TARGET:0:12}"
git reset --hard "$TARGET" --quiet

if grep -qE '^(local/php/|php/crontab|docker-compose\.prod\.yml)' <<<"$(git diff --name-only "$TARGET" "$CURRENT" || true)"; then
    echo "[rollback] infra differs — rebuilding php image"
    $DC build php
fi
$DC up -d

if [ "$RESTORE_DB" = "1" ]; then
    backup="$(ls -1t "$STATE_DIR"/backups/db-*.sql.gz 2>/dev/null | head -1 || true)"
    [ -n "$backup" ] || { echo "no database backup found in $STATE_DIR/backups" >&2; exit 1; }
    env_val() { grep -E "^$1=" .env.prod | head -1 | cut -d= -f2- | tr -d '\r' | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'\$//"; }
    db="$(env_val MYSQL_DATABASE)"; pw="$(env_val MYSQL_ROOT_PASSWORD)"
    echo "[rollback] restoring database from $backup"
    gunzip -c "$backup" | $DC exec -T -e MYSQL_PWD="$pw" mariadb mysql -u root "$db"
fi

$PHP composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true
$PHP php yii cache/flush-all || true
$PHP sh -c 'rm -rf runtime/cache/* web/assets/* 2>/dev/null' || true

code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "$HEALTHCHECK_URL" || true)"
echo "[rollback] done — now on ${TARGET:0:12}  (health: HTTP ${code:-none})"
