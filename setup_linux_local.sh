#!/bin/bash

set -e  # Exit on error

echo "========================================"
echo "Starting Local Environment Setup"
echo "========================================"

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file in root directory..."
    cp -f .env.example .env
    echo "✓ .env file created"
else
    echo "✓ .env file already exists"
fi

# Create db-local.php from environment variables
echo "Creating database configuration..."
cat > php/config/db-local.php << 'EOF'
<?php
// Auto-generated database configuration
return [
    'host' => getenv('DB_HOST') ?: 'mariadb',
    'port' => getenv('DB_PORT') ?: '3306',
    'dbname' => getenv('DB_NAME') ?: 'mybs',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWD') ?: 'mauFJcuf5dhRMQrjj',
    'tablePrefix' => getenv('DB_PREFIX') ?: 'mb_'
];
EOF
echo "✓ Database configuration created"

# Create mail-local.php from environment variables
echo "Creating mail configuration..."
cat > php/config/mail-local.php << 'EOF'
<?php
// Auto-generated mail configuration
$smtpHost = getenv('SMTP_HOST') ?: '';
$smtpPort = getenv('SMTP_PORT') ?: '587';
$smtpUser = getenv('SMTP_USER') ?: '';
$smtpPass = getenv('SMTP_PASS') ?: '';

return [
    'smtp' => [
        'dsn' => $smtpHost && $smtpUser
            ? sprintf('smtp://%s:%s@%s:%s', $smtpUser, $smtpPass, $smtpHost, $smtpPort)
            : 'native://default'  // Use native mail if no SMTP configured
    ],
];
EOF
echo "✓ Mail configuration created"

# Create post_install.sh if it doesn't exist
if [ ! -f "php/post_install.sh" ]; then
    echo "Creating post_install.sh..."
    cat > php/post_install.sh << 'EOFSCRIPT'
#!/bin/bash

set -e  # Exit on error

echo "========================================"
echo "Starting Post-Install Script"
echo "========================================"

# Check if vendor directory exists
if [ -d "vendor" ]; then
    echo "✓ Vendor directory already exists, checking if update needed..."
    # Check if composer.lock is newer than vendor
    if [ -f "composer.lock" ] && [ "composer.lock" -nt "vendor" ]; then
        echo "composer.lock is newer than vendor, running composer install..."
        composer install --optimize-autoloader --no-interaction --prefer-dist
    else
        echo "✓ Vendor is up to date"
    fi
else
    echo "Running composer install..."
    composer install --optimize-autoloader --no-interaction --prefer-dist
    if [ $? -eq 0 ]; then
        echo "✓ Composer install completed successfully"
    else
        echo "❌ Composer install failed"
        exit 1
    fi
fi

echo ""
echo "========================================"
echo "Post-Install Script Completed"
echo "========================================"
EOFSCRIPT
    echo "✓ post_install.sh created"
fi

# Make post_install.sh executable
if [ -f "php/post_install.sh" ]; then
    chmod +x php/post_install.sh
    echo "✓ Made post_install.sh executable"
fi

# Stop and remove existing containers
echo ""
echo "Stopping and removing existing containers..."
docker container rm -f mb-php mb-nginx mb-mariadb mb-redis 2>/dev/null || true
echo "✓ Containers removed"

# Build Docker images
echo ""
echo "Building Docker images..."
echo "Building nginx image..."
docker build . -t nginx:latest -f local/php/nginx/Dockerfile --progress=plain

echo ""
echo "Building php image..."
docker build . -t php:latest -f local/php/php-fpm/Dockerfile --progress=plain

# Load environment variables from .env file
echo ""
echo "Loading environment variables..."
if [ -f ".env" ]; then
    # Export all variables from .env file
    set -a
    source .env
    set +a
    echo "✓ Environment variables loaded"
else
    echo "⚠️  Warning: .env file not found, using defaults"
fi

# Start services
echo ""
echo "Starting Docker containers..."
docker compose -p mb up -d --build
cd ..

# Wait for MariaDB to be ready
echo ""
echo "Waiting for MariaDB to be ready..."
DB_PASSWORD="${DB_PASSWD:-mauFJcuf5dhRMQrjj}"
DB_NAME="${DB_NAME:-mybs}"
MAX_ATTEMPTS=30
ATTEMPT=0

while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    ATTEMPT=$((ATTEMPT + 1))
    echo "  Attempt $ATTEMPT/$MAX_ATTEMPTS..."

    if docker compose -p mb exec mariadb mysqladmin ping -h localhost -proot -p"$DB_PASSWORD" --silent 2>/dev/null; then
        echo "✓ MariaDB is ready!"
        break
    fi

    if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
        echo "❌ MariaDB failed to start within expected time"
        echo "   Check logs: docker logs mb-mariadb"
        exit 1
    fi

    sleep 2
done

# Create database if it doesn't exist
echo ""
echo "Creating database if not exists..."
docker compose -p mb exec mariadb mysql -uroot -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✓ Database '$DB_NAME' ready"
else
    echo "⚠️  Warning: Could not verify database creation (may already exist)"
fi

# Wait for PHP container to finish composer install
echo ""
echo "Waiting for Composer installation to complete..."
COMPOSER_WAIT=0
MAX_COMPOSER_WAIT=60

while [ $COMPOSER_WAIT -lt $MAX_COMPOSER_WAIT ]; do
    COMPOSER_WAIT=$((COMPOSER_WAIT + 1))

    # Check if vendor directory exists (composer install completed)
    if docker compose -p mb exec php test -d /var/www/html/vendor 2>/dev/null; then
        echo "✓ Composer installation completed!"
        break
    fi

    if [ $COMPOSER_WAIT -eq $MAX_COMPOSER_WAIT ]; then
        echo "⚠️  Warning: Composer installation taking longer than expected"
        echo "   Continuing anyway..."
        break
    fi

    sleep 2
done

# Run database migrations
echo ""
echo "Running database migrations..."
if docker compose -p mb exec php php yii migrate/up --interactive=0; then
    echo "✓ Database migrations completed successfully!"
    echo ""
    echo "========================================"
    echo "Default admin user created:"
    echo "  Username: admin"
    echo "  Email: admin@example.com"
    echo "  Password: admin123"
    echo "========================================"
else
    echo "⚠️  Warning: Database migrations failed"
    echo "   You can run them manually with:"
    echo "   docker compose -p mb exec php php yii migrate/up --interactive=0"
fi

echo ""
echo "========================================"
echo "Setup Complete!"
echo "========================================"
echo ""
echo "⚠️  IMPORTANT: Starting the application will take time as packages are installing in the first run..."
echo ""
echo "To check the progress:"
echo "  docker logs -f mb-php"
echo ""
echo "To access the application:"
echo "  http://localhost"
echo ""
echo "To check all containers status:"
echo "  docker ps"
echo ""
echo "To view logs:"
echo "  docker logs mb-php      # PHP logs"
echo "  docker logs mb-nginx    # Nginx logs"
echo "  docker logs mb-mariadb  # Database logs"
echo ""
echo "========================================"



