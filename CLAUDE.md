# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Yii2 web application for **Sri Lankan small-business tax and finance management**: invoicing, expense tracking, payroll, capital allowances, and quarterly/annual income-tax computation with an IRD-ready ZIP export. Single-tenant, self-hosted, Docker-deployed. Much of the code is AI-generated (see README disclaimer) and has not had a security audit.

Application code lives in `php/` (a Yii2 basic-template layout). The repo root holds Docker and provisioning files.

## Line endings

`.gitattributes` forces LF repo-wide. If `git status` ever shows hundreds of files changed while `git diff -w` is empty, that's an accidental CRLF conversion — run `git add --renormalize .` and commit it separately, don't bury real changes under it.

## Local development

Use `docker-compose.local.yml` — an **isolated** stack (project `fin-local`, containers `finlocal-*`, ports on `127.0.0.1` only) that coexists with other Docker projects on the machine (the `ez-*` stack owns port 80). App at **http://fin.localhost:8090** (Chrome/Edge auto-resolve `*.localhost`; `localhost:8090` also works). `fin.local` needs a Windows hosts-file entry — WSL `/etc/hosts` doesn't reach the Windows browser.

```bash
./local/dev.sh up          # build + start + migrate + create admin/admin123
./local/dev.sh migrate | seed | shell | mysql | logs | test | down | rebuild | nuke
./local/dev.sh pull-db     # dump prod over SSH -> db-dumps/ -> import into local 'mybs'
./local/dev.sh import <f>  # load a .sql[.gz] dump (replaces the DB)
./local/dev.sh reset-admin # local 'admin' password back to admin123 (after importing prod data)
```

Prod dumps have no `CREATE DATABASE`/`USE` (single-db `mysqldump`) so they load straight into local `mybs`. `db-dumps/` and `*.sql[.gz]` are git-ignored (real financial data). `deploy/pull-db.sh` is the underlying script.

`.env.local` (from `.env.local.example`) holds ports + DB creds. DB is in the named volume `finlocal_db`. Code is bind-mounted, no opcache — edits are live immediately; Gii + debug toolbar on. See `local/README.md`.

`docker-compose.yml` (the old `mb-*` stack, driven by `setup_linux_local.sh` which builds images that shadow the `nginx:latest`/`php:latest` tags) is superseded by the above — prefer `docker-compose.local.yml`.

## Common commands

Local: use `./local/dev.sh <cmd>` (wraps `docker compose -f docker-compose.local.yml -p fin-local ...`). Raw equivalents:

```bash
DC="docker compose -f docker-compose.local.yml --env-file .env.local -p fin-local"

$DC up -d --build
$DC ps
$DC logs -f php
$DC exec -w /var/www/html php bash

# Migrations
$DC exec -w /var/www/html php php yii migrate/up --interactive=0
$DC exec -w /var/www/html php php yii migrate/create create_x_table

# Cache
$DC exec -w /var/www/html php php yii cache/flush-all

# Composer
$DC exec -w /var/www/html php composer install
```

### Tests (Codeception 5, unit suite only)

```bash
# All unit tests   (or: ./local/dev.sh test)
$DC exec -w /var/www/html php ./vendor/bin/codecept run unit

# Single test file / single method
$DC exec -w /var/www/html php ./vendor/bin/codecept run unit models/ExpenseTest
$DC exec -w /var/www/html php ./vendor/bin/codecept run unit models/ExpenseTest:testValidation

# With HTML coverage -> php/tests/_output/coverage/index.html
$DC exec -w /var/www/html php ./vendor/bin/codecept run unit --coverage --coverage-html

# After adding/removing Codeception modules
$DC exec -w /var/www/html php ./vendor/bin/codecept build
```

Tests use `Codeception\Test\Unit` with `verify($x)->true()` assertions (not `$this->assertTrue`), the `Yii2` module (`orm`, `email`, `fixtures` parts), and `ActiveFixture` classes in `php/tests/fixtures/` with data in `php/tests/fixtures/data/`. Config is `php/config/test.php` + `php/config/test_db.php`.

**Warning:** `test_db.php` points at the same database as the running app (`db.php` with the dsn host forced to `mariadb`). DB-backed tests truncate/reload fixture tables there — fine on the local `finlocal_db` volume, never run them against a database with real data.

There is no linter configured. `.github/copilot-instructions.md` asks for PSR-12 and `declare(strict_types=1)`, but the existing code does **not** follow either — match the file you are editing, not the doc.

### Console entry points

- `yii migrate/*`, `yii cache/*` — framework.
- `yii seed/*` — `SeedController`: `seed/index` (full demo dataset), plus `seed/categories`, `seed/customers`, `seed/invoices`, `seed/expenses`, `seed/tax-records`, `seed/capital-assets`, `seed/clear`, etc.
- `yii expense-health-check/generate|count` — recurring-expense detection (cron: monthly).
- `yii paysheet-health-check/generate|count` — missing-paysheet generation (cron: monthly).
- `yii finance/daily`, `yii finance/monthly` — recurring expenses, overdue invoices, payment reminders, paysheet generation.

## Architecture

### Configuration layering

An env file (`.env.local` locally, `.env.prod` on the server) is passed as container env vars → read by:
- `php/config/params.php` (static params incl. `taxConfigs.<year>.*`)
- `php/config/db.php` — uses `php/config/db-local.php` if present (generated by the legacy `setup_linux_local.sh`, git-ignored), **else** env vars `MYSQL_HOST` / `MYSQL_DATABASE` / `MYSQL_USER` (default `root`) / `MYSQL_PASSWORD` / `DB_PREFIX`. The `docker-compose.local.yml` path uses the env-var branch — no `db-local.php`.
- `php/config/web.php` (web, mailer via `MAIL_DSN`) and `php/config/console.php` (CLI).
- The `admin` user is created during `migrate/up` by `m250827_000011_create_user_table` from `ADMIN_DEFAULT_USER/EMAIL/PASSWORD`.

Runtime, user-editable settings live in the DB (`SystemConfig` table) and are read through `app\helpers\ConfigHelper` (`getBusinessName()`, `getBankingDetails()`, ...). Static/tax config is read through `app\helpers\Params::get('dotted.key', $default)` and `app\models\TaxConfig`. Table prefix is `mb_`; models reference tables as `{{%name}}`.

### Model layer

- Domain models extend **`app\models\BaseModel`** (`ActiveRecord` + `TimestampBehavior` + `BlameableBehavior`, so every table has `created_at/updated_at/created_by/updated_by`). Do not confuse it with the unused empty stub `app\base\BaseModel`.
- `*Search.php` models are Yii GridView filter/search models (excluded from coverage).
- `User` is separate — plain `ActiveRecord implements IdentityInterface`, `TimestampBehavior` only.

### The tax recalculation engine (central, easy to miss)

`Expense`, `Invoice`, and `Paysheet` register handlers in their constructors on `EVENT_AFTER_INSERT` / after-update / before+after-delete that call **`TaxRecord::recalculateForDate($date)`**.

- Sri Lankan fiscal year is **April 1 – March 31**. Jan–Mar dates belong to the *previous* calendar year's tax year.
- `tax_code` format: `YYYYQ` where `Q` is `1..4` for quarters (Q1 = Apr–Jun ... Q4 = Jan–Mar) and `0` for the annual/final return.
- `recalculateForDate` finds the affected quarter code + the `YYYY0` annual code and re-runs `TaxRecord::calculateTax()` on each **existing** record, skipping any with `payment_status = 'paid'`.
- `calculateTax()` aggregates from `FinancialTransaction` rows (by `category` and `transaction_date` range), subtracts `CapitalAllowance` amounts for the `tax_code` and yearly relief (quarterly = yearly/4), applies `TaxConfig::getTaxRateForPeriod()` (0% before 2025-04-01), and stores results plus JSON arrays of contributing `related_invoice_ids` / `related_expense_ids` / `related_paysheet_ids`.
- Recalc failures are logged (`Yii::error`) and swallowed — they never abort the triggering save/delete.

`FinancialTransaction` is the ledger that ties everything together: income/expense/payroll/tax rows carrying `amount_lkr` and optional `related_*_id` back-references. The dashboard and tax engine both read from it, not from the source models directly.

### Controllers

Most extend `app\controllers\BaseController` (adds `AccessControl` requiring an authenticated user `@`, and POST-only `delete`). There is **no RBAC / role system** despite what `copilot-instructions.md` implies — any logged-in user can do anything. A few controllers extend raw `yii\web\Controller` inconsistently; `PublicInvoiceController` is deliberately unauthenticated (shares an invoice via a tokened `InvoiceLink`).

### Views, widgets, PDF/Excel

- Views under `php/views/<controller>/`, Bootstrap 5 + Kartik widgets. Custom `B*` widget wrappers in `php/widgets/` (`BHtml`, `BGridView`, `BActiveForm`, ...) are used in place of the Yii defaults for styling consistency.
- Dashboard widgets: `ExpenseHealthCheckWidget`, `PaysheetHealthCheckWidget`, `QuickAttendanceWidget`.
- PDF via `InvoicePdfGenerator` (mPDF/TCPDF); Excel/ZIP export via PhpSpreadsheet (`ext-zip` required in the image). Tax-return ZIP bundles an Excel report + uploaded bank statements (`TaxYearBankBalance`) + `TaxReturnSupportDocument` files.

### Health-check services (`php/components/`)

`ExpenseHealthCheckService` scans up to 6 months of history, groups expenses by `(category, vendor)`, and flags patterns present in ≥2 near-consecutive months (1-month gap tolerated) as `ExpenseSuggestion` rows for months with no matching expense — respecting per-pattern permanent/temporary ignore states. `PaysheetHealthCheckService` similarly generates `PaysheetSuggestion` rows for active employees missing a monthly paysheet. Both are surfaced as dashboard widgets (approve / ignore) and run via cron.

## Deployment

- **Local:** `docker-compose.yml` — nginx :80, mariadb :3307, phpMyAdmin :8080, redis. Reads `.env`.
- **Production:** `fin.chanakalk.com` on `178.128.211.159` (`/var/www/fin`). `docker-compose.prod.yml` — services bound to `127.0.0.1` only, behind a host Apache reverse proxy (`ProxyPass` to `127.0.0.1:8081`), reads `.env.prod`, `APP_ENV=prod`. The live DB name is whatever `.env.prod`'s `MYSQL_DATABASE` says (currently `ktjxqmrmkw`, not `mybs`). Code is bind-mounted and PHP has no opcache, so code changes are live without a restart.
- **Deploy scripts:** `deploy/deploy.sh` (push + remote pull, DB backup, conditional rebuild, migrate, cache clear, health check, auto-rollback), `deploy/rollback.sh`, `deploy/status.sh`. Config in `deploy/config.sh`; see `deploy/README.md`. Backups live in `/var/www/.fin-deploy/backups/` on the server.
- `MIGRATION.md` is the original Cloudways→VPS runbook (Let's Encrypt via certbot), now largely historical.
- MariaDB is pinned to **10.2** (long EOL) in both compose files.
- Before production: set `YII_DEBUG=false` / `APP_ENV=prod` (disables Gii + debug toolbar, which are otherwise IP-open with `allowedIPs => ['*']`), change the default `admin` / `admin123` credentials, configure real SMTP (`MAIL_DSN` or `mail-local.php`; defaults to `null://null`).

## Conventions (from .github/copilot-instructions.md + README)

- Currency: LKR, `DECIMAL(10,2)` money fields, `Y-m-d` dates, format in views via `Yii::$app->formatter`.
- Migrations: include FK constraints and indexes on FKs / frequently-queried columns; `{{%table}}` prefix syntax; keep `created_at/updated_at/created_by/updated_by`.
- Commit messages: conventional prefixes (`feat:`, `fix:`, `docs:`, `test:`, `refactor:`, `style:`, `perf:`, `chore:`).
- Update `README.md` when adding a user-facing feature, new table/migration, new console command, or new config option — not for bug fixes or refactors.
