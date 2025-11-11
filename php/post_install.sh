#!/bin/bash

set -e  # Exit on error

echo "========================================"
echo "Starting Post-Install Script"
echo "========================================"

# Check if vendor directory exists
if [ -d "vendor" ]; then
    echo "✓ Vendor directory already exists, skipping composer install"
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

# Check if composer.lock exists and vendor is older than composer.lock
if [ -f "composer.lock" ] && [ -d "vendor" ]; then
    if [ "composer.lock" -nt "vendor" ]; then
        echo "composer.lock is newer than vendor, running composer install..."
        composer install --optimize-autoloader --no-interaction --prefer-dist
    fi
fi

php yii migrate/up --interactive=0

echo ""
echo "========================================"
echo "Post-Install Script Completed"
echo "========================================"
echo "Default admin user: admin"
echo "Password: admin123"
echo "========================================"

