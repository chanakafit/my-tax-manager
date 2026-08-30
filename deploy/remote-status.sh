#!/usr/bin/env bash
# Runs ON the server (piped in by deploy/status.sh). Read-only.
set -euo pipefail
exec 0</dev/null

REMOTE_DIR="${REMOTE_DIR:-/var/www/fin}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
STATE_DIR="${STATE_DIR:-/var/www/.fin-deploy}"
BRANCH="${BRANCH:-main}"
DC="docker compose -f $COMPOSE_FILE"
cd "$REMOTE_DIR"

echo "== release =="
git fetch --quiet origin "$BRANCH" || true
printf 'deployed : %s  %s\n' "$(git rev-parse --short HEAD)" "$(git log -1 --format=%s)"
printf 'origin/%s: %s\n' "$BRANCH" "$(git rev-parse --short "origin/$BRANCH")"
behind="$(git rev-list --count "HEAD..origin/$BRANCH" 2>/dev/null || echo '?')"
[ "$behind" = "0" ] && echo "status   : up to date" || echo "status   : $behind commit(s) behind origin/$BRANCH"

echo
echo "== containers =="
$DC ps

echo
echo "== last backups =="
ls -1t "$STATE_DIR"/backups/db-*.sql.gz 2>/dev/null | head -5 || echo "(none)"

echo
echo "== pending migrations =="
$DC exec -T -w /var/www/html php php yii migrate/new --interactive=0 2>/dev/null || true
