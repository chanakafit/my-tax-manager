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
- **Reporting**: Excel/PDF exports, dashboard widgets, comprehensive financial reports

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

For detailed testing guide, see: **[php/tests/TESTING.md](php/tests/TESTING.md)**

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


