<?php

/**
 * Manual Test Script for Expense Health Check System
 *
 * Run this script from command line to test the system:
 * php php/tests/manual/test-expense-health-check.php
 */

// Bootstrap Yii application
require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../../config/console.php');
$application = new yii\console\Application($config);

use app\components\ExpenseHealthCheckService;
use app\models\Expense;
use app\models\ExpenseSuggestion;
use app\models\Vendor;
use app\models\ExpenseCategory;

echo "=== Expense Health Check System Test ===\n\n";

// Test 1: Service Initialization
echo "Test 1: Service Initialization\n";
try {
    $service = new ExpenseHealthCheckService();
    echo "✓ Service initialized successfully\n";
} catch (Exception $e) {
    echo "✗ Failed to initialize service: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check Database Tables
echo "\nTest 2: Database Tables\n";
try {
    $tableExists = Yii::$app->db->schema->getTableSchema('{{%expense_suggestion}}');
    if ($tableExists) {
        echo "✓ expense_suggestion table exists\n";
        echo "  Columns: " . implode(", ", array_keys($tableExists->columns)) . "\n";
    } else {
        echo "✗ expense_suggestion table does NOT exist\n";
        echo "  Please run migration: php yii migrate\n";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

// Test 3: Check for Existing Expenses
echo "\nTest 3: Historical Expense Data\n";
try {
    $expenseCount = Expense::find()
        ->where(['IS NOT', 'vendor_id', null])
        ->count();
    echo "✓ Found {$expenseCount} expenses with vendors\n";

    // Group by category and vendor to find potential patterns
    $patterns = Yii::$app->db->createCommand("
        SELECT 
            expense_category_id,
            vendor_id,
            COUNT(DISTINCT DATE_FORMAT(expense_date, '%Y-%m')) as month_count,
            MIN(expense_date) as first_expense,
            MAX(expense_date) as last_expense
        FROM {{%expense}}
        WHERE vendor_id IS NOT NULL
        GROUP BY expense_category_id, vendor_id
        HAVING month_count >= 2
        ORDER BY month_count DESC
        LIMIT 5
    ")->queryAll();

    if (count($patterns) > 0) {
        echo "✓ Found " . count($patterns) . " potential recurring patterns:\n";
        foreach ($patterns as $i => $pattern) {
            $category = ExpenseCategory::findOne($pattern['expense_category_id']);
            $vendor = Vendor::findOne($pattern['vendor_id']);
            echo "  " . ($i + 1) . ". {$category->name} - {$vendor->name}: {$pattern['month_count']} months\n";
            echo "     Period: {$pattern['first_expense']} to {$pattern['last_expense']}\n";
        }
    } else {
        echo "⚠ No recurring patterns found (need at least 2 months of expenses for same category/vendor)\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking expenses: " . $e->getMessage() . "\n";
}

// Test 4: Generate Suggestions
echo "\nTest 4: Generate Suggestions\n";
try {
    echo "Generating suggestions for current month...\n";
    $result = $service->generateSuggestionsForMonth();
    echo "✓ Generation completed:\n";
    echo "  Created: {$result['created']}\n";
    echo "  Skipped: {$result['skipped']}\n";
    if (!empty($result['errors'])) {
        echo "  Errors: " . implode(", ", $result['errors']) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error generating suggestions: " . $e->getMessage() . "\n";
}

// Test 5: Check Pending Suggestions
echo "\nTest 5: Pending Suggestions\n";
try {
    $pendingCount = $service->getPendingSuggestionsCount();
    echo "✓ Pending suggestions: {$pendingCount}\n";

    if ($pendingCount > 0) {
        $suggestions = ExpenseSuggestion::find()
            ->with(['expenseCategory', 'vendor'])
            ->where(['status' => ExpenseSuggestion::STATUS_PENDING])
            ->limit(5)
            ->all();

        echo "  Top suggestions:\n";
        foreach ($suggestions as $i => $suggestion) {
            echo "  " . ($i + 1) . ". {$suggestion->expenseCategory->name} - {$suggestion->vendor->name}\n";
            echo "     Month: " . date('F Y', strtotime($suggestion->suggested_month)) . "\n";
            echo "     Avg Amount: LKR " . number_format($suggestion->avg_amount_lkr, 2) . "\n";
            $months = $suggestion->getPatternMonthsArray();
            echo "     Pattern: " . count($months) . " months (" . implode(", ", array_map(function($m) {
                return date('M Y', strtotime($m));
            }, $months)) . ")\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking suggestions: " . $e->getMessage() . "\n";
}

// Test 6: Test Model Methods
echo "\nTest 6: Model Methods\n";
try {
    // Create a test suggestion (will be rolled back)
    $transaction = Yii::$app->db->beginTransaction();

    $testSuggestion = new ExpenseSuggestion([
        'expense_category_id' => 1,
        'vendor_id' => 1,
        'suggested_month' => date('Y-m-01'),
        'pattern_months' => json_encode(['2025-09-01', '2025-10-01']),
        'avg_amount_lkr' => 10000.00,
        'status' => ExpenseSuggestion::STATUS_PENDING,
        'generated_at' => time(),
    ]);

    if ($testSuggestion->save()) {
        echo "✓ Test suggestion created (ID: {$testSuggestion->id})\n";

        // Test status label
        echo "  Status label: {$testSuggestion->getStatusLabel()}\n";

        // Test pattern months
        $months = $testSuggestion->getPatternMonthsArray();
        echo "  Pattern months: " . count($months) . " months\n";

        // Test ignore method
        $testSuggestion->markAsIgnored('temporary', 'Test reason', 1);
        echo "  ✓ Mark as ignored works\n";

        // Test add method
        $testSuggestion->markAsAdded(1);
        echo "  ✓ Mark as added works\n";
    } else {
        echo "⚠ Could not create test suggestion (may need valid category/vendor IDs)\n";
    }

    $transaction->rollBack();
    echo "✓ Test data rolled back\n";
} catch (Exception $e) {
    echo "✗ Error testing model: " . $e->getMessage() . "\n";
}

// Test 7: Test Reset Ignored Suggestions
echo "\nTest 7: Reset Ignored Suggestions\n";
try {
    $resetCount = $service->resetIgnoredSuggestions(1, 1);
    echo "✓ Reset function works (reset {$resetCount} suggestions)\n";
} catch (Exception $e) {
    echo "✗ Error testing reset: " . $e->getMessage() . "\n";
}

// Test 8: Test Cleanup
echo "\nTest 8: Cleanup Old Temporary Ignores\n";
try {
    $cleanedCount = $service->cleanupTemporaryIgnores();
    echo "✓ Cleanup function works (cleaned {$cleanedCount} old ignores)\n";
} catch (Exception $e) {
    echo "✗ Error testing cleanup: " . $e->getMessage() . "\n";
}

// Summary
echo "\n=== Test Summary ===\n";
echo "All tests completed! Review the results above.\n";
echo "\nNext Steps:\n";
echo "1. If migration needed: php yii migrate\n";
echo "2. Add some expenses with recurring patterns\n";
echo "3. Run: php yii expense-health-check/generate\n";
echo "4. View dashboard to see suggestions widget\n";
echo "5. Visit /expense-suggestion/index to manage suggestions\n";
echo "6. Set up cron job (see php/crontab-expense-health-check)\n";

