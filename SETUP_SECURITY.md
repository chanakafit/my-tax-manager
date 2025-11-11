# Quick Setup - Environment Configuration

## 🚀 Getting Started

### 1. Copy Environment Files
```bash
# Root directory
cp .env.example .env

# PHP directory
cp php/.env.example php/.env
```

### 2. Generate Secure Keys
```bash
# Generate cookie validation key (save this)
openssl rand -base64 32

# Generate admin password (save this)
openssl rand -base64 24
```

### 3. Edit .env Files

Edit both `.env` and `php/.env` with your values:

```dotenv
# Database
DB_PASSWD="your_secure_password_here"
DB_ROOT_PASSWD="your_root_password_here"

# Admin
ADMIN_DEFAULT_PASSWORD="paste_generated_password_here"
EMAIL="your-email@example.com"

# Security
COOKIE_VALIDATION_KEY="paste_generated_key_here"
```

### 4. Set File Permissions
```bash
chmod 600 .env
chmod 600 php/.env
```

### 5. Run Setup
```bash
# Start containers
docker-compose up -d

# Run migrations
docker-compose exec php php yii migrate
```

### 6. Login
- **URL:** http://localhost
- **Username:** admin
- **Password:** (your ADMIN_DEFAULT_PASSWORD from .env)

---

## ⚠️ Security Notes

- Never commit `.env` files
- Never commit `config/db-local.php`
- Keep passwords secure
- Use different passwords per environment

See `SECURITY_SENSITIVE_DATA.md` for complete documentation.

