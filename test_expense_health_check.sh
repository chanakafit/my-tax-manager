#!/bin/bash

# Quick test runner for ExpenseHealthCheckService
# This verifies the coverage improvements

echo "================================================"
echo "ExpenseHealthCheckService Coverage Test"
echo "================================================"
echo ""

cd /Users/chana/Bee48/my-tax-manager

# Ensure containers are running
echo "🔄 Starting Docker containers..."
docker compose -p mb up -d > /dev/null 2>&1
sleep 5

echo "✅ Containers ready"
echo ""

# Run ExpenseHealthCheckService tests
echo "🧪 Running ExpenseHealthCheckService tests..."
echo "-----------------------------------------------"
docker compose -p mb exec -T php php vendor/bin/codecept run unit components/ExpenseHealthCheckServiceTest

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ ExpenseHealthCheckService tests PASSED!"
    echo ""

    # Show test count
    TEST_COUNT=$(docker compose -p mb exec -T php php vendor/bin/codecept run unit components/ExpenseHealthCheckServiceTest --no-colors 2>&1 | grep -o "[0-9]\+ tests" | grep -o "[0-9]\+")
    echo "📊 Total tests for ExpenseHealthCheckService: $TEST_COUNT"
    echo ""

    # Run full suite
    echo "🧪 Running full test suite..."
    echo "-----------------------------------------------"
    docker compose -p mb exec -T php php vendor/bin/codecept run unit

    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ ALL TESTS PASSED!"
        echo ""
        echo "📊 Generating coverage report..."
        docker compose -p mb exec -T php php vendor/bin/codecept run unit --coverage --coverage-html > /dev/null 2>&1

        if [ $? -eq 0 ]; then
            echo "✅ Coverage report generated!"
            echo ""
            echo "📂 View coverage report:"
            echo "   open php/tests/_output/coverage/index.html"
            echo ""
            echo "   Or run: open php/tests/_output/coverage/index.html"
        fi
    else
        echo ""
        echo "⚠️  Some tests failed in full suite"
    fi
else
    echo ""
    echo "❌ ExpenseHealthCheckService tests failed"
    echo "   Run with --verbose for more details:"
    echo "   docker compose -p mb exec php php vendor/bin/codecept run unit components/ExpenseHealthCheckServiceTest --verbose"
    exit 1
fi

echo ""
echo "================================================"
echo "✅ Testing Complete!"
echo "================================================"

