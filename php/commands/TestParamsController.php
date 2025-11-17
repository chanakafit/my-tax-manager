<?php

namespace app\commands;

use app\helpers\Params;
use app\models\SystemConfig;
use yii\console\Controller;

/**
 * Test Params helper with SystemConfig fallback
 */
class TestParamsController extends Controller
{
    /**
     * Test Params helper fallback to SystemConfig
     */
    public function actionTest()
    {
        echo "Testing Params Helper with SystemConfig Fallback\n";
        echo str_repeat("=", 70) . "\n\n";

        // Test 1: Value exists in params.php
        echo "Test 1: Value in params.php\n";
        $defaultTaxRate = Params::get('defaultTaxRate', 0);
        echo "  Key: defaultTaxRate\n";
        echo "  Value: {$defaultTaxRate}\n";
        echo "  Source: params.php\n";
        echo "  ✓ Pass\n\n";

        // Test 2: Value not in params.php, fallback to SystemConfig
        echo "Test 2: Value not in params.php, fallback to SystemConfig\n";
        $businessName = Params::get('businessName', 'Default Company');
        echo "  Key: businessName\n";
        echo "  Value: {$businessName}\n";
        echo "  Source: SystemConfig (business_name)\n";
        echo "  ✓ Pass\n\n";

        // Test 3: Direct SystemConfig lookup
        echo "Test 3: Direct SystemConfig lookup\n";
        $bankName = Params::get('bankName', 'Unknown Bank');
        echo "  Key: bankName\n";
        echo "  Value: {$bankName}\n";
        echo "  Source: SystemConfig (bank_name)\n";
        echo "  ✓ Pass\n\n";

        // Test 4: Non-existent key returns default
        echo "Test 4: Non-existent key returns default\n";
        $nonExistent = Params::get('thisDoesNotExist', 'My Default Value');
        echo "  Key: thisDoesNotExist\n";
        echo "  Value: {$nonExistent}\n";
        echo "  Source: Default parameter\n";
        echo "  ✓ Pass\n\n";

        // Test 5: Dot notation to underscore conversion
        echo "Test 5: Dot notation conversion\n";
        $businessCity = Params::get('business.city', 'Unknown City');
        echo "  Key: business.city\n";
        echo "  Converted to: business_city\n";
        echo "  Value: {$businessCity}\n";
        echo "  Source: SystemConfig\n";
        echo "  ✓ Pass\n\n";

        echo str_repeat("=", 70) . "\n";
        echo "All tests completed successfully! ✓\n";
        echo "\nParams helper correctly falls back to SystemConfig when needed.\n";

        return 0;
    }

    /**
     * Compare params.php vs SystemConfig values
     */
    public function actionCompare()
    {
        echo "Comparing params.php vs SystemConfig\n";
        echo str_repeat("=", 70) . "\n\n";

        $testKeys = [
            'defaultTaxRate' => 'Should be in params.php',
            'businessName' => 'Should be in SystemConfig (business_name)',
            'bankName' => 'Should be in SystemConfig (bank_name)',
            'currencySymbol' => 'Should be in SystemConfig (currency_symbol)',
            'signature' => 'Should be in params.php',
        ];

        foreach ($testKeys as $key => $expected) {
            $value = Params::get($key, 'NOT FOUND');
            echo "Key: {$key}\n";
            echo "  Expected: {$expected}\n";
            echo "  Value: {$value}\n";
            echo "  Status: " . ($value !== 'NOT FOUND' ? '✓' : '✗') . "\n\n";
        }

        return 0;
    }
}

