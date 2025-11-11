#!/bin/bash

# Post-install script that runs inside the PHP container
echo "Running post-install setup..."

# Install Composer dependencies
echo "Installing Composer dependencies..."
composer install --optimize-autoloader --no-interaction

echo ""
echo "✓ Composer dependencies installed!"
echo ""
echo "Note: Database migrations will be run by the setup script"
echo "      after confirming database is ready."
echo ""
echo "Post-install setup completed!"

