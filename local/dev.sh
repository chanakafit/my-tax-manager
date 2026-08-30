#!/usr/bin/env bash
# Local dev stack control for My Tax Manager  ->  http://fin.local:8090
#
#   local/dev.sh up            build + start, wait for DB, run migrations
#   local/dev.sh down          stop and remove containers (keeps the DB volume)
#   local/dev.sh restart       restart php + nginx
#   local/dev.sh rebuild       rebuild the php image and restart
#   local/dev.sh migrate       php yii migrate/up
#   local/dev.sh seed          php yii seed/index  (demo data)
#   local/dev.sh shell         bash in the php container
#   local/dev.sh mysql         mysql client in the db container
#   local/dev.sh logs [svc]    follow logs
#   local/dev.sh test [...]    ./vendor/bin/codecept run unit [...]
#   local/dev.sh nuke          stop + delete the DB volume (full reset)
set -euo pipefail
cd "$(dirname "$0")/.."

COMPOSE="docker compose -f docker-compose.local.yml --env-file .env.local -p fin-local"
envget() { grep -E "^$1=" .env.local 2>/dev/null | head -1 | cut -d= -f2- | sed 's/[[:space:]#].*//'; }
APP_PORT="$(envget HTTP_PORT)"; APP_PORT="${APP_PORT:-8090}"
PMA_PORT="$(envget PMA_PORT)"; PMA_PORT="${PMA_PORT:-8091}"
APP_URL="http://fin.localhost:${APP_PORT}"

need_env() {
    if [ ! -f .env.local ]; then
        cp .env.local.example .env.local
        echo "• created .env.local from .env.local.example"
    fi
}

# The php container's entrypoint execs php/post_install.sh; a CRLF shebang
# makes it "file not found". Guarantee LF on the scripts the container runs.
fix_eol() {
    for f in php/post_install.sh php/crontab php/yii; do
        [ -f "$f" ] && sed -i 's/\r$//' "$f"
    done
}

urls() {
    echo
    echo "  ✔ up"
    echo "      app         ${APP_URL}          (Chrome/Edge resolve *.localhost automatically)"
    echo "      or          http://localhost:${APP_PORT}"
    echo "      phpMyAdmin  http://localhost:${PMA_PORT}"
    echo "      login       admin / admin123"
    echo
    echo "  For a bare 'fin.local' hostname instead, add to the Windows hosts file"
    echo "  (C:\\Windows\\System32\\drivers\\etc\\hosts, as Administrator):"
    echo "      127.0.0.1 fin.local"
    echo
}

wait_for_db() {
    echo -n "• waiting for MariaDB "
    for _ in $(seq 1 60); do
        if $COMPOSE exec -T mariadb mysqladmin ping -h localhost --silent >/dev/null 2>&1; then
            echo "ready"; return 0
        fi
        echo -n "."; sleep 2
    done
    echo " timed out"; return 1
}

cmd="${1:-up}"; shift || true

case "$cmd" in
    up)
        need_env
        fix_eol
        $COMPOSE up -d --build
        wait_for_db
        # the container entrypoint (post_install.sh) runs composer + migrations on
        # boot; re-run migrate here so `up` is deterministic and the admin user
        # exists before we report success.
        $COMPOSE exec -T -w /var/www/html php sh -c \
            '[ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist --optimize-autoloader'
        $COMPOSE exec -T -w /var/www/html php php yii migrate/up --interactive=0
        $COMPOSE exec -T -w /var/www/html php php yii cache/flush-all || true
        urls
        ;;
    down)     $COMPOSE down ;;
    restart)  $COMPOSE restart php nginx ;;
    rebuild)  $COMPOSE build php && $COMPOSE up -d ;;
    migrate)  $COMPOSE exec -w /var/www/html php php yii migrate/up --interactive=0 ;;
    seed)     $COMPOSE exec -w /var/www/html php php yii "${1:-seed/index}" ;;
    shell)    $COMPOSE exec -w /var/www/html php bash ;;
    mysql)    $COMPOSE exec mariadb mysql -uroot -proot "${1:-mybs}" ;;
    logs)     $COMPOSE logs -f "${@:-php nginx}" ;;
    test)     $COMPOSE exec -w /var/www/html php ./vendor/bin/codecept run unit "$@" ;;
    ps)       $COMPOSE ps ;;

    import)   # import a .sql / .sql.gz dump, replacing the local DB
        f="${1:?usage: dev.sh import <file.sql[.gz]>}"
        [ -f "$f" ] || { echo "no such file: $f" >&2; exit 1; }
        DBNAME="$(envget MYSQL_DATABASE)"; DBNAME="${DBNAME:-mybs}"
        echo "• recreating database '$DBNAME' and importing $f"
        $COMPOSE exec -T mariadb mysql -uroot -proot \
            -e "DROP DATABASE IF EXISTS \`$DBNAME\`; CREATE DATABASE \`$DBNAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        case "$f" in
            *.gz) gunzip -c "$f" ;;
            *)    cat "$f" ;;
        esac | $COMPOSE exec -T mariadb mysql -uroot -proot "$DBNAME"
        $COMPOSE exec -T -w /var/www/html php php yii migrate/up --interactive=0 || \
            echo "  (migrate/up had nothing to do or the dump is ahead of local code)"
        $COMPOSE exec -T -w /var/www/html php php yii cache/flush-all || true
        echo "• imported. If login fails, run: ./local/dev.sh reset-admin"
        ;;

    reset-admin)  # set the 'admin' user's password back to admin123
        $COMPOSE exec -T -w /var/www/html php php -r '
            require "vendor/autoload.php"; require "vendor/yiisoft/yii2/Yii.php";
            new yii\console\Application(require "config/console.php");
            $h = Yii::$app->security->generatePasswordHash("admin123");
            $n = Yii::$app->db->createCommand()->update("mb_user", ["password_hash" => $h], ["username" => "admin"])->execute();
            echo $n ? "admin password reset to admin123\n" : "no user named admin found\n";
        '
        ;;

    pull-db)  exec "$(dirname "$0")/../deploy/pull-db.sh" ;;
    nuke)
        read -rp "Delete the local DB volume 'finlocal_db'? [y/N] " a
        [ "$a" = y ] || exit 0
        $COMPOSE down -v
        echo "• volume removed — next 'up' starts with a fresh database"
        ;;
    *) sed -n '2,20p' "$0"; exit 1 ;;
esac
