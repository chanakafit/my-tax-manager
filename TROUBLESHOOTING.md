# Troubleshooting Guide

## Common Issues and Solutions

### 🚫 Port Already in Use

**Error:** `Bind for 0.0.0.0:80 failed: port is already allocated`

**Solution 1: Find and stop the conflicting service**
```bash
# Find what's using port 80
lsof -i :80

# Example output might show nginx, apache, or another service
# Stop that service, e.g.:
sudo systemctl stop nginx
sudo systemctl stop apache2
```

**Solution 2: Change the port**
Edit `local/docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8080:80"  # Change to 8080 or another available port
```

Then access via `http://localhost:8080`

---

### 🔌 Database Connection Refused

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Cause:** MariaDB container not ready or network issue

**Solution:**
```bash
# 1. Check if MariaDB is running
docker compose -p mb ps

# 2. Check MariaDB logs
docker compose -p mb logs mariadb

# 3. Wait for MariaDB to fully start (can take 10-30 seconds)
docker compose -p mb exec mariadb mysqladmin ping -h localhost -pmauFJcuf5dhRMQrjj

# 4. If still failing, restart containers
docker compose -p mb restart mariadb
sleep 15
docker compose -p mb restart php
```

---

### 💾 Database Not Found

**Error:** `SQLSTATE[HY000] [1049] Unknown database 'mybs'`

**Cause:** Database was not created before application tried to connect

**Solution:**

This is now **automatically fixed** in the setup script, but if you encounter it:

```bash
# Create the database manually
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj -e "CREATE DATABASE IF NOT EXISTS mybs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
docker compose -p mb exec php php yii migrate/up --interactive=0
```

**To prevent this in future:**
- Use the updated `./setup_linux_local.sh` script which now creates the database automatically
- The setup script now waits for MariaDB to be ready before proceeding

See [DATABASE_CREATION_FIX.md](DATABASE_CREATION_FIX.md) for details.

---

### 🌐 Name Does Not Resolve

**Error:** `php_network_getaddresses: getaddrinfo for mariadb failed: Name does not resolve`

**Cause:** Containers not on same Docker network

**Solution:**
```bash
# Recreate containers with proper network
docker compose -p mb down
docker compose -p mb up -d

# Verify network connectivity
docker compose -p mb exec php ping -c 2 mariadb
```

---

### 💾 Database Corruption

**Error:** `InnoDB: Invalid flags 0x15 in ./ibdata1` or `Data structure corruption`

**Cause:** Incompatible MariaDB version data files

**Solution:**
```bash
# 1. Stop MariaDB
docker compose -p mb stop mariadb

# 2. Backup existing data (if needed)
cp -r local/mariadb local/mariadb.backup

# 3. Remove corrupted data
rm -rf local/mariadb/*

# 4. Restart MariaDB (will initialize fresh)
docker compose -p mb up -d mariadb

# 5. Wait for initialization
sleep 15

# 6. Run migrations
docker compose -p mb exec php php yii migrate/up --interactive=0
```

---

### 📦 Composer Install Failures

**Error:** Package installation fails or dependency conflicts

**Solution 1: Clear cache and retry**
```bash
docker compose -p mb exec php composer clear-cache
docker compose -p mb exec php composer install --no-interaction
```

**Solution 2: Update dependencies**
```bash
docker compose -p mb exec php composer update
```

**Solution 3: Install specific package**
```bash
# If certain packages fail, skip them initially
docker compose -p mb exec php composer install --ignore-platform-reqs
```

---

### 🔒 Permission Denied

**Error:** Permission denied errors when accessing files

**Linux/macOS Solution:**
```bash
# Fix ownership
sudo chown -R $(whoami):$(whoami) php/ local/ logs/

# Fix permissions
chmod -R 755 php/
chmod -R 755 local/
chmod -R 777 logs/
chmod -R 777 php/runtime
chmod -R 777 php/web/assets
```

**Windows Solution:**
- Ensure Docker Desktop has file sharing enabled
- Check folder permissions in Windows Explorer
- Run Docker Desktop as Administrator

---

### 🐌 Slow Performance

**Issue:** Application loading slowly

**Solutions:**

1. **Increase Docker resources**
   - Docker Desktop → Settings → Resources
   - Allocate more CPU (4+ cores) and RAM (4GB+)

2. **Clear all caches**
   ```bash
   docker compose -p mb exec php php yii cache/flush-all
   rm -rf php/runtime/cache/*
   rm -rf php/web/assets/*
   ```

3. **Disable debug mode** (for production-like testing)
   Edit `.env`:
   ```
   YII_DEBUG=false
   APP_ENV=prod
   ```

---

### 📝 Migrations Fail

**Error:** Migration command fails or hangs

**Solution 1: Check database connection**
```bash
# Verify database is accessible
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj -e "SHOW DATABASES;"
```

**Solution 2: Manually mark migrations**
```bash
# Access database
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj mybs

# Mark specific migration as applied
INSERT INTO mb_migration (version, apply_time) VALUES ('m250816_081754_create_employee_table', UNIX_TIMESTAMP());
```

**Solution 3: Reset migrations** (⚠️ loses data)
```bash
# Drop and recreate database
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj -e "DROP DATABASE mybs; CREATE DATABASE mybs;"

# Run all migrations fresh
docker compose -p mb exec php php yii migrate/up --interactive=0
```

---

### 🖼️ Assets Not Loading

**Issue:** CSS/JS files not loading, pages look broken

**Solution:**
```bash
# Clear assets
rm -rf php/web/assets/*

# Fix permissions
chmod -R 777 php/web/assets/

# Restart nginx
docker compose -p mb restart nginx
```

---

### 🔍 Can't Access Gii

**Error:** 404 when accessing http://localhost/gii

**Cause:** Gii only works in debug mode

**Solution:**
1. Verify `.env` has:
   ```
   YII_DEBUG=true
   APP_ENV=dev
   ```

2. Restart PHP container:
   ```bash
   docker compose -p mb restart php
   ```

3. Access from allowed IP (localhost should work)

---

### 📧 Email Not Sending

**Issue:** Email functionality not working

**Check Configuration:**
```bash
# View mail config
docker compose -p mb exec php cat config/mail-local.php
```

**Update SMTP Settings in `.env`:**
```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SENDER_EMAIL=your-email@gmail.com
```

**Restart to apply:**
```bash
docker compose -p mb restart php
```

---

### 🔧 Container Won't Start

**Issue:** Container exits immediately or won't start

**Diagnosis:**
```bash
# Check container status
docker compose -p mb ps -a

# View container logs
docker compose -p mb logs [service-name]

# Example:
docker compose -p mb logs php
docker compose -p mb logs nginx
```

**Common Fixes:**

1. **Port conflict:**
   ```bash
   # Change port in docker-compose.yml
   ```

2. **Volume mount issue:**
   ```bash
   # Check if paths exist
   ls -la php/
   ls -la local/
   ```

3. **Rebuild containers:**
   ```bash
   docker compose -p mb down
   docker compose -p mb up -d --build
   ```

---

### 🗄️ Database Connection from Host

**Issue:** Can't connect to database from host machine

**Solution:**

Use external port `3307`:
```bash
# Command line
mysql -h 127.0.0.1 -P 3307 -uroot -pmauFJcuf5dhRMQrjj mybs

# Or in your database tool:
Host: 127.0.0.1
Port: 3307
User: root
Password: mauFJcuf5dhRMQrjj
Database: mybs
```

---

### 🧹 Complete Reset

**When all else fails, start fresh:**

```bash
# 1. Stop and remove everything
docker compose -p mb down -v

# 2. Remove all data
rm -rf local/mariadb/*
rm -rf php/runtime/cache/*
rm -rf php/web/assets/*

# 3. Rebuild from scratch
./setup_linux_local.sh

# 4. Monitor startup
docker logs -f mb-php
```

---

## 🔍 Debugging Tips

### Enable Verbose Logging

Edit `php/config/web.php`:
```php
$config = [
    // ... existing code ...
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,  // Increase trace level
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info', 'trace'],  // Add 'info' and 'trace'
                ],
            ],
        ],
    ],
];
```

### View Real-time Logs

```bash
# PHP application logs
docker compose -p mb exec php tail -f runtime/logs/app.log

# PHP-FPM logs
docker logs -f mb-php

# Nginx access logs
docker compose -p mb logs -f nginx

# All logs combined
docker compose -p mb logs -f
```

### Test Database Connection

```bash
# From PHP container
docker compose -p mb exec php php -r "echo (new PDO('mysql:host=mariadb;dbname=mybs', 'root', 'mauFJcuf5dhRMQrjj'))->query('SELECT 1')->fetchColumn();"
```

### Check PHP Configuration

```bash
# View PHP info
docker compose -p mb exec php php -i

# Check loaded extensions
docker compose -p mb exec php php -m

# Check PHP version
docker compose -p mb exec php php -v
```

---

## 📞 Getting Help

If you're still stuck:

1. **Check logs first:**
   ```bash
   docker compose -p mb logs
   ```

2. **Verify all services running:**
   ```bash
   docker compose -p mb ps
   ```

3. **Test connectivity:**
   ```bash
   docker compose -p mb exec php ping mariadb
   curl -I http://localhost
   ```

4. **Create an issue** with:
   - Error message
   - Log output
   - Steps to reproduce
   - System info (OS, Docker version)

---

**Remember:** Most issues are resolved by:
1. Checking logs
2. Restarting containers
3. Ensuring proper networking
4. Verifying file permissions

