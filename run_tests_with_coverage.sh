#!/bin/bash

# Code Coverage Test Runner Script
# This script runs unit tests with coverage reporting

set -e

echo "=================================="
echo "Running Unit Tests with Coverage"
echo "=================================="
echo ""

# Navigate to project directory
cd /Users/chana/Bee48/my-tax-manager

# Clean up any orphaned containers first
echo "Cleaning up orphaned containers..."
docker rm -f $(docker ps -aq --filter name=mb-) 2>/dev/null || true

# Check if containers are running
echo "Checking Docker containers..."
if ! docker-compose ps | grep -q "mb-php.*Up"; then
    echo "Starting Docker containers..."
    docker-compose up -d
    echo "Waiting for containers to be ready..."
    sleep 15

    # Wait for database to be fully ready
    echo "Waiting for database to be ready..."
    until docker-compose exec -T mariadb mysql -uroot -pmauFJcuf5dhRMQrjj -e "SELECT 1" &>/dev/null; do
        echo "Database not ready yet, waiting..."
        sleep 2
    done
    echo "Database is ready!"
fi

echo "Docker containers are running."
echo ""

# Run tests without coverage first (faster)
echo "Step 1: Running all unit tests..."
echo "-----------------------------------"
docker-compose exec php php vendor/bin/codecept run unit
echo ""

# Check if tests passed
if [ $? -eq 0 ]; then
    echo "✅ All tests passed!"
    echo ""

    # Run with coverage
    echo "Step 2: Generating coverage report..."
    echo "--------------------------------------"
    docker-compose exec php php vendor/bin/codecept run unit --coverage --coverage-html --coverage-text

    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Coverage report generated successfully!"
        echo ""
        echo "Coverage report location:"
        echo "  HTML: php/tests/_output/coverage/index.html"
        echo "  XML:  php/tests/_output/coverage.xml"
        echo ""
        echo "To view the HTML report, run:"
        echo "  open php/tests/_output/coverage/index.html"
        echo ""
    else
        echo "❌ Coverage report generation failed"
        exit 1
    fi
else
    echo "❌ Some tests failed. Fix tests before generating coverage report."
    exit 1
fi

echo "=================================="
echo "Test Suite Complete!"
echo "=================================="

