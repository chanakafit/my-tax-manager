# Testing the Setup Script Fix

**Date:** November 11, 2025  
**Purpose:** Verify the ext-zip fix works correctly

---

## 🧪 Test Procedure

### Prerequisites

Before testing, ensure:
- Docker Desktop is running
- Ports 80, 3307, 8080 are available
- You have at least 4GB RAM available

---

## Test 1: Clean Installation ✅

### Steps

1. **Clean up any existing containers:**
   ```bash
   cd /Users/chana/Bee48/my-tax-manager
   docker compose -p mb down
   docker rmi php:latest nginx:latest 2>/dev/null || true
   rm -rf local/mariadb/*
   ```

2. **Run setup script:**
   ```bash
   ./setup_linux_local.sh
   ```

3. **Monitor PHP container logs:**
   ```bash
   docker logs -f mb-php
   ```

### Expected Results ✅

You should see:

```
Building php image...
Step 7/9 : RUN apk add --no-cache libzip-dev && docker-php-ext-install zip && docker-php-ext-enable zip
 ---> Running in...
 ---> Installing libzip-dev...
 ---> Installing zip extension...
Successfully built [image-id]
Successfully tagged php:latest
```

Then in the container logs:

```
Loading composer repositories with package information
Installing dependencies from lock file (including require-dev)
Verifying lock file contents can be installed on current platform.
Package operations: X installs, 0 updates, 0 removals
  - Installing vendor/package...
  - Installing phpoffice/phpspreadsheet...  [SUCCESS]
Generating autoload files
```

**No "ext-zip is missing" errors** ✅

---

## Test 2: Verify Extension Loaded ✅

```bash
docker compose -p mb exec php php -m | grep zip
```

**Expected Output:**
```
zip
```

---

## Test 3: Verify All Extensions ✅

```bash
docker compose -p mb exec php php -m
```

**Expected Extensions:**
- [x] Core
- [x] ctype
- [x] curl
- [x] date
- [x] dom
- [x] fileinfo
- [x] filter
- [x] gd
- [x] hash
- [x] iconv
- [x] json
- [x] libxml
- [x] mbstring
- [x] mysqli
- [x] mysqlnd
- [x] openssl
- [x] pcre
- [x] PDO
- [x] pdo_mysql
- [x] pdo_sqlite
- [x] Phar
- [x] redis
- [x] session
- [x] SimpleXML
- [x] sodium
- [x] SPL
- [x] sqlite3
- [x] standard
- [x] tokenizer
- [x] xml
- [x] xmlreader
- [x] xmlwriter
- [x] **zip** ✅
- [x] zlib

---

## Test 4: Application Accessible ✅

```bash
curl -I http://localhost
```

**Expected Output:**
```
HTTP/1.1 302 Found
Server: nginx
Location: /site/login
```

---

## Test 5: Login Works ✅

1. Open browser: `http://localhost`
2. Should redirect to login page
3. Login with:
   - Username: `admin`
   - Password: `12345678`
4. Should successfully log in and see dashboard

---

## Test 6: Excel Export Feature ✅

1. Navigate to: Tax Returns → View Report (any year)
2. Click "Download ZIP (Excel + Bank Statements)"
3. File should download successfully (no ZipArchive error)

---

## Test 7: Container Status ✅

```bash
docker compose -p mb ps
```

**Expected Output:**
```
NAME             STATUS    PORTS
mb-mariadb       Up        0.0.0.0:3307->3306/tcp
mb-nginx         Up        0.0.0.0:80->80/tcp
mb-php           Up        9000/tcp
mb-phpmyadmin    Up        0.0.0.0:8080->80/tcp
mb-redis         Up        6379/tcp
```

All containers should be **Up** ✅

---

## Test 8: Database Migrations ✅

```bash
docker compose -p mb exec php php yii migrate/history
```

**Expected Output:**
```
Total 36 migrations have been applied before:
    m130524_201442_init
    m190124_110200_add_verification_token_column_to_user_table
    ...
    (36 migrations listed)
```

---

## Test 9: Composer Dependencies ✅

```bash
docker compose -p mb exec php composer show | grep phpspreadsheet
```

**Expected Output:**
```
phpoffice/phpspreadsheet    1.30.1  PHP spreadsheet library
```

Should **not** show any warnings about missing extensions ✅

---

## Test 10: PHP Info Check ✅

```bash
docker compose -p mb exec php php -i | grep -A 5 "zip"
```

**Expected Output:**
```
zip

Zip => enabled
Zip version => 1.x.x
Libzip version => 1.x.x
```

---

## 🎯 Success Criteria

All tests should pass with these results:

- ✅ Setup script completes without errors
- ✅ No "ext-zip is missing" messages
- ✅ Composer install succeeds
- ✅ All containers running
- ✅ Application accessible
- ✅ Login works
- ✅ Excel export works
- ✅ Zip extension loaded
- ✅ Database migrations applied
- ✅ No fatal errors in logs

---

## 📊 Test Results Template

Copy this and fill in your results:

```
TEST RESULTS - [Date/Time]
================================

[ ] Test 1: Clean Installation
    Status: PASS / FAIL
    Notes:

[ ] Test 2: Extension Loaded
    Status: PASS / FAIL
    Output:

[ ] Test 3: All Extensions Present
    Status: PASS / FAIL
    Missing (if any):

[ ] Test 4: Application Accessible
    Status: PASS / FAIL
    Response:

[ ] Test 5: Login Works
    Status: PASS / FAIL
    Notes:

[ ] Test 6: Excel Export
    Status: PASS / FAIL
    Notes:

[ ] Test 7: Container Status
    Status: PASS / FAIL
    Down containers (if any):

[ ] Test 8: Database Migrations
    Status: PASS / FAIL
    Count:

[ ] Test 9: Composer Dependencies
    Status: PASS / FAIL
    Warnings (if any):

[ ] Test 10: PHP Info
    Status: PASS / FAIL
    Zip version:

OVERALL: PASS / FAIL
================================
```

---

## 🐛 If Tests Fail

### Test 1 Fails (Build Error)

```bash
# Check Dockerfile syntax
cat local/php/php-fpm/Dockerfile | grep -A 3 "zip"

# Try building manually with verbose output
docker build . -t php:test -f local/php/php-fpm/Dockerfile --progress=plain --no-cache
```

### Test 2 Fails (Extension Not Loaded)

```bash
# Check if extension file exists
docker compose -p mb exec php ls -la /usr/local/lib/php/extensions/

# Check PHP configuration
docker compose -p mb exec php php --ini
```

### Test 5 Fails (Can't Login)

```bash
# Check database connection
docker compose -p mb exec php php yii migrate/history

# Recreate admin user
docker compose -p mb exec php php yii seed/admin
```

### Test 6 Fails (Excel Export Error)

```bash
# Check logs for detailed error
docker logs mb-php | tail -100

# Verify phpspreadsheet is installed
docker compose -p mb exec php composer show phpoffice/phpspreadsheet
```

---

## 📝 Reporting Issues

If any test fails, collect this information:

```bash
# System info
uname -a
docker --version

# Container logs
docker logs mb-php > php_logs.txt
docker logs mb-nginx > nginx_logs.txt

# Extension list
docker compose -p mb exec php php -m > extensions.txt

# Dockerfile content
cat local/php/php-fpm/Dockerfile > dockerfile_content.txt

# Docker compose config
cat local/docker-compose.yml > compose_content.txt
```

---

## ✅ Cleanup After Testing

```bash
# Stop containers (keep data)
docker compose -p mb down

# Complete cleanup (removes data)
docker compose -p mb down -v
rm -rf local/mariadb/*
```

---

**Test Document Version:** 1.0  
**Created:** November 11, 2025  
**Purpose:** Verify ext-zip fix in setup script

