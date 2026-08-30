#!/usr/bin/env bash
# Quick look at what's deployed and running on the server.
#   deploy/status.sh            summary
#   deploy/status.sh logs       tail php + nginx logs (Ctrl-C to stop)
set -euo pipefail
cd "$(dirname "$0")/.."
. deploy/config.sh

what="${1:-summary}"

if [ "$what" = "logs" ]; then
    exec ssh -t -o ConnectTimeout=15 "$SSH_TARGET" \
        "cd '$REMOTE_DIR' && docker compose -f '$COMPOSE_FILE' logs --tail=100 -f php nginx"
fi

cat deploy/remote-status.sh | ssh -o ConnectTimeout=15 "$SSH_TARGET" "
    set -e
    t=\$(mktemp /tmp/fin-status.XXXXXX.sh)
    trap 'rm -f \"\$t\"' EXIT
    cat > \"\$t\"
    REMOTE_DIR='$REMOTE_DIR' COMPOSE_FILE='$COMPOSE_FILE' STATE_DIR='$STATE_DIR' BRANCH='$BRANCH' bash \"\$t\"
"
