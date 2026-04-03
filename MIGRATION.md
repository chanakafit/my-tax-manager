# Migration Plan: Cloudways → Self-Managed VPS

## Overview

| | Detail |
|---|---|
| **App** | My Tax Manager (Yii2) |
| **From** | Cloudways (native PHP/MySQL stack) |
| **To** | Ubuntu 22.04 VPS (`ezbook-demo`) |
| **Domain** | `fin.chanakalk.com` |
| **SSL** | Let's Encrypt |
| **Downtime** | Accepted |

## Target Server Architecture

```
Internet
    ↓
Apache2 (port 80/443)  — manages all apps on this server
    ↓ ProxyPass → localhost:8081
Docker: mb-nginx (127.0.0.1:8081)
    ↓
Docker: mb-php (PHP-FPM 8.3)
Docker: mb-mariadb (127.0.0.1:3307)
Docker: mb-redis
Docker: mb-phpmyadmin (127.0.0.1:8080, SSH tunnel only)
```

---

## Progress

- [x] Analysis of source and target environment
- [x] `docker-compose.prod.yml` created (no `.env` dependency, ports bound to localhost)
- [x] `php/config/db.php` updated to fall back to env vars if `db-local.php` missing
- [x] `php/config/web.php` updated to fall back to `MAIL_DSN` env var if `mail-local.php` missing
- [x] `.env.prod.example` created as template
- [x] `.gitignore` updated (`.env.prod` excluded)
- [x] Docker (official) installed on target server
- [ ] Start Docker stack on server
- [ ] Create required log directories on server
- [ ] Create `.env.prod` on server
- [ ] Install Composer dependencies
- [ ] Export database from Cloudways
- [ ] Import database into MariaDB container
- [ ] Upload files from Cloudways (`php/web/uploads/`)
- [ ] Run Yii2 migrations
- [ ] Configure Apache vhost
- [ ] Obtain SSL certificate (Let's Encrypt)
- [ ] Enable Apache site and reload
- [ ] Point DNS to server
- [ ] Smoke test
- [ ] Decommission Cloudways app

---

## Step-by-Step

### Phase 1 — Prepare on Cloudways

**1. Export the database**
- Cloudways panel → your app → phpMyAdmin
- Select database → Export → Quick → SQL format
- Save `.sql` file locally

**2. Download uploaded files**
- SFTP into Cloudways
- Download `php/web/uploads/` to your local machine

---

### Phase 2 — Set up on Target Server

**1. Pull latest code**
```bash
cd /var/www/fin
git pull
```

**2. Create required log directories**
```bash
mkdir -p /var/www/fin/logs/nginx
mkdir -p /var/www/fin/logs/php
touch /var/www/fin/logs/php/php.log
```

**3. Create `.env.prod`**
```bash
cp .env.prod.example .env.prod
nano .env.prod
```

Fill in real values:
```env
DB_ROOT_PASSWORD=your_strong_root_password
DB_NAME=mybs
DB_USER=mybs_user
DB_PASSWD=your_strong_password

APP_NAME=Finance Manager
COOKIE_VALIDATION_KEY=generate_a_random_32_char_string

# SMTP DSN — use null://null to disable mail
MAIL_DSN=smtp://user:password@smtp.host:587
```

**4. Start the Docker stack**
```bash
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml ps
```

**5. Install Composer dependencies**
```bash
docker compose -f docker-compose.prod.yml exec php composer install --no-dev --optimize-autoloader
```

**6. Import the database**
```bash
# Copy dump from local machine to server
scp your-dump.sql root@SERVER_IP:/var/www/fin/

# Import into MariaDB container
docker compose -f docker-compose.prod.yml exec -T mariadb \
  mysql -uroot -p"YOUR_DB_ROOT_PASSWORD" mybs < /var/www/fin/your-dump.sql
```

**7. Upload files**
```bash
# From local machine
scp -r ./uploads root@SERVER_IP:/var/www/fin/php/web/uploads/

# Fix permissions on server
docker compose -f docker-compose.prod.yml exec php \
  chown -R www-data:www-data /var/www/html/web/uploads
```

**8. Run Yii2 migrations**
```bash
docker compose -f docker-compose.prod.yml exec php php yii migrate --interactive=0
```

---

### Phase 3 — Configure Apache

**1. Enable required modules** (one-time)
```bash
a2enmod proxy proxy_http ssl rewrite headers
systemctl reload apache2
```

**2. Obtain SSL certificate**
> DNS must be pointed to this server before running certbot.
```bash
certbot certonly --apache -d fin.chanakalk.com
```

**3. Create Apache vhost**
```bash
nano /etc/apache2/sites-available/fin.chanakalk.com.conf
```

```apache
<VirtualHost *:80>
    ServerName fin.chanakalk.com
    RewriteEngine On
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerName fin.chanakalk.com

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/fin.chanakalk.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/fin.chanakalk.com/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf

    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:8081/
    ProxyPassReverse / http://127.0.0.1:8081/

    ErrorLog ${APACHE_LOG_DIR}/fin-error.log
    CustomLog ${APACHE_LOG_DIR}/fin-access.log combined
</VirtualHost>
```

**4. Enable site and reload**
```bash
a2ensite fin.chanakalk.com.conf
apache2ctl configtest
systemctl reload apache2
```

---

### Phase 4 — DNS Cutover

1. Point `fin.chanakalk.com` A record → this server's IP
2. Wait for DNS propagation
3. Test `https://fin.chanakalk.com` — verify login, financial data, file uploads
4. Once confirmed working, decommission Cloudways app

---

## Useful Commands

```bash
# View running containers
docker compose -f docker-compose.prod.yml ps

# View logs
docker compose -f docker-compose.prod.yml logs -f php
docker compose -f docker-compose.prod.yml logs -f nginx

# Restart stack
docker compose -f docker-compose.prod.yml restart

# Stop stack
docker compose -f docker-compose.prod.yml down

# Access phpMyAdmin (via SSH tunnel)
# Run on local machine: ssh -L 8080:localhost:8080 root@SERVER_IP
# Then open: http://localhost:8080
```
