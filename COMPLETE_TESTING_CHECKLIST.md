# Testing Checklist - Complete Setup Verification

**Date:** November 11, 2025  
**Purpose:** Verify both fixes work correctly

---

## 🧪 Pre-Test Setup

### Clean Environment

```bash
cd /Users/chana/Bee48/my-tax-manager

# Stop and remove everything
docker compose -p mb down -v

# Remove images
docker rmi php:latest nginx:latest 2>/dev/null || true

# Remove database data
rm -rf local/mariadb/*

# Verify clean state
docker ps | grep mb-
# Should show: nothing
```

---

## ✅ Test 1: Setup Script Execution

### Run Setup
```bash
./setup_linux_local.sh
```

### Expected Output Checklist

- [ ] "Building nginx image..." appears
- [ ] Nginx image builds successfully
- [ ] "Building php image..." appears
- [ ] PHP image builds successfully
- [ ] "Installing libzip-dev" appears in build logs
- [ ] "Installing zip extension" appears in build logs
- [ ] "Starting Docker containers..." appears
- [ ] Containers start successfully
- [ ] **"Waiting for MariaDB to be ready..."** appears
- [ ] Shows attempt counters (Attempt 1/30, 2/30, etc.)
- [ ] **"✓ MariaDB is ready!"** appears (within 30 seconds)
- [ ] **"Creating database if not exists..."** appears
- [ ] **"✓ Database 'mybs' ready"** appears
- [ ] "Setup Complete!" appears
- [ ] No error messages
- [ ] Script exits with success

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 2: PHP Extension Verification

### Check Zip Extension

```bash
docker compose -p mb exec php php -m | grep zip
```

**Expected Output:**
```
zip
```

- [ ] "zip" appears in output
- [ ] No error messages

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 3: Database Verification

### Check Database Exists

```bash
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj -e "SHOW DATABASES LIKE 'mybs';"
```

**Expected Output:**
```
+----------------+
| Database (mybs)|
+----------------+
| mybs           |
+----------------+
```

- [ ] Database "mybs" is listed
- [ ] No error messages

### Check Database Charset

```bash
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj -e "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='mybs';"
```

**Expected Output:**
```
+----------------------------+------------------------+
| DEFAULT_CHARACTER_SET_NAME | DEFAULT_COLLATION_NAME |
+----------------------------+------------------------+
| utf8mb4                    | utf8mb4_unicode_ci     |
+----------------------------+------------------------+
```

- [ ] Character set is "utf8mb4"
- [ ] Collation is "utf8mb4_unicode_ci"

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 4: PHP Container Logs

### Check Post-Install Logs

```bash
docker logs mb-php 2>&1 | tail -50
```

### Expected Messages Checklist

- [ ] "Running post-install setup..." appears
- [ ] "Installing Composer dependencies..." appears
- [ ] Composer packages install successfully
- [ ] **"Waiting for database to be ready..."** appears
- [ ] Shows connection attempts
- [ ] **"✓ Database connection successful!"** appears
- [ ] "Running database migrations..." appears
- [ ] **"✓ Database migrations completed successfully!"** appears
- [ ] "Default admin user created:" appears
- [ ] "Username: admin" appears
- [ ] "Post-install setup completed!" appears
- [ ] No fatal errors
- [ ] No "Unknown database" errors
- [ ] No "ext-zip" errors

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 5: Composer Package Verification

### Check phpoffice/phpspreadsheet Installed

```bash
docker compose -p mb exec php composer show phpoffice/phpspreadsheet
```

**Expected Output:**
```
name     : phpoffice/phpspreadsheet
descrip. : PHP spreadsheet library
versions : * 1.30.1
...
```

- [ ] Package is installed
- [ ] Version shown (1.30.1 or higher)
- [ ] No warnings about missing extensions

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 6: Database Tables

### Check Tables Created

```bash
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj mybs -e "SHOW TABLES;"
```

**Expected Output:**
```
+---------------------------+
| Tables_in_mybs            |
+---------------------------+
| mb_migration              |
| mb_user                   |
| mb_bank_account           |
| mb_asset                  |
| ... (more tables)         |
+---------------------------+
```

- [ ] At least 20+ tables listed
- [ ] Tables have "mb_" prefix
- [ ] mb_user table exists
- [ ] mb_migration table exists

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 7: Admin User Created

### Check Admin User

```bash
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj mybs -e "SELECT id, username, email FROM mb_user WHERE username='admin';"
```

**Expected Output:**
```
+----+----------+-------------------+
| id | username | email             |
+----+----------+-------------------+
|  1 | admin    | admin@example.com |
+----+----------+-------------------+
```

- [ ] Admin user exists
- [ ] Username is "admin"
- [ ] Email is "admin@example.com"

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 8: Container Status

### Check All Containers Running

```bash
docker compose -p mb ps
```

**Expected Output:**
```
NAME             STATUS    PORTS
mb-mariadb       Up        0.0.0.0:3307->3306/tcp
mb-nginx         Up        0.0.0.0:80->80/tcp
mb-php           Up        9000/tcp
mb-redis         Up        6379/tcp
```

- [ ] mb-mariadb is "Up"
- [ ] mb-nginx is "Up"
- [ ] mb-php is "Up"
- [ ] mb-redis is "Up"
- [ ] All containers healthy (no "Restarting" status)

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 9: Application Accessible

### Check HTTP Response

```bash
curl -I http://localhost
```

**Expected Output:**
```
HTTP/1.1 302 Found
Server: nginx
Location: /site/login
...
```

- [ ] Returns HTTP 302 (redirect)
- [ ] Location header points to /site/login
- [ ] Server is nginx
- [ ] No error responses (500, 503, etc.)

### Open in Browser

```bash
open http://localhost
# or manually visit: http://localhost
```

- [ ] Page loads successfully
- [ ] Redirects to login page
- [ ] Login form is visible
- [ ] No error messages
- [ ] No "database connection" errors

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 10: Login Functionality

### Login with Admin Credentials

**URL:** http://localhost  
**Username:** admin  
**Password:** 12345678

- [ ] Login form accepts credentials
- [ ] Successfully logs in
- [ ] Redirects to dashboard/home page
- [ ] User menu shows "admin"
- [ ] No error messages

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 11: Excel Export Feature

### Test Tax Return Export

1. Navigate to: **Tax Returns** (if available in menu)
2. View any tax return report
3. Click **"Download ZIP (Excel + Bank Statements)"** or similar export button

**Checklist:**
- [ ] Button/link is clickable
- [ ] File downloads successfully
- [ ] File is a ZIP or Excel file
- [ ] No "ZipArchive" errors
- [ ] No "ext-zip" errors
- [ ] File opens correctly

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 12: Database Connection Stability

### Run Migrations Again (Should Show "No new migrations")

```bash
docker compose -p mb exec php php yii migrate/up --interactive=0
```

**Expected Output:**
```
No new migrations found. Your system is up-to-date.
```

- [ ] No errors
- [ ] Shows "up-to-date" message
- [ ] No connection errors

**Result:** ⬜ PASS / ⬜ FAIL

---

## ✅ Test 13: Container Restart Test

### Test Stability After Restart

```bash
# Restart all containers
docker compose -p mb restart

# Wait 30 seconds
sleep 30

# Test application
curl -I http://localhost
```

**Checklist:**
- [ ] Containers restart successfully
- [ ] Application accessible after restart
- [ ] No errors in logs
- [ ] Database connection works
- [ ] Can still login

**Result:** ⬜ PASS / ⬜ FAIL

---

## 📊 Final Results

### Test Summary

| Test | Result | Notes |
|------|--------|-------|
| 1. Setup Script | ⬜ PASS / ⬜ FAIL | |
| 2. Zip Extension | ⬜ PASS / ⬜ FAIL | |
| 3. Database | ⬜ PASS / ⬜ FAIL | |
| 4. PHP Logs | ⬜ PASS / ⬜ FAIL | |
| 5. Composer Packages | ⬜ PASS / ⬜ FAIL | |
| 6. Database Tables | ⬜ PASS / ⬜ FAIL | |
| 7. Admin User | ⬜ PASS / ⬜ FAIL | |
| 8. Container Status | ⬜ PASS / ⬜ FAIL | |
| 9. Application Access | ⬜ PASS / ⬜ FAIL | |
| 10. Login | ⬜ PASS / ⬜ FAIL | |
| 11. Excel Export | ⬜ PASS / ⬜ FAIL | |
| 12. DB Stability | ⬜ PASS / ⬜ FAIL | |
| 13. Restart Test | ⬜ PASS / ⬜ FAIL | |

### Overall Result

**Total Tests:** 13  
**Passed:** ___/13  
**Failed:** ___/13  

**Status:** ⬜ ALL PASSED ✅ / ⬜ SOME FAILED ❌

---

## 🐛 If Any Test Fails

### Test 1 Failed (Setup Script)
- Check Docker is running: `docker ps`
- Check disk space: `df -h`
- Check setup script syntax: `bash -n setup_linux_local.sh`
- View full output for error details

### Test 2 Failed (Zip Extension)
- Check Dockerfile has zip extension lines
- Rebuild image: `docker rmi php:latest && ./setup_linux_local.sh`
- Check build logs: Look for "Installing zip extension"

### Test 3-7 Failed (Database)
- Check MariaDB logs: `docker logs mb-mariadb`
- Manually create database: See TROUBLESHOOTING.md
- Check wait loop completed in setup script

### Test 9-11 Failed (Application)
- Check nginx logs: `docker logs mb-nginx`
- Check PHP logs: `docker logs mb-php`
- Check all containers running: `docker compose -p mb ps`
- Verify port 80 not in use: `lsof -i :80`

---

## 📝 Notes Section

Use this space to record any issues, observations, or additional notes:

```
Date: ___________
Tester: _________

Notes:








```

---

**Testing Document Version:** 1.0  
**Created:** November 11, 2025  
**Purpose:** Verify ext-zip and database creation fixes

