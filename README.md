# My Tax Manager - Business & Tax Management System

A comprehensive business management application built with Yii2 PHP framework, featuring invoice management, expense tracking, employee payroll, tax return submissions, and financial reporting with advanced automation features.

## 📋 Table of Contents

- [Overview](#overview)
- [⚠️ Important Disclaimer](#important-disclaimer)
- [Key Features](#key-features)
- [Quick Start](#quick-start)
- [Docker Services](#docker-services)
- [Core Functionality](#core-functionality)
- [Advanced Features](#advanced-features)
- [Technology Stack](#technology-stack)
- [Common Commands](#common-commands)
- [Security](#security)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

My Tax Manager is a Yii2-based web application designed for small to medium businesses to manage their financial operations, track expenses, process payroll, and prepare comprehensive tax returns. The system automates recurring expense detection, supports bank statement uploads, and generates complete tax submission packages.

---

## ⚠️ Important Disclaimer

**AI-Generated Code Notice**

This project has been **heavily developed using Generative AI technologies**. While extensively tested, users should be aware:

- ⚠️ **No Warranty**: Software provided "AS IS" without any warranty
- ⚠️ **Financial Data**: Users are solely responsible for verifying all calculations and submissions
- ⚠️ **Tax Compliance**: Always consult qualified tax professionals before submitting tax returns
- ⚠️ **Security**: Perform your own security audit before production deployment
- ⚠️ **Data Backup**: Maintain regular backups - developers not responsible for data loss

**By using this software, you acknowledge and accept these risks and agree to use it at your own discretion.**

---

## ✨ Key Features

- **Financial Management**: Invoices, expenses, bank accounts, liabilities, multi-currency support
- **Tax Management**: Complete tax return workflow, year-end balances, bank statement integration, ZIP export
- **Employee Management**: Payroll, attendance tracking (full/half/1.5 day), salary advances with monthly overview
- **Automation**: Expense health check (detects missing recurring expenses), paysheet health check (auto-generates missing paysheets)
- **System Configuration**: Database-driven settings with UI management, signature upload, bulk updates
- **Reporting**: Excel/PDF exports, dashboard widgets, tax-year switcher on the dashboard, comprehensive financial reports

---


## 🚀 Quick Start

### Prerequisites
- Docker Desktop (4GB+ RAM recommended)
- Git
- Ports 80, 3307, 8080 available

### Installation (Linux/macOS)

```bash
# 1. Clone repository
git clone <repository-url>
cd my-tax-manager

# 2. Run setup script
chmod +x setup_linux_local.sh
./setup_linux_local.sh
```

**The setup script automatically:**
- ✅ Creates environment configuration files
- ✅ Builds Docker images with all PHP extensions (including ext-zip)
- ✅ Starts all containers (nginx, PHP, MariaDB, Redis, phpMyAdmin)
- ✅ Waits for MariaDB to be ready
- ✅ Creates database 'mybs'
- ✅ Runs 36+ database migrations
- ✅ Creates default admin user

### First Login

```
URL: http://localhost
Username: admin
Email: admin@example.com
Password: admin123

⚠️ Change password immediately after first login!
```

### Monitor Startup
```bash
# Watch container logs (first run takes 2-3 minutes)
docker logs -f mb-php
```

---

## 🐳 Docker Services

| Service | Container | Port | URL |
|---------|-----------|------|-----|
| Nginx | mb-nginx | 80 | http://localhost |
| PHP-FPM | mb-php | 9000 | - |
| MariaDB | mb-mariadb | 3307 | localhost:3307 |
| Redis | mb-redis | 6379 | localhost:6379 |
| phpMyAdmin | mb-phpmyadmin | 8080 | http://localhost:8080 |

**Common Commands:**
```bash
docker compose -p mb ps                    # View status
docker logs -f mb-php                      # View logs
docker compose -p mb restart php           # Restart
docker compose -p mb down                  # Stop all
docker compose -p mb up -d                 # Start all
```

---

## 🔧 Core Functionality

### Financial Management
- **Expenses**: Categories, vendors, receipts upload, payment methods, multi-currency support
- **Invoices**: Line items, PDF/Excel export, professional formatting
- **Tax Returns**: Assessment years, income tracking, expense summaries, bank balances, ZIP export (Excel + documents)
- **Bank Accounts**: Multiple banks support, balance history, statement uploads (PDF/JPG/PNG, max 10MB)
- **Liabilities**: Credit cards tracking, payment schedules

### Employee Management
- **Payroll**: Employee paysheets with automated calculations, Excel export
- **Attendance**: Track Full Day, Half Day, and 1.5 Day attendance with dashboard widget, monthly/yearly summaries
- **Salary Advances**: Monthly overview and year-to-date reporting with breakdown by month, counts, and averages

### System Configuration
Database-driven settings with UI management (Settings → System Configuration):
- **Business/Banking/System/Invoice** settings
- **Signature upload** for documents
- **Bulk updates** with automatic cache clearing
- Helper methods: `ConfigHelper::getBusinessName()`, `ConfigHelper::getBankingDetails()`

---

## 🚀 Advanced Features

### Expense Health Check
Automatically detects missing recurring expenses by analyzing 6 months of history. Requires 2-3 consecutive months to establish pattern. Dashboard widget shows alerts with Add/Ignore actions.

**Console Commands:**
```bash
docker exec mb-php php /var/www/html/yii expense-health-check/generate
docker exec mb-php php /var/www/html/yii expense-health-check/count
```

**Cron:** `0 1 1 * * docker exec mb-php php /var/www/html/yii expense-health-check/generate`

### Paysheet Health Check
Auto-generates missing monthly paysheets for active employees. Dashboard widget for review, edit, approve/reject. Calculates: Basic + Allowances - Deductions - Tax.

**Console Commands:**
```bash
docker exec mb-php php /var/www/html/yii paysheet-health-check/generate
docker exec mb-php php /var/www/html/yii paysheet-health-check/count
```

**Cron:** `0 2 1 * * docker exec mb-php php /var/www/html/yii paysheet-health-check/generate`

### Bank Statement Upload & ZIP Export
Upload statements (PDF/JPG/PNG, max 10MB) for each bank account. Download complete ZIP package with Excel report + all statements ready for tax submission.

### Tax Return Support Documents
Upload additional supporting documents for tax return submissions (e.g., income statements, property documents, loan agreements). Supports PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, JPEG (max 10MB per file). All uploaded documents are automatically included in the final ZIP package download under "Support_Documents" folder.

### Capital Assets & Allowances
Track business and personal capital assets. No automatic allowances - you decide when to claim, and each claim is a straight-line **20% of the original cost, for at most 5 years** per asset (Second Schedule write-off), so the percentage is not editable. Written down value automatically updated after each allowance. Only business assets are eligible. View historical allowances and maintain accurate written down values for tax reporting.

Allowances are claimed for a year of assessment and are applied in full to the annual return; each quarterly instalment claims a quarter of the year's allowances.

### Income Tax Computation
Records are computed per tax code (`YYYYQ`, quarters 1-4 = Apr-Jun ... Jan-Mar, `0` = annual return) from the financial transaction ledger:

```
profit        = income - expenses - payroll
taxable       = max(0, profit - capital allowances - personal relief)
tax           = min(progressive rates, maximum rate x taxable)
```

For a year of assessment, quarterly instalments use a quarter of the relief, the allowances and each rate band, so the four quarters add up to roughly the annual liability.

Rates and reliefs are configured in `php/config/params.php` under `taxConfigs.<year of assessment>`:

- `yearlyTaxRelief` - personal relief (Rs. 1,800,000 from 2025/2026)
- `taxRate` - the **maximum** rate (15% for foreign-currency service exports / foreign source income from 1 April 2025, 0% before, when such income was exempt). The database `tax_config.profit_tax_rate` decides which rate applies from which date.
- `taxBrackets` - the normal progressive bands for individuals (6% / 18% / 24% / 30% / 36% from 2025/2026). Where these produce less tax than the maximum rate, the lower amount is charged.

A year of assessment that is not listed inherits the most recent configured year, so no entry is needed until the IRD changes the rates.

**Console command** - recalculate stored records after a rate, relief or calculation change (records are otherwise recalculated only when an invoice, expense or paysheet changes; records already marked paid are skipped):

```bash
docker exec mb-php php /var/www/html/yii finance/recalculate        # all years
docker exec mb-php php /var/www/html/yii finance/recalculate 2025   # 2025/2026 only
```

---

## 🛠 Technology Stack

- **Backend**: PHP 8.2+ with Yii2 Framework (~2.0.45)
- **Database**: MariaDB 10.2
- **Web Server**: Nginx (latest)
- **Cache**: Redis (latest)
- **Containerization**: Docker & Docker Compose
- **Frontend**: Bootstrap 5, jQuery, Kartik Yii2 Widgets
- **PDF Generation**: mPDF, TCPDF
- **Excel**: PHPSpreadsheet (with ext-zip support)
- **Date/Time**: Kartik Date Range Picker
- **Charts**: Highcharts

---

## 💻 Common Commands

```bash
# Application
docker compose -p mb exec php php yii cache/flush-all                # Clear cache
docker compose -p mb exec php php yii migrate/up --interactive=0     # Run migrations
docker compose -p mb exec php bash                                   # Access shell

# Composer
docker compose -p mb exec php composer install                       # Install dependencies
docker compose -p mb exec php composer dump-autoload                 # Rebuild autoload

# Debugging
docker compose -p mb logs php -f                                     # View logs
docker compose -p mb ps                                              # Container status
```

---

## 🔒 Security

**Implemented:**
- Environment variables for credentials
- File upload validation (type/size limits)
- Authentication & RBAC
- Session management via Redis
- Password hashing (bcrypt)
- CSRF protection
- SQL injection prevention

**Before Production Checklist:**
- [ ] Change default admin password
- [ ] Set `YII_DEBUG=false` and `APP_ENV=prod`
- [ ] Configure SSL/TLS
- [ ] Set up regular backups
- [ ] Disable Gii and Debug toolbar

---

## 🧪 Testing

### Comprehensive Unit Test Suite

**280+ tests** with **~72% line coverage** using Codeception 5.x + Xdebug

**Coverage:**
- Services: ExpenseHealthCheckService (34 tests), PaysheetHealthCheckService (17 tests)
- Models: Expense, Invoice, Customer, Vendor, Employee, BankAccount, FinancialTransaction, etc. (216+ tests)
- Business Logic: Pattern detection, financial calculations, validation rules, status workflows

### Test Fixtures

All database tests use **ActiveFixture** with realistic Sri Lankan business data:

**Available Fixtures:**
- EmployeeFixture (4 employees), CustomerFixture (4 customers), VendorFixture (4 vendors)
- ExpenseCategoryFixture (5 categories), ExpenseFixture (5 expenses)
- InvoiceFixture (6 invoices), EmployeeSalaryAdvanceFixture (5 advances)

**Example Usage:**
```php
public function _fixtures()
{
    return [
        'employees' => ['class' => EmployeeFixture::class],
        'advances' => ['class' => EmployeeSalaryAdvanceFixture::class],
    ];
}

public function testSomething()
{
    $employee = $this->tester->grabFixture('employees', 'john_doe');
    verify($employee->first_name)->equals('John');
}
```

**Run Tests:**
```bash
# All tests
docker compose -p mb exec php php vendor/bin/codecept run unit

# Specific test
docker compose -p mb exec php php vendor/bin/codecept run unit models/ExpenseTest

# With coverage
docker compose -p mb exec php php vendor/bin/codecept run unit --coverage --coverage-html
# View: php/tests/_output/coverage/index.html
```

For detailed guides, see:
- **[php/tests/TESTING.md](php/tests/TESTING.md)** - Complete testing guide
- **[php/tests/FIXTURES.md](php/tests/FIXTURES.md)** - Fixture usage & creation guide

---


## 🐛 Troubleshooting

```bash
lsof -i :80  # Find what's using port 80
```

**Database issues:**
```bash
# Reset database
docker compose -p mb down
rm -rf local/mariadb/*
docker compose -p mb up -d
docker exec mb-php ./yii migrate/up --interactive=0
```

**Permission errors:**
```bash
chmod -R 777 php/runtime php/web/assets php/web/uploads
```

**Clear cache:**
```bash
docker exec mb-php php yii cache/flush-all
rm -rf php/runtime/cache/* php/web/assets/*
```

---

**For support or questions, please open an issue in the repository.**


