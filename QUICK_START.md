# Quick Start Guide

## 🚀 Get Started in 3 Steps

### 1. Run Setup
```bash
./setup_linux_local.sh
```

**The script automatically:**
- ✅ Builds Docker images
- ✅ Starts containers
- ✅ Waits for MariaDB
- ✅ Creates database 'mybs'
- ✅ Runs migrations
- ✅ Creates admin user

### 2. Wait for Containers (2-3 minutes)
```bash
docker logs -f mb-php
```

### 3. Access Application
```
http://localhost
```

**Login:** admin / admin123

---

## 📝 Common Commands

### Container Management
```bash
# View status
docker compose -p mb ps

# View logs
docker logs -f mb-php
docker logs -f mb-nginx

# Restart services
docker compose -p mb restart php
docker compose -p mb restart nginx

# Stop everything
docker compose -p mb down

# Start everything
docker compose -p mb up -d
```

### Database Commands
```bash
# Run migrations
docker compose -p mb exec php php yii migrate/up --interactive=0

# Access database
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj mybs

# Create migration
docker compose -p mb exec php php yii migrate/create <name>
```

### Application Commands
```bash
# Clear cache
docker compose -p mb exec php php yii cache/flush-all

# Run console command
docker compose -p mb exec php php yii <command>

# Composer install
docker compose -p mb exec php composer install

# Access PHP shell
docker compose -p mb exec php bash
```

---

## 🔧 Troubleshooting

### Problem: Port 80 in use
```bash
# Find what's using port 80
lsof -i :80

# Or change port in docker-compose.yml
ports:
  - "8080:80"  # Use port 8080 instead
```

### Problem: Database won't connect
### Problem: Database 'mybs' not found
```bash
# Create database manually
docker compose -p mb exec mariadb mysql -uroot -pmauFJcuf5dhRMQrjj -e "CREATE DATABASE IF NOT EXISTS mybs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
docker compose -p mb exec php php yii migrate/up --interactive=0
```
Note: The setup script now creates this automatically, but you can use this if needed.

```bash
# Restart with clean database
docker compose -p mb stop mariadb
rm -rf local/mariadb/*
docker compose -p mb up -d mariadb
docker compose -p mb exec php php yii migrate/up --interactive=0
```

### Problem: Composer fails
```bash
# Clear cache and reinstall
docker compose -p mb exec php composer clear-cache
docker compose -p mb exec php composer install
```

### Problem: ext-zip missing error
```bash
# Rebuild with updated Dockerfile
docker compose -p mb down
docker rmi php:latest
./setup_linux_local.sh
```
See [SETUP_SCRIPT_FIX.md](SETUP_SCRIPT_FIX.md) for details.

---

## 📊 Service Information

| Service | Container | Port | URL |
|---------|-----------|------|-----|
| Web App | mb-nginx | 80 | http://localhost |
| Database | mb-mariadb | 3307 | localhost:3307 |
| phpMyAdmin | mb-phpmyadmin | 8080 | http://localhost:8080 |
| Redis | mb-redis | 6379 | localhost:6379 |

### Database Credentials
- Host: `mariadb` (internal) or `localhost:3307` (external)
- Database: `mybs`
- Username: `root`
- Password: `mauFJcuf5dhRMQrjj`
- Prefix: `mb_`

---

## 🎯 Development Workflow

1. **Start containers**
   ```bash
   docker compose -p mb up -d
   ```

2. **Make code changes** in `php/` directory

3. **Changes are live** (volume mounted, no rebuild needed)

4. **Clear cache if needed**
   ```bash
   docker compose -p mb exec php php yii cache/flush-all
   ```

5. **View logs for debugging**
   ```bash
   docker logs -f mb-php
   ```

---

## 📱 Access Points

- **Main Application:** http://localhost
- **Gii Code Generator:** http://localhost/gii (dev mode only)
- **Debug Toolbar:** Available in dev mode (bottom of pages)

---

## 🔐 Default Credentials

**Admin User:**
- Username: `admin`
- Email: `admin@example.com`
- Password: `admin123`

⚠️ **Change password immediately after first login!**

---

## 💡 Pro Tips

- Use `docker compose -p mb` prefix for all commands to target this project
- Logs are in `logs/nginx/` and `logs/php/`
- Database data persists in `local/mariadb/`
- Composer cache is preserved between rebuilds
- PHP errors appear in container logs: `docker logs mb-php`

---

For full documentation, see [README.md](README.md)

