#!/usr/bin/env bash
# Roll fin.chanakalk.com back to a previous release.
#
#   deploy/rollback.sh                 roll back to the previous release (state file)
#   deploy/rollback.sh <commit>        roll back to a specific commit
#   RESTORE_DB=1 deploy/rollback.sh    also restore the most recent database backup
set -euo pipefail
cd "$(dirname "$0")/.."
. deploy/config.sh

TARGET="${1:-}"
echo "==> Rolling back $SSH_TARGET:$REMOTE_DIR ${TARGET:+to $TARGET}"
{ cat deploy/config.sh; echo; cat deploy/remote-rollback.sh; } | ssh -o ConnectTimeout=15 "$SSH_TARGET" "
    set -e
    t=\$(mktemp /tmp/fin-rollback.XXXXXX.sh)
    trap 'rm -f \"\$t\"' EXIT
    cat > \"\$t\"
    RESTORE_DB='${RESTORE_DB:-0}' BRANCH='$BRANCH' REMOTE_DIR='$REMOTE_DIR' COMPOSE_FILE='$COMPOSE_FILE' \
    STATE_DIR='$STATE_DIR' HEALTHCHECK_URL='$HEALTHCHECK_URL' \
    bash \"\$t\" '$TARGET'
"
