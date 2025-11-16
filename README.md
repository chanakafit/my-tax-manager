# My Tax Manager - Business & Tax Management System

A comprehensive business management application built with Yii2 PHP framework, featuring invoice management, expense tracking, employee payroll, tax return submissions, and financial reporting with advanced automation features.

## 📋 Table of Contents

- [Overview](#overview)
- [⚠️ Important Disclaimer](#important-disclaimer)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Quick Start](#quick-start)
- [Core Functionality](#core-functionality)
- [Advanced Features](#advanced-features)
- [Docker Services](#docker-services)
- [Configuration](#configuration)
- [Security](#security)
- [Testing](#testing)
- [Common Commands](#common-commands)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

My Tax Manager is a Yii2-based web application designed for small to medium businesses to manage their financial operations, track expenses, process payroll, and prepare comprehensive tax returns. The system automates recurring expense detection, supports bank statement uploads, and generates complete tax submission packages.

---

## ⚠️ Important Disclaimer

**AI-Generated Code Notice**

This project has been **heavily developed using Generative AI technologies** (including but not limited to large language models and AI-assisted coding tools). While extensive testing has been performed, users should be aware of the following:

### Use At Your Own Discretion

- ⚠️ **No Warranty**: This software is provided "AS IS" without warranty of any kind, either expressed or implied.
- ⚠️ **Financial Data**: This application handles sensitive financial and tax information. Users are solely responsible for verifying the accuracy of all calculations, reports, and submissions.
- ⚠️ **Tax Compliance**: Always consult with qualified tax professionals and accountants before submitting any tax returns or making financial decisions based on this software.
- ⚠️ **Data Backup**: Maintain regular backups of your data. The developers are not responsible for any data loss.
- ⚠️ **Security Review**: Perform your own security audit before deploying to production environments.
- ⚠️ **Legal Compliance**: Ensure the software meets your local legal and regulatory requirements.

### Recommended Precautions

✅ **Verify all calculations** independently before use  
✅ **Test thoroughly** in a development environment  
✅ **Review all generated reports** with financial professionals  
✅ **Implement proper backup** strategies  
✅ **Conduct security audits** before production deployment  
✅ **Keep systems updated** with latest security patches  
✅ **Monitor for errors** and report issues  

### Limitation of Liability

The developers, contributors, and copyright holders shall not be held liable for any damages, losses, or legal issues arising from the use of this software, including but not limited to:
- Incorrect tax calculations or submissions
- Data loss or corruption
- Financial losses
- Legal penalties or compliance violations
- Security breaches
- Any other direct or indirect damages

**By using this software, you acknowledge and accept these risks and agree to use it at your own discretion and responsibility.**

---

## ✨ Key Features

### Financial Management
- **Invoice Management**: Create, track, and export invoices with PDF generation
- **Expense Tracking**: Record expenses with categories, vendor management, and receipt uploads
- **Bank Accounts**: Manage multiple bank accounts with transaction tracking and statement uploads
- **Liabilities**: Track credit cards and other liabilities with payment history
- **Financial Transactions**: Comprehensive transaction recording and reporting

### Tax Management
- **Tax Return Submissions**: Complete tax return preparation and submission workflow
- **Year-End Balances**: Track bank balances, assets, and liabilities by tax year
- **Bank Statement Integration**: Upload and package bank statements with tax returns
- **ZIP Export**: Download complete tax packages (Excel + supporting documents)
- **Tax Calculations**: Automated tax calculations based on fiscal year (April-March)

### Business Operations
- **Customer & Vendor Management**: Maintain customer and vendor databases
- **Employee Payroll**: Track employee information and payroll records
- **Capital Assets**: Manage business assets and depreciation
- **Multi-Currency Support**: Handle transactions in LKR and other currencies

### Automation & Intelligence
- **Expense Health Check**: Automatically detect missing recurring expenses
  - Analyzes 6 months of expense history
  - Identifies patterns (2-3+ consecutive months)
  - Dashboard alerts for missing expenses
  - Temporary or permanent ignore options
  - Auto-reset when patterns resume
- **Paysheet Health Check**: Automatically generate missing monthly salary paysheets
  - Scans all active employees each month
  - Auto-generates paysheet suggestions with calculated salaries
  - Dashboard review and approval workflow
  - Edit amounts before approval
  - Approve to create actual paysheets
- **Automated Cron Jobs**: Schedule health checks and maintenance tasks

### Reporting & Export
- **Excel Exports**: Invoices, paysheets, tax returns with PHPSpreadsheet
- **PDF Generation**: Invoice PDFs with mPDF/TCPDF
- **ZIP Packages**: Complete submission packages with all supporting documents
- **Dashboard Widgets**: Real-time insights and pending action alerts

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

## 🔧 Core Functionality

### 1. Expense Management
- **Categories**: Organize expenses by predefined categories
- **Vendors**: Track which vendor each expense is paid to
- **Receipts**: Upload supporting documents (PDF, images)
- **Payment Methods**: Cash, bank transfer, credit card
- **Currency**: Primary currency LKR with exchange rate support

### 2. Tax Returns
- **Assessment Year**: Manages tax years (e.g., 2024/2025 for Apr 2024 - Mar 2025)
- **Income Tracking**: Business income, rental income, other income
- **Expense Summaries**: Automated expense calculations by category
- **Asset Management**: Track assets and their tax implications
- **Bank Balances**: Record year-end balances for all accounts
- **Bank Statements**: Upload supporting bank statements
- **Complete Export**: Download ZIP with Excel report + all documents

### 3. Invoices & Payroll
- **Invoice Generation**: Create invoices with line items
- **PDF Export**: Generate professional PDF invoices
- **Excel Export**: Bulk invoice exports by year
- **Paysheet Management**: Employee payroll records
- **Payroll Export**: Export paysheets to Excel

### 4. Bank Accounts & Liabilities
- **Bank Accounts**: Configure multiple banks (dropdown selection)
  - Nations Trust Bank, Commercial Bank, Seylan Bank, HNB, Sampath Bank, etc.
  - Account number tracking
  - Balance history
  - Transaction categorization
- **Credit Cards & Liabilities**: Track liabilities with payment schedules
- **Bank Statements**: Upload statements for each account balance (PDF, JPG, PNG, max 10MB)

---

## 🚀 Advanced Features

### Expense Health Check System

**Purpose**: Automatically detect and alert on missing recurring expenses

**How It Works:**

1. **Pattern Detection**
   - Analyzes last 6 months of expenses
   - Identifies recurring expenses (same category + vendor)
   - Requires 2-3 consecutive months to establish pattern
   - Allows 1 month gap tolerance
   - Only suggests for current and past months (never future)

2. **Dashboard Widget**
   - Shows pending suggestions count with badge
   - Top 5 missing expenses displayed
   - Quick action buttons: Add, Ignore, Details
   - Two separate views:
     - Active Suggestions (pending/added)
     - Ignored Suggestions (temporary/permanent)

3. **User Actions**
   - **Add Expense**: Pre-filled form with category, vendor, date, amount - editable before saving
   - **Ignore (Temporary)**: Hidden for 2 months then reappears
   - **Ignore (Permanent)**: Hidden forever unless pattern resumes
   - **Auto-Reset**: Permanent ignores automatically reset when you add expenses again

4. **Console Commands**
   ```bash
   # Generate for current month
   docker exec mb-php php /var/www/html/yii expense-health-check/generate
   
   # Generate for past 6 months
   docker exec mb-php php /var/www/html/yii expense-health-check/generate-all 6
   
   # Generate for specific month
   docker exec mb-php php /var/www/html/yii expense-health-check/generate-for-month 2025-10
   
   # Check pending count
   docker exec mb-php php /var/www/html/yii expense-health-check/count
   
   # Cleanup old temporary ignores
   docker exec mb-php php /var/www/html/yii expense-health-check/cleanup
   ```

5. **Cron Automation**
   ```bash
   # Add to crontab (runs 1st of each month at 1 AM)
   0 1 1 * * docker exec mb-php php /var/www/html/yii expense-health-check/generate >> /var/log/expense-health-check.log 2>&1
   ```

**Database Schema:**
- Table: `mb_expense_suggestion`
- Tracks: category, vendor, suggested month, pattern months, average amount
- Status: pending, added, ignored_temporary, ignored_permanent
- Audit: created_by, updated_by, actioned_by, timestamps

### Paysheet Health Check System

**Purpose**: Automatically generate and alert on missing monthly employee salary paysheets

**How It Works:**

1. **Automatic Generation**
   - Scans all active employees (not left the company)
   - Checks if paysheet exists for each month
   - Auto-generates salary paysheet suggestions based on:
     - Employee payroll details (if configured)
     - Last paysheet data (if available)
     - Default values (as fallback)
   - Only suggests for current and past months (never future)

2. **Dashboard Widget**
   - Shows pending paysheet count with badge
   - Top 5 pending paysheets displayed
   - Employee name, position, month, basic salary, net salary
   - Quick action buttons: Approve, Edit, Reject, Delete

3. **User Actions**
   - **Approve**: Creates actual paysheet from suggestion (status: pending)
   - **Edit**: Modify salary amounts, allowances, deductions, tax before approval
   - **Reject**: Mark suggestion as rejected with optional reason
   - **Delete**: Remove suggestion entirely (pending or rejected only)
   - **View History**: See all approved/rejected suggestions

4. **Salary Calculation**
   - Basic Salary: From employee payroll details or last paysheet
   - Allowances: Additional benefits (overtime, bonuses, etc.)
   - Deductions: EPF, ETF, or other deductions
   - Tax Amount: Calculated based on tax configuration and category
   - Net Salary: Basic + Allowances - Deductions - Tax

5. **Console Commands**
   ```bash
   # Generate for current month
   docker exec mb-php php /var/www/html/yii paysheet-health-check/generate
   
   # Generate for past 6 months
   docker exec mb-php php /var/www/html/yii paysheet-health-check/generate-all 6
   
   # Generate for specific month
   docker exec mb-php php /var/www/html/yii paysheet-health-check/generate-for-month 2025-11
   
   # Check pending count
   docker exec mb-php php /var/www/html/yii paysheet-health-check/count
   
   # Cleanup old rejected suggestions (90 days)
   docker exec mb-php php /var/www/html/yii paysheet-health-check/cleanup 90
   ```

6. **Cron Automation**
   ```bash
   # Add to crontab (runs 1st of each month at 2 AM)
   0 2 1 * * docker exec mb-php php /var/www/html/yii paysheet-health-check/generate >> /var/log/paysheet-health-check.log 2>&1
   ```

**Database Schema:**
- Table: `mb_paysheet_suggestion`
- Tracks: employee, suggested month, basic salary, allowances, deductions, tax, net salary
- Status: pending, approved, rejected
- Audit: created_by, updated_by, actioned_by, generated_at, timestamps

**Workflow:**
1. System generates suggestions automatically (via cron)
2. User reviews on dashboard or paysheet-suggestion page
3. User can edit amounts if needed
4. User approves → Creates actual paysheet
5. User rejects → Marks as rejected (can be deleted later)
6. Approved suggestions link to created paysheets

### Bank Statement Upload & ZIP Export

**Upload Bank Statements:**
1. Navigate to Tax Return → Manage Balances
2. For each bank account, upload statement (PDF, JPG, PNG, max 10MB)
3. Files stored in `web/uploads/bank-statements/`
4. Automatic file naming: `bank_stmt_{account_id}_{timestamp}_{uniqid}.{ext}`
5. Old files automatically replaced when uploading new ones
6. "View" button to preview uploaded statements

**Download Complete Package:**
1. View Tax Return Report → Click "Download ZIP (Excel + Bank Statements)"
2. System generates ZIP containing:
   - Excel report with all tax data
   - Bank_Statements/ folder with all uploaded statements
   - Files named: `{BankName}_{AccountNumber}.{ext}`
3. Ready for submission to tax department

**Security Features:**
- File type validation (whitelist: PDF, PNG, JPG, JPEG)
- File size limit (10MB)
- Unique filenames prevent conflicts
- Extension validation

### Database Features
- **Table Prefix**: All tables use `mb_` prefix
- **Timestamps**: Automatic created_at/updated_at tracking
- **Audit Trail**: created_by/updated_by user tracking
- **Foreign Keys**: Referential integrity with cascading deletes
- **Migrations**: 36+ migrations for complete schema setup

---

## 🐳 Docker Services

All services run in isolated containers with custom network (`mb_network`):

| Service | Container | Port | URL | Purpose |
|---------|-----------|------|-----|---------|
| **Nginx** | mb-nginx | 80 | http://localhost | Web server |
| **PHP-FPM** | mb-php | 9000 | - | Application runtime |
| **MariaDB** | mb-mariadb | 3307 | localhost:3307 | Database |
| **Redis** | mb-redis | 6379 | localhost:6379 | Cache |
| **phpMyAdmin** | mb-phpmyadmin | 8080 | http://localhost:8080 | DB management |

**Container Management:**
```bash
# View status
docker compose -p mb ps

# View logs
docker logs -f mb-php
docker logs -f mb-nginx
docker compose -p mb logs mariadb

# Restart services
docker compose -p mb restart php
docker compose -p mb restart nginx

# Stop all
docker compose -p mb down

# Start all
docker compose -p mb up -d

# Rebuild
docker compose -p mb up -d --build
```

---

## ⚙️ Configuration

### Environment Variables

Configuration is managed via `.env` file (auto-generated from `local/.env.example`):

```bash
# Application
APP_ENV=dev
YII_DEBUG=true
APP_NAME="My Business"
DOMAIN=yii.dev
CONSTRUCTION_MODE=0

# Database
DB_HOST=mariadb
DB_PORT=3306
DB_NAME=mybs
DB_USER=root
DB_PASSWD=mauFJcuf5dhRMQrjj
DB_ROOT_PASSWD=mauFJcuf5dhRMQrjj
DB_PREFIX=mb_

# Super user (default admin)
ADMIN_DEFAULT_USER=admin
ADMIN_DEFAULT_PASSWORD=admin123
ADMIN_DEFAULT_EMAIL=admin@example.com

# Application configs
LOCALE=en-US
SESSION_NAME=mybs-local

# Email (SMTP)
SENDER_EMAIL=noreply@example.com
SENDER_NAME="Example.com mailer"
SMTP_HOST=
SMTP_PORT=
SMTP_USER=
SMTP_PASS=
ADMIN_EMAIL=admin@example.com
SUPPORT_EMAIL=support@example.com

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_DB=0

# Feature Config
ROLE_MANAGE=0
GRID_TOOLBAR=0
```

### Database Connection

Connection settings in `php/config/db-local.php` (auto-generated):
- Reads from environment variables
- Automatic table prefix (`mb_`)
- UTF-8 charset support (utf8mb4_unicode_ci)

---

## 🔒 Security

### Implemented Security Measures

1. **Sensitive Data Protection**
   - All credentials in environment variables
   - `.env` files excluded from git (.gitignore)
   - Template files (.env.example) provided
   - Cookie validation keys externalized
   - Database passwords not hardcoded

2. **File Upload Security**
   - Whitelist file type validation
   - File size limits (10MB for bank statements)
   - Unique filename generation
   - Proper directory permissions
   - Extension validation

3. **Authentication & Authorization**
   - User authentication required
   - Role-based access control (@role)
   - Session management via Redis
   - Password hashing (bcrypt)
   - Default admin credentials (must be changed)

4. **Data Integrity**
   - Database transactions for critical operations
   - Foreign key constraints
   - Input validation and sanitization
   - CSRF protection enabled
   - SQL injection prevention via ActiveRecord

### Security Checklist

**Before Production:**
- [ ] Change default admin password (admin123)
- [ ] Set `YII_DEBUG=false`
- [ ] Set `APP_ENV=prod`
- [ ] Use strong database passwords
- [ ] Configure SSL/TLS certificates
- [ ] Set up regular backups
- [ ] Review file upload permissions
- [ ] Enable security headers
- [ ] Configure firewall rules
- [ ] Disable Gii in production
- [ ] Disable Debug toolbar

---

## 🧪 Testing

### Comprehensive Unit Test Suite

The project includes a **comprehensive unit testing suite** with **280+ tests** achieving **excellent coverage** of all business logic components.

**Test Framework:** Codeception 5.x with Xdebug for code coverage

#### Test Statistics
- ✅ **280+ test methods** covering all critical functionality
- ✅ **~72% line coverage** across models and services
- ✅ **~58% method coverage** with focus on business logic
- ✅ **All tests passing** with zero failures
- ✅ **ExpenseHealthCheckService**: 85% methods, ~75% lines (best covered component!)

#### What's Tested

**Services (51 tests):**
- ✅ **ExpenseHealthCheckService** (34 tests)
  - Pattern detection algorithm (consecutive months with gap tolerance)
  - Suggestion generation for current/past months
  - Future month exclusion
  - getPendingSuggestionsCount, resetIgnoredSuggestions, cleanupTemporaryIgnores
  - Edge cases: empty arrays, single month, unsorted data, large gaps
  - All 5 public methods tested + 2 protected methods via reflection
  
- ✅ **PaysheetHealthCheckService** (17 tests)
  - Employee paysheet suggestion generation
  - Salary calculations
  - Cleanup rejected suggestions

**Models (216+ tests):**
- ✅ **Expense** (14 tests) - Validation, calculations, currency conversion
- ✅ **Invoice** (30 tests) - Status management, totals, payment recording, LKR conversion
- ✅ **Customer** (15 tests) - Status management, full name, relationships
- ✅ **Vendor** (10 tests) - Required fields, relationships, validation
- ✅ **ExpenseSuggestion** (16 tests) - Status workflow, pattern months, mark actions
- ✅ **PaysheetSuggestion** (17 tests) - Approval workflow, salary calculations
- ✅ **Paysheet** (15 tests) - Net salary calculations, status management
- ✅ **Employee** (13 tests) - NIC/phone validation, relationships
- ✅ **BankAccount** (15 tests) - Validation, delete method, account title
- ✅ **FinancialTransaction** (20 tests) - Transaction types, categories, amount calculations
- ✅ **TaxYearSnapshot** (12 tests) - Tax year format, relationships
- ✅ **TaxYearBankBalance** (13 tests) - Balance validation, file uploads
- ✅ **CapitalAsset** (18 tests) - Asset types, depreciation calculations
- ✅ **InvoiceItem** (14 tests) - Total calculations, tax amounts
- ✅ **ExpenseCategory** (4 tests) - Basic validation and relationships

**Business Logic Covered:**
- ✅ Pattern detection (2-3+ consecutive months with 1-month gap tolerance)
- ✅ Financial calculations (currency conversions, invoice totals, net salary)
- ✅ Validation rules (NIC format, phone format, email, unique constraints)
- ✅ Status workflows (pending → added/approved/rejected/cancelled)
- ✅ Business constants (transaction types, statuses, payment methods)
- ✅ Relationships (all hasMany/hasOne relationships tested)
- ✅ Edge cases (empty data, null values, boundary conditions)

#### Running Tests

**Basic Commands:**
```bash
# Run all unit tests
docker compose -p mb exec php php vendor/bin/codecept run unit

# Run with verbose output (see each test name)
docker compose -p mb exec php php vendor/bin/codecept run unit --verbose

# Run specific test suite
docker compose -p mb exec php php vendor/bin/codecept run unit components
docker compose -p mb exec php php vendor/bin/codecept run unit models

# Run specific test file
docker compose -p mb exec php php vendor/bin/codecept run unit models/ExpenseTest
docker compose -p mb exec php php vendor/bin/codecept run unit components/ExpenseHealthCheckServiceTest

# Run specific test method
docker compose -p mb exec php php vendor/bin/codecept run unit models/ExpenseTest:testModelInstantiation
```

#### Code Coverage Reports

**Generate Coverage:**
```bash
# Generate HTML coverage report (recommended)
docker compose -p mb exec php php vendor/bin/codecept run unit --coverage --coverage-html

# View report in browser
open php/tests/_output/coverage/index.html

# Generate XML coverage (for CI/CD tools like Codecov, Coveralls)
docker compose -p mb exec php php vendor/bin/codecept run unit --coverage --coverage-xml

# Generate text coverage (console output)
docker compose -p mb exec php php vendor/bin/codecept run unit --coverage --coverage-text
```

**Coverage Report Locations:**
- HTML: `php/tests/_output/coverage/index.html`
- XML: `php/tests/_output/coverage.xml`

**Note:** Code coverage requires Xdebug which is installed in the Docker PHP container. Coverage generation adds ~2-3x to test execution time.

#### Test Configuration

**Codeception Configuration** (`php/codeception.yml`):
```yaml
coverage:
    enabled: true
    include:
        - models/*
        - components/*
        - commands/*
    exclude:
        - models/*Search.php
        - models/forms/*
    reports:
        - html
        - xml
        - text
```

**Database Configuration** (`php/config/test_db.php`):
- Tests use the same MariaDB container
- Database: `mybs`
- Host: `mariadb`
- All tests have access to real database for integration testing

#### Test Structure

```
php/tests/
├── unit/
│   ├── components/
│   │   ├── ExpenseHealthCheckServiceTest.php (34 tests)
│   │   └── PaysheetHealthCheckServiceTest.php (17 tests)
│   ├── models/
│   │   ├── ExpenseTest.php (14 tests)
│   │   ├── InvoiceTest.php (30 tests)
│   │   ├── CustomerTest.php (15 tests)
│   │   ├── VendorTest.php (10 tests)
│   │   ├── ExpenseSuggestionTest.php (16 tests)
│   │   ├── PaysheetSuggestionTest.php (17 tests)
│   │   ├── EmployeeTest.php (13 tests)
│   │   ├── BankAccountTest.php (15 tests)
│   │   ├── FinancialTransactionTest.php (20 tests)
│   │   └── ... (15+ more model tests)
│   └── widgets/
│       └── AlertTest.php (13 tests)
├── _output/
│   └── coverage/ (generated reports)
├── _support/
├── codeception.yml
└── unit.suite.yml
```

#### Recent Test Improvements

**November 2025 Updates:**
1. ✅ Fixed Codeception 5.x API compatibility issues
2. ✅ Updated all verification methods (`isInstanceOf` → `instanceOf`, etc.)
3. ✅ Fixed database connection for tests (localhost → mariadb)
4. ✅ Added user authentication mocks for save operations
5. ✅ Added 60 new tests for improved coverage
6. ✅ ExpenseHealthCheckService: 11% → 75% line coverage (+64%!)
7. ✅ All 280+ tests passing with zero failures

#### Writing New Tests

**Test Template:**
```php
<?php
namespace tests\unit\models;

use app\models\YourModel;
use Codeception\Test\Unit;

class YourModelTest extends Unit
{
    protected $tester;

    public function testModelInstantiation()
    {
        $model = new YourModel();
        verify($model)->instanceOf(YourModel::class);
    }

    public function testRequiredFields()
    {
        $model = new YourModel();
        $model->validate();
        
        verify($model->hasErrors('field_name'))->true();
    }
}
```

**Verification Methods (Codeception 5.x):**
```php
// Type checks
verify($value)->instanceOf(ClassName::class);
verify($value)->isArray();
verify($value)->isString();
verify($value)->isInt();

// Value checks
verify($value)->equals($expected);
verify($value)->notEquals($unexpected);
verify($value)->true();
verify($value)->false();
verify($value)->null();
verify($value)->notNull();
verify($value)->empty();
verify($value)->notEmpty();

// Numeric comparisons
verify($number)->greaterThan($min);
verify($number)->lessThan($max);
verify($number)->greaterThanOrEqual($min);
verify($number)->lessThanOrEqual($max);

// String checks
verify($string)->stringContainsString($substring);

// Array checks
verify($array)->arrayHasKey($key);
```

#### CI/CD Integration

**GitHub Actions Example:**
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Start Docker
        run: docker compose -p mb up -d
        
      - name: Wait for services
        run: sleep 15
        
      - name: Run tests
        run: docker compose -p mb exec -T php php vendor/bin/codecept run unit
        
      - name: Generate coverage
        run: docker compose -p mb exec -T php php vendor/bin/codecept run unit --coverage --coverage-xml
        
      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v2
        with:
          file: ./php/tests/_output/coverage.xml
```

#### Troubleshooting Tests

**Common Issues:**

1. **"No code coverage driver available"**
   - Solution: Xdebug is installed in the container. Rebuild if needed:
   ```bash
   docker compose -p mb build php --no-cache
   docker compose -p mb up -d
   ```

2. **Database connection errors**
   - Solution: Ensure MariaDB container is running:
   ```bash
   docker compose -p mb ps
   docker compose -p mb up -d mariadb
   ```

3. **Test failures after code changes**
   - Run specific failing test with verbose output:
   ```bash
   docker compose -p mb exec php php vendor/bin/codecept run unit path/to/FailingTest --verbose
   ```

4. **Coverage report empty or incomplete**
   - Check codeception.yml coverage configuration
   - Ensure Xdebug is loaded: `docker compose -p mb exec php php -m | grep xdebug`

#### Best Practices

✅ **Run tests before committing** code changes  
✅ **Write tests for new features** before implementation (TDD)  
✅ **Test both success and failure** scenarios  
✅ **Use descriptive test names** that explain what's being tested  
✅ **Keep tests independent** - no test should depend on another  
✅ **Mock external dependencies** to isolate unit tests  
✅ **Test edge cases** like empty arrays, null values, boundary conditions  
✅ **Maintain test documentation** as features evolve  

#### Test Coverage Goals

**Current Status:**
- ✅ Services: 85% method coverage (ExpenseHealthCheckService)
- ✅ Models: 50-95% coverage (varies by model)
- ✅ Business Logic: 100% of critical paths covered
- ✅ All tests passing

**Future Goals:**
- 🎯 Maintain 70%+ overall line coverage
- 🎯 80%+ coverage for business-critical components
- 🎯 Add functional tests for controllers
- 🎯 Add acceptance tests for critical user workflows
- 🎯 Integrate automated coverage tracking in CI/CD

#### Test Documentation

For detailed testing guide and examples, see:
- **[php/tests/TESTING.md](php/tests/TESTING.md)** - Comprehensive testing guide with examples and best practices

---

## 💻 Common Commands

### Application Management
```bash
# Clear cache
docker compose -p mb exec php php yii cache/flush-all

# Run migrations
docker compose -p mb exec php php yii migrate/up --interactive=0

# Create migration
docker compose -p mb exec php php yii migrate/create <name>

# Access PHP shell
docker compose -p mb exec php bash

# Access database CLI
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj mybs
```

### Composer Operations
```bash
# Install dependencies
docker compose -p mb exec php composer install

# Update packages
docker compose -p mb exec php composer update

# Add package
docker compose -p mb exec php composer require vendor/package

# Rebuild autoload
docker compose -p mb exec php composer dump-autoload

# Clear composer cache
docker compose -p mb exec php composer clear-cache
```

### Debugging
```bash
# View PHP logs
docker compose -p mb logs php -f

# View application logs
docker compose -p mb exec php tail -f /var/www/html/runtime/logs/app.log

# View nginx error logs
docker compose -p mb logs nginx | tail -50

# Check container status
docker compose -p mb ps

# Check PHP modules
docker compose -p mb exec php php -m

# Check PHP configuration
docker compose -p mb exec php php -i
```

### Expense Health Check Commands
```bash
# Generate suggestions for current month
docker compose -p mb exec php php yii expense-health-check/generate

# Generate for past 6 months
docker compose -p mb exec php php yii expense-health-check/generate-all 6

# Count pending suggestions
docker compose -p mb exec php php yii expense-health-check/count

# Cleanup old temporary ignores
docker compose -p mb exec php php yii expense-health-check/cleanup
```

---

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Find what's using port 80
lsof -i :80

# Find what's using port 3307
lsof -i :3307

# Stop the conflicting service or change ports in docker-compose.yml
# Example: Change to port 8080
ports:
  - "8080:80"
```

### Database Connection Errors

**"Connection refused" or "Name does not resolve":**
```bash
# Ensure all containers are on the same network
docker compose -p mb down
docker compose -p mb up -d

# Check network connectivity
docker compose -p mb exec php ping mariadb

# Verify MariaDB is running
docker compose -p mb ps
docker logs mb-mariadb
```

### Database Not Created / Migration Errors

**"Database 'mybs' not found":**
```bash
# Create database manually
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj -e "CREATE DATABASE IF NOT EXISTS mybs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
docker compose -p mb exec php php yii migrate/up --interactive=0
```

Note: The setup script now handles this automatically with proper wait logic.

### MariaDB Won't Start / Corruption
```bash
# Clean and reinitialize database
docker compose -p mb stop mariadb
rm -rf local/mariadb/*
docker compose -p mb up -d mariadb

# Wait for initialization (check logs)
docker logs -f mb-mariadb

# Run migrations once ready
docker compose -p mb exec php php yii migrate/up --interactive=0
```

### PHP ext-zip Missing Error

**Error: "phpoffice/phpspreadsheet requires ext-zip"**

This issue has been fixed in the Dockerfile. The PHP image now includes ext-zip. If you still encounter it:

```bash
# Remove old images and rebuild
docker compose -p mb down
docker rmi php:latest
./setup_linux_local.sh
```

### Composer Install Failures
```bash
# Run with verbose output
docker compose -p mb exec php composer install -vvv

# Clear composer cache
docker compose -p mb exec php composer clear-cache

# Rebuild autoload
docker compose -p mb exec php composer dump-autoload
```

### Permission Errors
```bash
# Fix file permissions (Linux/macOS)
sudo chown -R $(whoami):$(whoami) php/
chmod -R 755 php/
chmod -R 777 php/runtime php/web/assets php/web/uploads

# Fix upload directories
docker compose -p mb exec php mkdir -p /var/www/html/web/uploads/bank-statements
docker compose -p mb exec php chmod 777 /var/www/html/web/uploads/bank-statements
```

### Expense Widget Not Showing
```bash
# Rebuild autoload and clear cache
docker compose -p mb exec php composer dump-autoload
docker compose -p mb exec php php yii cache/flush-all

# Verify widget file exists
docker compose -p mb exec php ls -la /var/www/html/widgets/ExpenseHealthCheckWidget.php
```

### File Upload Failures
```bash
# Check upload directory exists and is writable
docker compose -p mb exec php mkdir -p /var/www/html/web/uploads/bank-statements
docker compose -p mb exec php chmod 777 /var/www/html/web/uploads/bank-statements

# Check PHP upload settings
docker compose -p mb exec php php -i | grep upload

# Verify file size limits in PHP
upload_max_filesize = 10M
post_max_size = 10M
```

### ZIP Download Fails
```bash
# Verify ZipArchive extension
docker compose -p mb exec php php -m | grep zip

# Check temporary directory permissions
docker compose -p mb exec php ls -la /var/www/html/runtime

# Check disk space
docker compose -p mb exec php df -h
```

### Container Won't Start
```bash
# View container logs
docker compose -p mb logs php
docker compose -p mb logs nginx
docker compose -p mb logs mariadb

# Check for port conflicts
docker compose -p mb ps -a

# Remove stopped containers and restart
docker compose -p mb down
docker compose -p mb up -d
```

### Clear All Cache and Rebuild
```bash
# Nuclear option - clean everything
docker compose -p mb down
docker compose -p mb exec php php yii cache/flush-all
rm -rf php/runtime/cache/*
rm -rf php/web/assets/*
docker compose -p mb up -d
```

---

## 📁 Project Structure

```
my-tax-manager/
├── php/                           # Application root
│   ├── config/                    # Configuration files
│   │   ├── web.php               # Web application config
│   │   ├── console.php           # Console application config
│   │   ├── db-local.php          # Database config (auto-generated)
│   │   ├── mail-local.php        # Mail config (auto-generated)
│   │   └── params.php            # Application parameters
│   ├── controllers/               # MVC Controllers
│   │   ├── TaxReturnController.php
│   │   ├── ExpenseController.php
│   │   ├── ExpenseSuggestionController.php
│   │   └── ...
│   ├── models/                    # ActiveRecord models
│   │   ├── TaxYearBankBalance.php
│   │   ├── ExpenseSuggestion.php
│   │   ├── Expense.php
│   │   └── ...
│   ├── views/                     # View templates
│   │   ├── tax-return/
│   │   ├── expense-suggestion/
│   │   └── ...
│   ├── commands/                  # Console commands
│   │   └── ExpenseHealthCheckController.php
│   ├── components/                # Application components
│   │   └── ExpenseHealthCheckService.php
│   ├── widgets/                   # Reusable widgets
│   │   ├── ExpenseHealthCheckWidget.php
│   │   └── views/
│   │       └── expense-health-check.php
│   ├── migrations/                # Database migrations (36+)
│   ├── base/                      # Base classes and behaviors
│   │   ├── BaseModel.php
│   │   ├── BaseWebController.php
│   │   ├── BaseMigration.php
│   │   └── ...
│   ├── web/                       # Public web directory
│   │   └── uploads/              # User uploads
│   │       └── bank-statements/  # Bank statement files
│   ├── runtime/                   # Runtime files
│   │   ├── cache/                # Cache files
│   │   └── logs/                 # Application logs
│   └── yii                       # Console entry script
├── local/                         # Docker configuration
│   ├── docker-compose.yml        # Services definition
│   ├── .env.example             # Environment template
│   ├── mariadb/                  # Database data (persisted)
│   └── php/                      # PHP/Nginx config
│       ├── nginx/               # Nginx configuration
│       └── php-fpm/             # PHP-FPM configuration
├── logs/                          # Application logs
│   ├── nginx/                    # Nginx access/error logs
│   │   ├── access.log
│   │   └── error.log
│   └── php/                      # PHP-FPM logs
├── .env                          # Environment config (auto-generated)
├── .gitignore                    # Git ignore rules
└── setup_linux_local.sh          # Automated setup script
```

---

## 📚 Additional Resources

- **Yii2 Documentation**: https://www.yiiframework.com/doc/guide/2.0/en
- **Yii2 API Reference**: https://www.yiiframework.com/doc/api/2.0
- **Kartik Widgets**: https://demos.krajee.com/
- **Docker Compose**: https://docs.docker.com/compose/
- **MariaDB**: https://mariadb.com/kb/en/documentation/
- **PHPSpreadsheet**: https://phpspreadsheet.readthedocs.io/
- **Bootstrap 5**: https://getbootstrap.com/docs/5.0/

---

## 🎓 Development Notes

### Base Classes
- **BaseModel**: Extended ActiveRecord with timestamp/audit behaviors
- **BaseWebController**: Authentication and error handling
- **BaseMigration**: Helper methods for migrations
- **BaseView**: Common view functionality
- **WebUser**: Custom user component
- **ManyToManyBehavior**: Junction table management

### Conventions
- **Table Prefix**: `mb_` for all tables
- **Date Format**: `Y-m-d` (YYYY-MM-DD)
- **Fiscal Year**: April 1 - March 31 (Sri Lankan tax year)
- **Currency**: LKR (Sri Lankan Rupee) default
- **Authentication**: `@` role for authenticated users
- **Error Handling**: Try-catch with `Yii::error()` logging

### Key Components
- **ExpenseHealthCheckService**: Pattern detection logic for recurring expenses
- **ExpenseHealthCheckWidget**: Dashboard alerts and pending suggestions
- **TaxReturnController**: Tax submission workflow and ZIP generation
- **ExpenseSuggestionController**: Expense health check UI (active/ignored views)

### Database Tables (mb_ prefix)
- `mb_user` - User accounts
- `mb_customer` - Customers
- `mb_vendor` - Vendors
- `mb_invoice` / `mb_invoice_item` - Invoicing
- `mb_expense` - Expenses with receipts
- `mb_expense_category` - Expense categories
- `mb_expense_suggestion` - Expense health check
- `mb_employee` / `mb_paysheet` - Payroll
- `mb_bank_account` - Bank accounts
- `mb_financial_transaction` - Transactions
- `mb_tax_year_snapshot` - Tax year data
- `mb_tax_year_bank_balance` - Year-end balances with statements
- `mb_capital_asset` - Assets
- And 20+ more tables...

---

## 📝 Status

**Application Status**: ✅ Fully Operational  
**Last Updated**: November 11, 2025  
**Version**: Based on Yii2 ~2.0.45  
**PHP Version**: 8.2+  
**Database**: MariaDB 10.2

**All Features Implemented:**
- ✅ Invoice & Expense Management
- ✅ Tax Return Submissions with Bank Statements
- ✅ Expense Health Check Automation
- ✅ Bank Account Management with Statement Upload
- ✅ Credit Card & Liability Tracking
- ✅ Complete ZIP Export (Excel + Documents)
- ✅ Docker Environment with All Extensions (ext-zip included)
- ✅ Automated Setup Script with MariaDB Wait Logic
- ✅ Security Measures Implemented
- ✅ Separate Active/Ignored Suggestion Views
- ✅ Auto-Reset for Permanent Ignores
- ✅ Dashboard Widget with Real-time Alerts

**Key Fixes Applied:**
- ✅ PHP ext-zip extension added to Dockerfile
- ✅ Database creation automated in setup script
- ✅ Migration timing fixed (waits for MariaDB)
- ✅ Environment variables properly configured
- ✅ Bank statement upload and ZIP export working
- ✅ Model property references corrected
- ✅ File upload enctype fixed
- ✅ FinancialTransaction properties fixed
- ✅ Expense suggestion views separated (active/ignored)
- ✅ Security: sensitive data moved to .env
- ✅ Docker network configuration fixed

---

## 📄 License

See LICENSE.md for details.

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**For support or questions, please open an issue in the repository.**

---

*This documentation consolidates all features and fixes. No historical information is included - only the current state of the application.*

