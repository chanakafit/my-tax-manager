#!/usr/bin/env bash
# Deploy fin.chanakalk.com from your workstation.
#
#   deploy/deploy.sh              push current branch, then deploy origin/<branch> on the server
#   PUSH=0 deploy/deploy.sh       deploy whatever is already on origin/<branch> (no local push)
#   FORCE=1 deploy/deploy.sh      redeploy same commit / discard local changes on the server
#
# Overridable: SSH_HOST, SSH_USER, REMOTE_DIR, BRANCH, HEALTHCHECK_URL (see config.sh)
set -euo pipefail
cd "$(dirname "$0")/.."
. deploy/config.sh

PUSH="${PUSH:-1}"
FORCE="${FORCE:-0}"

if [ "$PUSH" = "1" ]; then
    current="$(git rev-parse --abbrev-ref HEAD)"
    if [ "$current" != "$BRANCH" ]; then
        echo "You are on '$current', not '$BRANCH'." >&2
        echo "Switch branches, or run with PUSH=0 to deploy origin/$BRANCH as-is." >&2
        exit 1
    fi
    echo "==> Pushing $BRANCH to origin"
    git push origin "$BRANCH"
fi

echo "==> Deploying origin/$BRANCH  ->  $SSH_TARGET:$REMOTE_DIR"

# Stream config.sh + remote-deploy.sh to a temp file on the server, then run it.
# (Running via a file — not `bash -s` on stdin — keeps stdin free for the
#  `docker compose exec` calls inside the remote script.)
{ cat deploy/config.sh; echo; cat deploy/remote-deploy.sh; } | ssh -o ConnectTimeout=15 "$SSH_TARGET" "
    set -e
    t=\$(mktemp /tmp/fin-deploy.XXXXXX.sh)
    trap 'rm -f \"\$t\"' EXIT
    cat > \"\$t\"
    FORCE='$FORCE' BRANCH='$BRANCH' REMOTE_DIR='$REMOTE_DIR' COMPOSE_FILE='$COMPOSE_FILE' \
    STATE_DIR='$STATE_DIR' KEEP_BACKUPS='$KEEP_BACKUPS' HEALTHCHECK_URL='$HEALTHCHECK_URL' \
    bash \"\$t\"
"
