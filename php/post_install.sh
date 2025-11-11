#!/bin/bash

set -e  # Exit on error

echo "========================================"
echo "Starting Post-Install Script"
echo "========================================"

# Check if vendor directory exists and has content (autoload.php is the key file)
if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
    echo "✓ Vendor directory already exists with dependencies"

    # Check if composer.lock is newer than vendor, indicating updates are needed
    if [ -f "composer.lock" ] && [ "composer.lock" -nt "vendor/autoload.php" ]; then
        echo "composer.lock is newer than vendor, updating dependencies..."
        composer install --optimize-autoloader --no-interaction --prefer-dist
        if [ $? -eq 0 ]; then
            echo "✓ Composer update completed successfully"
        else
            echo "❌ Composer update failed"
            exit 1
        fi
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
echo "Running database migrations..."
php yii migrate/up --interactive=0

echo ""
echo "========================================"
echo "Post-Install Script Completed"
echo "========================================"
echo "Default admin user: admin"
echo "Password: admin123"
echo "========================================"

