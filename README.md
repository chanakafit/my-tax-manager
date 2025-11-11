# My Tax Manager - Business & Tax Management System

A comprehensive business management application built with Yii2 PHP framework, featuring invoice management, expense tracking, employee payroll, tax return submissions, and financial reporting with advanced automation features.

## 📋 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Quick Start](#quick-start)
- [Core Functionality](#core-functionality)
- [Advanced Features](#advanced-features)
- [Docker Services](#docker-services)
- [Configuration](#configuration)
- [Security](#security)
- [Common Commands](#common-commands)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

My Tax Manager is a Yii2-based web application designed for small to medium businesses to manage their financial operations, track expenses, process payroll, and prepare comprehensive tax returns. The system automates recurring expense detection, supports bank statement uploads, and generates complete tax submission packages.

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
- **Automated Cron Jobs**: Schedule expense health checks and other maintenance tasks

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

