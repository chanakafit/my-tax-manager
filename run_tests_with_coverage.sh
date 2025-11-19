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

# Function to wait for container to be healthy/running
wait_for_container() {
    local container_name=$1
    local max_wait=60
    local waited=0

    echo "Waiting for container $container_name to be ready..."
    while [ $waited -lt $max_wait ]; do
        local status=$(docker inspect --format='{{.State.Status}}' $container_name 2>/dev/null || echo "not_found")
        local health=$(docker inspect --format='{{.State.Health.Status}}' $container_name 2>/dev/null || echo "none")

        if [ "$status" = "running" ]; then
            if [ "$health" = "healthy" ] || [ "$health" = "none" ]; then
                echo "✓ Container $container_name is ready"
                return 0
            fi
        fi

        echo "Container status: $status (health: $health), waiting..."
        sleep 2
        waited=$((waited + 2))
    done

    echo "✗ Timeout waiting for container $container_name"
    return 1
}

# Stop any existing containers to ensure clean state
echo "Stopping existing containers..."
docker compose -p mb down 2>/dev/null || true
sleep 2

# Start containers
echo "Starting Docker containers..."
docker compose -p mb up -d

# Wait for each critical container
wait_for_container "mb-mariadb"
wait_for_container "mb-php"
wait_for_container "mb-redis"

# Additional wait for MariaDB to be fully ready
echo "Verifying database connectivity..."
max_attempts=30
attempt=0
until docker exec mb-php mysql -h mariadb -uroot -pmauFJcuf5dhRMQrjj -e "SELECT 1" &>/dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -ge $max_attempts ]; then
        echo "✗ Database connection timeout"
        exit 1
    fi
    echo "Attempting database connection ($attempt/$max_attempts)..."
    sleep 2
done
echo "✓ Database is ready!"
echo ""

echo "All containers are running and ready."
echo ""

# Run tests without coverage first (faster)
echo "Step 1: Running all unit tests..."
echo "-----------------------------------"
docker exec mb-php ./vendor/bin/codecept run unit
echo ""

# Check if tests passed
if [ $? -eq 0 ]; then
    echo "✅ All tests passed!"
    echo ""

    # Run with coverage
    echo "Step 2: Generating coverage report..."
    echo "--------------------------------------"
    docker exec mb-php ./vendor/bin/codecept run unit --coverage --coverage-html --coverage-text

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

