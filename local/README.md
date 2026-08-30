# Local development — http://fin.localhost:8090

This stack (`docker-compose.local.yml`) runs the app **alongside** any other
Docker project on the machine. Everything is namespaced `fin-local` / `finlocal-*`
and every host port is bound to `127.0.0.1` and overridable in `.env.local`:

| Service     | URL / port                                        |
|-------------|---------------------------------------------------|
| App         | http://fin.localhost:8090  (or http://localhost:8090) |
| phpMyAdmin  | http://localhost:8091                             |
| MariaDB     | `127.0.0.1:3307` (root / root)                    |
| Redis       | container-internal only                          |

The port-80 slot and other ports are left for whatever else is running.

## First run

```bash
cp .env.local.example .env.local     # done automatically by dev.sh if missing
./local/dev.sh up
```

`dev.sh up` builds the PHP image, starts the stack, waits for MariaDB, runs
`composer install` + `php yii migrate/up` (which also creates the `admin/admin123`
user), and flushes the cache. First build takes a few minutes.

## Hostname

**`http://fin.localhost:8090`** works out of the box in Chrome and Edge — they
resolve any `*.localhost` name to `127.0.0.1` with no configuration.

For a bare **`fin.local`** instead, add to the **Windows** hosts file
(`C:\Windows\System32\drivers\etc\hosts`, edited as Administrator — WSL's
`/etc/hosts` does not affect the Windows browser):

```
127.0.0.1 fin.local
```

Do **not** rely on `.local` without that entry — Windows treats `.local` as mDNS
and it will fail with `DNS_PROBE_FINISHED_NXDOMAIN`.

## Everyday commands

```bash
./local/dev.sh up|down|restart|rebuild
./local/dev.sh migrate                 # php yii migrate/up
./local/dev.sh seed                    # php yii seed/index  (demo data)
./local/dev.sh seed seed/customers     # a specific seeder
./local/dev.sh shell                   # bash inside finlocal-php
./local/dev.sh mysql                   # mysql client
./local/dev.sh logs [service]
./local/dev.sh test [models/ExpenseTest]
./local/dev.sh nuke                    # drop the DB volume, start clean

# Load real data
./local/dev.sh pull-db                 # dump production now, download, import (deploy/pull-db.sh)
./local/dev.sh import db-dumps/x.sql.gz # import a local dump (.sql or .sql.gz), replacing the DB
./local/dev.sh reset-admin             # set the 'admin' password back to admin123
```

### Loading production data

```bash
./local/dev.sh pull-db
```

Dumps the live DB over SSH into `db-dumps/` (git-ignored) and loads it into the
local `mybs` database. The prod dump has no `CREATE DATABASE` line so it drops
in cleanly. After import the users table is prod's (`admin` / `admin@example.com`
with the **production** password) — run `./local/dev.sh reset-admin` to get back
`admin123` locally. `db-dumps/` and any `*.sql` / `*.sql.gz` are git-ignored
because they hold real financial data.

## Notes

- Code is bind-mounted (`./php` → `/var/www/html`) and there is no opcache, so
  PHP edits are live on the next request. Gii and the debug toolbar are on
  (`APP_ENV=dev`).
- The DB lives in the named volume `finlocal_db`; `dev.sh down` keeps it,
  `dev.sh nuke` deletes it.
- This is separate from `docker-compose.yml` (the old `mb-*` local stack) and
  from `docker-compose.prod.yml` / `deploy/` (the `fin.chanakalk.com` server).
- To import a production dump:
  `./local/dev.sh mysql < some-dump.sql` won't work directly — use
  `docker compose -f docker-compose.local.yml -p fin-local exec -T mariadb mysql -uroot -proot mybs < dump.sql`.
