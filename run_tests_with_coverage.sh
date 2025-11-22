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

# Function to wait for container to be running
wait_for_container() {
    local container_name=$1
    local max_wait=60
    local waited=0

    echo "Waiting for container $container_name to be ready..."
    while [ $waited -lt $max_wait ]; do
        local status=$(docker inspect --format='{{.State.Status}}' $container_name 2>/dev/null || echo "not_found")

        if [ "$status" = "running" ]; then
            # Check if container has health check
            local has_health=$(docker inspect --format='{{if .State.Health}}yes{{else}}no{{end}}' $container_name 2>/dev/null)

            if [ "$has_health" = "no" ]; then
                # No health check - container is ready when running
                echo "✓ Container $container_name is running"
                return 0
            else
                # Has health check - wait for healthy
                local health=$(docker inspect --format='{{.State.Health.Status}}' $container_name 2>/dev/null)
                if [ "$health" = "healthy" ]; then
                    echo "✓ Container $container_name is healthy"
                    return 0
                fi

                # Show progress every 10 seconds
                if [ $((waited % 10)) -eq 0 ] && [ $waited -gt 0 ]; then
                    echo "Container $container_name health: $health (waited ${waited}s)..."
                fi
            fi
        else
            echo "Container $container_name status: $status, waiting..."
        fi

        sleep 2
        waited=$((waited + 2))
    done

    echo "✗ Timeout waiting for container $container_name after ${max_wait}s"
    echo "Container logs:"
    docker logs $container_name --tail 30
    return 1
}

# Stop any existing containers to ensure clean state
echo "Stopping existing containers..."
docker compose -p mb down 2>/dev/null || true

# Remove any orphaned containers that might be holding ports
echo "Cleaning up orphaned containers..."
docker container prune -f 2>/dev/null || true

# Kill any processes that might be using port 80
echo "Checking for port conflicts..."
if lsof -i :80 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "Warning: Port 80 is in use. Attempting to free it..."
    # Try to stop any docker containers using port 80
    for container in $(docker ps -q); do
        if docker port "$container" 2>/dev/null | grep -q ":80"; then
            echo "Stopping container $container using port 80..."
            docker stop "$container" 2>/dev/null || true
        fi
    done
fi

sleep 2

# Start containers
echo "Starting Docker containers..."
docker compose -p mb up -d

# Give containers a moment to start
sleep 5

# Wait for critical containers to be running (skip health checks for faster startup)
echo "Checking if containers are running..."
for container in mb-mariadb mb-redis mb-nginx; do
    wait_for_container "$container"
done

# For PHP container, just verify it's running and can execute commands
echo "Waiting for mb-php container..."
max_wait=30
waited=0
while [ $waited -lt $max_wait ]; do
    if docker exec mb-php php --version &>/dev/null; then
        echo "✓ PHP container is ready and can execute commands"
        break
    fi
    if [ $waited -gt 0 ] && [ $((waited % 10)) -eq 0 ]; then
        echo "Waiting for PHP to be ready (${waited}s)..."
    fi
    sleep 2
    waited=$((waited + 2))
done

if [ $waited -ge $max_wait ]; then
    echo "✗ PHP container not responding"
    docker logs mb-php --tail 30
    exit 1
fi

# Give services additional time to initialize after containers are running
echo "Waiting for services to initialize..."
sleep 5

# Now verify database connectivity
# Now verify database connectivity using PHP
echo "Verifying database connectivity..."
max_attempts=15
attempt=0

# Create a test PHP script to check database connection
until docker exec mb-php php -r "
try {
    \$pdo = new PDO('mysql:host=mariadb;dbname=mybs', 'root', 'mauFJcuf5dhRMQrjj');
    echo 'Connected';
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" &>/dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -ge $max_attempts ]; then
        echo "✗ Database connection timeout after $max_attempts attempts"
        echo "Checking if database is accessible..."
        docker exec mb-php php -r "
        try {
            \$pdo = new PDO('mysql:host=mariadb;dbname=mybs', 'root', 'mauFJcuf5dhRMQrjj');
            echo 'Database connected successfully\n';
        } catch (Exception \$e) {
            echo 'Database error: ' . \$e->getMessage() . '\n';
        }
        "
        echo "MariaDB container logs:"
        docker logs mb-mariadb --tail 20
        exit 1
    fi
    if [ $((attempt % 5)) -eq 0 ]; then
        echo "Attempting database connection ($attempt/$max_attempts)..."
    fi
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

