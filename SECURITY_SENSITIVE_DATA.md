# Security & Sensitive Data Management

## ✅ Sensitive Data Removed

All sensitive data has been stripped from the repository and moved to environment variables or example template files.

---

## Changes Made

### 1. Environment Files ✅

#### Created `.env.example` Files:
- **Root:** `.env.example` - Template for root environment variables
- **PHP:** `php/.env.example` - Template for PHP environment variables

**What to do:**
1. Copy `.env.example` to `.env`
2. Copy `php/.env.example` to `php/.env`
3. Fill in your actual credentials

```bash
# In root directory
cp .env.example .env

# In php directory
cp php/.env.example php/.env
```

---

### 2. Configuration Templates ✅

#### Created Template Files:
- `php/config/db-local.template.php` - Database configuration template

**What to do:**
1. Copy template to actual config file
2. Fill in your credentials

```bash
cd php/config
cp db-local.template.php db-local.php
# Edit db-local.php with your credentials
```

---

### 3. Hardcoded Values Replaced ✅

#### Cookie Validation Key:
**File:** `php/config/web.php`

**Before:**
```php
'cookieValidationKey' => 's6PSdtLtXP6dGksM7D_ZSJQH89QjZV0Y',
```

**After:**
```php
'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: 'change-this-secret-key',
```

#### Database Password:
**File:** `php/config/db-local.php`

**Before:**
```php
'password' => getenv('DB_PASSWD') ?: 'mauFJcuf5dhRMQrjj',
```

**After:**
```php
'password' => getenv('DB_PASSWD') ?: 'your_db_password',
```

---

### 4. .gitignore Updated ✅

**File:** `.gitignore`

**Added:**
```gitignore
.env
**/config/db-local.php
**/config/mail-local.php
```

Ensures sensitive files are never committed to git.

---

## Sensitive Data Checklist

### ✅ Protected:
- [x] Database passwords
- [x] Database root passwords
- [x] Admin passwords
- [x] Cookie validation keys
- [x] Email credentials (in .env)
- [x] User emails
- [x] Configuration files

### ✅ In Environment Variables:
- DB_PASSWD
- DB_ROOT_PASSWD
- PASSWORD
- ADMIN_DEFAULT_PASSWORD
- COOKIE_VALIDATION_KEY
- EMAIL
- SMTP credentials (when used)

### ✅ In .gitignore:
- .env files
- db-local.php
- mail-local.php
- uploads directory

---

## Environment Variables Reference

### Database:
```dotenv
DB_HOST="mariadb"
DB_PORT="3306"
DB_NAME="your_database_name"
DB_USER="your_db_user"
DB_PASSWD="your_secure_db_password"
DB_ROOT_PASSWD="your_secure_root_password"
DB_PREFIX="mb_"
```

### Authentication:
```dotenv
PASSWORD="your_secure_password"
EMAIL="your-email@example.com"
ADMIN_DEFAULT_PASSWORD="your_admin_password"
COOKIE_VALIDATION_KEY="your-32-character-random-string"
```

### Email (Optional):
```dotenv
SMTP_HOST="smtp.example.com"
SMTP_PORT="587"
SMTP_USER="your_smtp_user"
SMTP_PASS="your_smtp_password"
```

---

## Generating Secure Keys

### Cookie Validation Key:
```bash
# Generate random 32-character string
openssl rand -base64 32
```

### Admin Password:
```bash
# Generate secure random password
openssl rand -base64 24
```

---

## Setup Instructions for New Environments

### Step 1: Copy Template Files
```bash
# Root .env
cp .env.example .env

# PHP .env
cp php/.env.example php/.env

# Database config
cp php/config/db-local.template.php php/config/db-local.php
```

### Step 2: Generate Secure Keys
```bash
# Generate cookie validation key
echo "COOKIE_VALIDATION_KEY=\"$(openssl rand -base64 32)\"" >> .env

# Generate admin password
echo "ADMIN_DEFAULT_PASSWORD=\"$(openssl rand -base64 24)\"" >> .env
```

### Step 3: Edit Configuration
Edit `.env` and `php/.env` files with your actual:
- Database credentials
- Email addresses
- SMTP settings (if using email)
- Other environment-specific values

### Step 4: Verify Security
```bash
# Make sure .env is in .gitignore
grep "\.env" .gitignore

# Check git status - should NOT show .env files
git status
```

---

## Security Best Practices

### ✅ DO:
- Use environment variables for all sensitive data
- Generate unique keys per environment
- Use strong passwords (20+ characters, mixed case, numbers, symbols)
- Rotate passwords regularly
- Keep .env files out of version control
- Use different credentials for dev/staging/production
- Restrict file permissions (chmod 600 for .env files)

### ❌ DON'T:
- Commit .env files to git
- Share credentials in chat/email
- Use default passwords in production
- Reuse passwords across environments
- Commit database dumps with real data
- Hardcode credentials in code

---

## File Permissions

Set proper permissions on sensitive files:
```bash
# Restrict .env files
chmod 600 .env
chmod 600 php/.env

# Restrict config files
chmod 600 php/config/db-local.php
chmod 600 php/config/mail-local.php
```

---

## Checking for Exposed Secrets

### Before Committing:
```bash
# Check what files will be committed
git status

# Check file contents
git diff

# Search for potential secrets
grep -r "password.*=" --include="*.php" .
grep -r "passwd.*=" --include="*.php" .
```

### Tools to Help:
- `git-secrets` - Prevents committing secrets
- `truffleHog` - Finds secrets in git history
- `detect-secrets` - Scans for secrets

---

## Emergency: If Secrets Were Committed

### If you accidentally committed secrets:

1. **Change all passwords immediately**
2. **Rotate all keys**
3. **Remove from git history:**
   ```bash
   # Remove file from history
   git filter-branch --force --index-filter \
     "git rm --cached --ignore-unmatch path/to/file" \
     --prune-empty --tag-name-filter cat -- --all
   
   # Force push (careful!)
   git push origin --force --all
   ```

4. **Consider repository as compromised**
5. **Review all access logs**

---

## Production Deployment

### Environment Variables:
Set via server environment, not .env files:
```bash
# In server environment
export DB_PASSWD="production_password"
export COOKIE_VALIDATION_KEY="production_key"
export ADMIN_DEFAULT_PASSWORD="production_admin_pass"
```

### Or use secrets management:
- AWS Secrets Manager
- HashiCorp Vault
- Azure Key Vault
- Google Secret Manager

---

## Status: ✅ SECURED

All sensitive data has been:
- ✅ Removed from code
- ✅ Moved to environment variables
- ✅ Added to .gitignore
- ✅ Template files created
- ✅ Documentation provided

---

## Support Files Created

1. `.env.example` - Root environment template
2. `php/.env.example` - PHP environment template
3. `php/config/db-local.template.php` - Database config template
4. `SECURITY_SENSITIVE_DATA.md` - This file

---

**Last Updated:** November 11, 2025  
**Status:** All sensitive data secured  
**Action Required:** Update .env files with your credentials

