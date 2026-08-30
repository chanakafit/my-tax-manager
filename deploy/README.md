# Deploy — fin.chanakalk.com

Production runs on a DigitalOcean droplet (`178.128.211.159`, host `ezbook-demo`,
Ubuntu 22.04). Host **Apache** terminates TLS for `fin.chanakalk.com` and reverse
-proxies to the Docker stack:

```
Apache (:443)  ──►  mb-nginx (127.0.0.1:8081)  ──►  mb-php (php-fpm 8.3)
                                                     mb-mariadb (127.0.0.1:3307)
                                                     mb-redis, mb-phpmyadmin (127.0.0.1:8080)
```

The repo is checked out at `/var/www/fin`; `docker-compose.prod.yml` runs the
stack with `.env.prod`. **Application code is bind-mounted** (`./php` → container
`/var/www/html`) and PHP has no opcache, so a code change is live the moment the
files change on disk — no container restart needed for code-only deploys.

## Prerequisites (one-time)

- SSH access as `root@178.128.211.159` with your key (`ssh root@178.128.211.159` works).
- The server can already `git fetch` from `git@github.com:chanakafit/my-tax-manager.git`.
- `.env.prod` exists on the server (it does).

## Deploy

```bash
deploy/deploy.sh
```

This pushes your current `main`, then on the server:

1. Aborts if another deploy is running, `.env.prod` is missing, or the server
   working tree has uncommitted tracked changes (`FORCE=1` overrides).
2. `git fetch` + shows the incoming commits.
3. **Backs up the database** to `/var/www/.fin-deploy/backups/db-<ts>-<sha>.sql.gz`
   (keeps the last 10). Aborts if the dump is empty.
4. `git reset --hard origin/main`.
5. Rebuilds the `php` image **only if** `local/php/**`, `php/crontab`, or
   `docker-compose.prod.yml` changed; otherwise just `docker compose up -d`.
6. `composer install --no-dev` **only if** `php/composer.json|lock` changed.
7. `php yii migrate/up`.
8. `php yii cache/flush-all` and clears `runtime/cache/*` + `web/assets/*`.
9. Health-checks `https://fin.chanakalk.com/` (expects 200/302).

If **any** step 4–9 fails, it automatically rolls the code back to the previous
commit **and restores the database** from the backup taken in step 3.

### Variations

```bash
PUSH=0  deploy/deploy.sh          # deploy what's already on origin/main, don't push
FORCE=1 deploy/deploy.sh          # redeploy the same commit / discard server-side local edits
BRANCH=hotfix deploy/deploy.sh    # deploy a different branch
```

## Status

```bash
deploy/status.sh                  # deployed SHA vs origin, containers, backups, pending migrations
deploy/status.sh logs            # tail -f php + nginx logs
```

## Rollback

Automatic on a failed deploy. To roll back manually afterwards:

```bash
deploy/rollback.sh                       # back to the previous release (code only)
deploy/rollback.sh <commit-sha>          # back to a specific commit
RESTORE_DB=1 deploy/rollback.sh          # also restore the most recent DB backup
```

`RESTORE_DB=1` restores the newest dump in `/var/www/.fin-deploy/backups/`. Since
migrations have no down-path guarantee in this app, **restoring the database is
the reliable way to undo a bad migration** — pair it with a code rollback to the
matching commit.

## Manual DB restore

```bash
ssh root@178.128.211.159
cd /var/www/fin
ls -t /var/www/.fin-deploy/backups/
DB=$(grep ^MYSQL_DATABASE= .env.prod | cut -d= -f2-)
gunzip -c /var/www/.fin-deploy/backups/db-XXXX.sql.gz \
  | docker compose -f docker-compose.prod.yml exec -T mariadb mysql -u root -p "$DB"
```

> The live database name comes from `.env.prod` (`MYSQL_DATABASE`), not
> necessarily `mybs`.

## Config

Non-secret settings (host, paths, branch, health-check URL) live in
`deploy/config.sh` and can be overridden by environment variables. Nothing in
`deploy/` contains credentials — the DB password is read from `.env.prod` on the
server at run time.

## Notes / caveats

- **Line endings:** the repo now ships a `.gitattributes` forcing LF. Do **not**
  commit a repo-wide CRLF conversion — `git reset --hard` on the server would then
  rewrite `post_install.sh` with CRLF and break container startup. If `git status`
  shows hundreds of files changed with no real diff (`git diff -w` is empty), run
  `git add --renormalize .` and commit that separately before deploying.
- **Brief inconsistency window:** during `migrate/up` new code may briefly run
  against the old schema (and vice-versa). Acceptable for this single-tenant app;
  there is no maintenance-mode page.
- Backups and the `last-release` marker live in `/var/www/.fin-deploy/` (outside
  the repo) so `git reset --hard` / `git clean` can't remove them.
- First run of the new scripts: the server is currently 1 commit behind
  `origin/main` (`310a6759` vs `8daf0c77`) — `deploy/deploy.sh` will pick that up.
