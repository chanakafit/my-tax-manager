#!/usr/bin/env bash
# Shared deploy configuration. No secrets here — this file is committed.
# Sourced by deploy.sh / rollback.sh locally, and prepended to the remote
# scripts when they are piped over SSH.

# --- SSH target ---
SSH_USER="${SSH_USER:-root}"
SSH_HOST="${SSH_HOST:-178.128.211.159}"
SSH_TARGET="${SSH_USER}@${SSH_HOST}"

# --- Server layout ---
REMOTE_DIR="${REMOTE_DIR:-/var/www/fin}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
BRANCH="${BRANCH:-main}"

# State + database backups live outside the repo tree so `git reset --hard`
# and `git clean` can never touch them.
STATE_DIR="${STATE_DIR:-/var/www/.fin-deploy}"
KEEP_BACKUPS="${KEEP_BACKUPS:-10}"

# --- Health check ---
HEALTHCHECK_URL="${HEALTHCHECK_URL:-https://fin.chanakalk.com/}"
