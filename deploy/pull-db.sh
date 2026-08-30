#!/usr/bin/env bash
# Pull a fresh copy of the production database and load it into the local stack.
#
#   deploy/pull-db.sh              dump prod now -> db-dumps/ -> import into local 'mybs'
#   NO_IMPORT=1 deploy/pull-db.sh  only download the dump, don't touch the local DB
#
# The prod dump carries no CREATE DATABASE / USE lines, so it loads into whatever
# local database we name (prod's db is 'ktjxqmrmkw', local is 'mybs').
# After import the local login is prod's: user 'admin' / email admin@example.com,
# with prod's password — reset it locally with:
#   ./local/dev.sh reset-admin
set -euo pipefail
cd "$(dirname "$0")/.."
. deploy/config.sh

LOCAL_DC="docker compose -f docker-compose.local.yml --env-file .env.local -p fin-local"
LOCAL_DB="$(grep -E '^MYSQL_DATABASE=' .env.local 2>/dev/null | head -1 | cut -d= -f2- | sed 's/[[:space:]#].*//')"
LOCAL_DB="${LOCAL_DB:-mybs}"

mkdir -p db-dumps
OUT="db-dumps/prod-$(date +%Y%m%d-%H%M%S).sql.gz"

echo "==> Dumping production database ($SSH_TARGET)"
ssh -o ConnectTimeout=15 "$SSH_TARGET" bash -s > "$OUT" <<REMOTE
set -euo pipefail
cd "$REMOTE_DIR"
db=\$(grep -E '^MYSQL_DATABASE='       .env.prod | head -1 | cut -d= -f2- | tr -d '\r')
pw=\$(grep -E '^MYSQL_ROOT_PASSWORD=' .env.prod | head -1 | cut -d= -f2- | tr -d '\r')
docker compose -f $COMPOSE_FILE exec -T -e MYSQL_PWD="\$pw" mariadb \
    mysqldump -u root --single-transaction --quick --routines --triggers --events "\$db" 2>/dev/null | gzip
REMOTE

if [ ! -s "$OUT" ] || ! gzip -t "$OUT" 2>/dev/null; then
    rm -f "$OUT"; echo "dump failed / empty" >&2; exit 1
fi
echo "    saved $OUT  ($(du -h "$OUT" | cut -f1), $(gunzip -c "$OUT" | grep -c 'CREATE TABLE') tables)"

if [ "${NO_IMPORT:-0}" = "1" ]; then exit 0; fi

echo "==> Importing into local '$LOCAL_DB'"
"$(dirname "$0")/../local/dev.sh" import "$OUT"
