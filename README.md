# My Business - Yii2 Web Application

A comprehensive business management application built with Yii2 PHP framework, featuring invoice management, expense tracking, employee payroll, tax records, and financial reporting.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Project Structure](#project-structure)
- [Configuration](#configuration)
- [Database](#database)
- [Development](#development)
- [Production Deployment](#production-deployment)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

This is a Yii2 "basic" PHP web application designed for small to medium business management. The application provides tools for:

- Customer and vendor management
- Invoice generation and tracking
- Expense management with receipt uploads
- Employee records and payroll processing
- Tax record management and submissions
- Financial transaction tracking
- Capital asset management
- Year-end financial reporting

## ✨ Features

- **Invoice Management**: Create, send, and track invoices with PDF generation
- **Expense Tracking**: Record expenses with categories and receipt attachments
- **Employee Payroll**: Manage employee information and payroll details
- **Tax Records**: Track tax payments and submissions
- **Financial Transactions**: Monitor bank account transactions
- **Customer Portal**: Public invoice viewing via secure links
- **Multi-currency Support**: Handle transactions in different currencies (LKR default)
- **User Authentication**: Secure login system with access control
- **Responsive UI**: Bootstrap 5 based interface

## 🛠 Technology Stack

- **Backend**: PHP 8.2+ with Yii2 Framework
- **Database**: MariaDB 10.2
- **Web Server**: Nginx
- **Cache**: Redis
- **PDF Generation**: mPDF/TCPDF
- **Frontend**: Bootstrap 5, jQuery
- **Containerization**: Docker & Docker Compose

## 📦 Prerequisites

### For Local Development (Linux/macOS)

- Docker Desktop (or Docker Engine + Docker Compose)
- Git
- At least 4GB RAM available for Docker
- Ports 80, 3307 available

### For Windows Development

- Docker Desktop for Windows
- Git
- PowerShell or Command Prompt
- Administrative privileges (first-time setup)

---

## 🚀 Quick Start

### Linux/macOS Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd my-business
   ```

2. **Run the setup script**
   ```bash
   chmod +x setup_linux_local.sh
   ./setup_linux_local.sh
   ```

   This script will:
   - Create necessary configuration files
   - Build Docker images
   - Start all containers
   - **Wait for MariaDB to be ready**
   - **Create database automatically**
   - Run database migrations
   - Create default admin user

3. **Wait for initialization** (first run takes 2-3 minutes)
   ```bash
   # Monitor the PHP container logs
   docker logs -f mb-php
   ```

4. **Access the application**
   ```
   http://localhost
   ```

5. **Login with default credentials**
   - Username: `admin`
   - Email: `admin@example.com`
   - Password: `12345678`

### Windows Setup

1. **Clone the repository**
   ```powershell
   git clone <repository-url>
   cd my-business
   ```

2. **Run setup script** (Run as Administrator first time)
   ```powershell
   .\setup_win_local.bat
   ```

3. **Access the application**
   ```
   http://localhost
   ```

---

## 📁 Project Structure

```
my-business/
├── php/                          # Application root
│   ├── config/                   # Configuration files
│   │   ├── web.php              # Web application config
│   │   ├── console.php          # Console application config
│   │   ├── db-local.php         # Database configuration (auto-generated)
│   │   ├── mail-local.php       # Mail configuration (auto-generated)
│   │   └── params.php           # Application parameters
│   ├── controllers/             # MVC Controllers
│   ├── models/                  # ActiveRecord models
│   ├── views/                   # View templates
│   ├── commands/                # Console commands
│   ├── migrations/              # Database migrations
│   ├── base/                    # Base classes and behaviors
│   ├── components/              # Application components
│   ├── web/                     # Public web directory
│   ├── widgets/                 # Reusable UI widgets
│   └── yii                      # Console entry script
├── local/                       # Docker configuration
│   ├── docker-compose.yml       # Main compose file
│   ├── .env.example            # Environment variables template
│   └── php/
│       ├── nginx/              # Nginx configuration
│       └── php-fpm/            # PHP-FPM configuration
├── logs/                        # Application logs
├── .env                         # Environment configuration (auto-generated)
└── setup_linux_local.sh         # Setup script
```

## ⚙️ Configuration

### Environment Variables

The application uses environment variables for configuration. These are stored in `.env` file (auto-generated from `local/.env.example`).

Key configuration options:

```bash
# Application
APP_ENV=dev
YII_DEBUG=true
DOMAIN=yii.dev
APP_NAME="My Business"

# Database
DB_HOST=mariadb
DB_PORT=3306
DB_NAME=mybs
DB_USER=root
DB_PASSWD=mauFJcuf5dhRMQrjj
DB_PREFIX=mb_

# Email (SMTP)
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SENDER_EMAIL=noreply@example.com

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### Database Configuration

Database settings are managed in `php/config/db-local.php` (auto-generated). The file reads from environment variables:

```php
return [
    'host' => getenv('DB_HOST') ?: 'mariadb',
    'port' => getenv('DB_PORT') ?: '3306',
    'dbname' => getenv('DB_NAME') ?: 'mybs',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWD') ?: 'mauFJcuf5dhRMQrjj',
    'tablePrefix' => getenv('DB_PREFIX') ?: 'mb_'
];
```

## 💾 Database

### Table Prefix

All database tables use the prefix `mb_` by default (configurable via `DB_PREFIX`).

### Migrations

The application includes 36+ migrations that create tables for:
- Users and authentication
- Customers and vendors
- Invoices and invoice items
- Expenses and categories
- Employees and payroll
- Tax records and payments
- Financial transactions
- Capital assets
- Year-end submissions

To run migrations manually:
```bash
docker compose -p mb exec php php yii migrate/up --interactive=0
```

### Default User

After migrations, a default admin user is created:
- Username: `admin`
- Email: `admin@example.com`
- Password: `12345678`

**⚠️ Important**: Change this password immediately after first login!

## 🔧 Development

### Docker Services

The application runs four main services:

| Service | Container | Port | Description |
|---------|-----------|------|-------------|
| Nginx | mb-nginx | 80 | Web server |
| PHP-FPM | mb-php | 9000 | PHP application |
| MariaDB | mb-mariadb | 3307 | Database |
| Redis | mb-redis | 6379 | Cache |
| phpMyAdmin | mb-phpmyadmin | 8080 | Database management |

### Useful Commands

**View container status:**
```bash
docker compose -p mb ps
```

**View logs:**
```bash
docker compose -p mb logs php      # PHP logs
docker compose -p mb logs nginx    # Nginx logs
docker compose -p mb logs mariadb  # Database logs
docker compose -p mb logs redis    # Redis logs
```

**Follow logs in real-time:**
```bash
docker logs -f mb-php
```

**Execute commands in containers:**
```bash
# Run Yii console commands
docker compose -p mb exec php php yii migrate/create <migration_name>

# Access PHP container shell
docker compose -p mb exec php bash

# Access MySQL CLI
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj mybs
```

**Restart services:**
```bash
docker compose -p mb restart php
docker compose -p mb restart nginx
```

**Stop all services:**
```bash
docker compose -p mb down
```

**Start services:**
```bash
docker compose -p mb up -d
```

**Rebuild and restart:**
```bash
docker compose -p mb up -d --build
```

### Running Console Commands

```bash
# From host
docker compose -p mb exec php php yii <command>

# Examples
docker compose -p mb exec php php yii migrate/create add_column_to_table
docker compose -p mb exec php php yii cache/flush-all
```

### Installing New Dependencies

```bash
# Install PHP package
docker compose -p mb exec php composer require vendor/package

# Update dependencies
docker compose -p mb exec php composer update
```

### Code Generation with Gii

Gii is available in development mode:
```
http://localhost/gii
```

Use it to generate:
- Models
- CRUD controllers
- Forms
- Modules

## 🚀 Production Deployment

### Before Deploying

1. **Update environment variables**
   - Set `YII_DEBUG=false`
   - Set `APP_ENV=prod`
   - Use strong database passwords
   - Configure proper SMTP settings

2. **Change default admin password**

3. **Set up SSL/TLS certificates**

4. **Configure backup strategy**

5. **Set up monitoring and logging**

### Production Checklist

- [ ] Debug mode disabled
- [ ] Strong passwords set
- [ ] SSL certificates configured
- [ ] Database backups automated
- [ ] Error logging configured
- [ ] Email notifications working
- [ ] Regular security updates scheduled

## 🐛 Troubleshooting

### Port Already in Use

If port 80 or 3306 is already allocated:

```bash
# Check what's using the port
lsof -i :80
lsof -i :3306

# Stop the conflicting service or change ports in docker-compose.yml
```

### Database Connection Errors

If you see "Connection refused" or "Name does not resolve":

```bash
# Ensure all containers are on the same network
docker compose -p mb down
docker compose -p mb up -d

# Check network connectivity
docker compose -p mb exec php ping mariadb
```

### MariaDB Won't Start / Corruption

```bash
# Clean and reinitialize database
docker compose -p mb stop mariadb
rm -rf local/mariadb/*
docker compose -p mb up -d mariadb

# Wait for initialization, then run migrations
docker compose -p mb exec php php yii migrate/up --interactive=0
```

### Composer Install Failures

```bash
# Run composer install with more verbose output
docker compose -p mb exec php composer install -vvv

# Clear composer cache
docker compose -p mb exec php composer clear-cache
```

### Permission Issues

```bash
# Fix file permissions (Linux/macOS)
sudo chown -R $(whoami):$(whoami) php/
chmod -R 755 php/
```

### View Detailed Logs

```bash
# PHP errors
docker compose -p mb exec php tail -f /var/log/fpm-php.www.log

# Nginx errors
docker compose -p mb logs nginx | tail -50

# Application logs
tail -f php/runtime/logs/app.log
```

### Clear Cache

```bash
docker compose -p mb exec php php yii cache/flush-all
rm -rf php/runtime/cache/*
```

### Composer "ext-zip" Missing Error

If you see errors about missing `ext-zip` extension during setup:

**Error:**
```
phpoffice/phpspreadsheet requires ext-zip * -> it is missing from your system.
```

**Solution:**

This has been fixed in the Dockerfile. If you still see this error:

```bash
# Remove old images and rebuild
docker compose -p mb down
docker rmi php:latest
./setup_linux_local.sh
```

See [SETUP_SCRIPT_FIX.md](SETUP_SCRIPT_FIX.md) for details.

## 📚 Additional Resources

- [Yii2 Framework Documentation](https://www.yiiframework.com/doc/guide/2.0/en)
- [Yii2 API Reference](https://www.yiiframework.com/doc/api/2.0)
- [Docker Documentation](https://docs.docker.com/)
- [MariaDB Documentation](https://mariadb.com/kb/en/documentation/)

## 📝 Development Notes

### Base Classes

The project includes custom base classes in `php/base/`:

- **BaseModel**: Extended ActiveRecord with common functionality
- **BaseWebController**: Base controller with authentication
- **BaseMigration**: Extended migration helper
- **BaseView**: Common view functionality
- **WebUser**: Custom user component
- **ManyToManyBehavior**: Handles many-to-many relations

### Conventions

- **Table Prefix**: All tables use `mb_` prefix
- **Date Format**: `Y-m-d` (YYYY-MM-DD)
- **Currency**: LKR (Sri Lankan Rupee) by default
- **Authentication**: `@` role required for most actions
- **Error Handling**: Defensive try-catch with `Yii::error()` logging

### Key Paths

- Web root: `php/web/`
- Console entry: `php/yii`
- Config: `php/config/`
- Migrations: `php/migrations/`
- Models: `php/models/`
- Controllers: `php/controllers/`

---

## 📄 License

See LICENSE.md for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

---

**For support or questions, please open an issue in the repository.**
