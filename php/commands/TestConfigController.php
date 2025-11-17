<?php

namespace app\commands;

use app\models\SystemConfig;
use yii\console\Controller;

/**
 * Test SystemConfig save functionality
 */
class TestConfigController extends Controller
{
    /**
     * Test saving a config value
     */
    public function actionSave($key = 'business_name', $value = 'Test Value')
    {
        echo "Testing SystemConfig save...\n";
        echo "Looking for config: {$key}\n";

        $config = SystemConfig::findOne(['config_key' => $key]);

        if (!$config) {
            echo "ERROR: Config not found!\n";
            return 1;
        }

        echo "Found config: {$config->config_key}\n";
        echo "Current value: {$config->config_value}\n";
        echo "New value: {$value}\n";

        $oldValue = $config->config_value;
        $config->config_value = $value;

        echo "\nAttempting to save...\n";

        if ($config->save(false)) {
            echo "✓ Save successful!\n";
            echo "Updated from '{$oldValue}' to '{$config->config_value}'\n";

            // Verify in database
            $verify = SystemConfig::findOne(['config_key' => $key]);
            echo "Verified in DB: {$verify->config_value}\n";

            return 0;
        } else {
            echo "✗ Save failed!\n";
            if ($config->hasErrors()) {
                echo "Errors:\n";
                print_r($config->errors);
            }
            return 1;
        }
    }

    /**
     * Check all configs
     */
    public function actionList()
    {
        echo "All system configurations:\n";
        echo str_repeat("-", 80) . "\n";

        $configs = SystemConfig::find()
            ->select(['id', 'config_key', 'config_value', 'is_editable'])
            ->all();

        foreach ($configs as $config) {
            $editable = $config->is_editable ? 'Yes' : 'No';
            $value = strlen($config->config_value) > 50
                ? substr($config->config_value, 0, 50) . '...'
                : $config->config_value;

            printf("ID: %-3d | %-30s | Editable: %-3s | Value: %s\n",
                $config->id,
                $config->config_key,
                $editable,
                $value
            );
        }

        echo str_repeat("-", 80) . "\n";
        echo "Total: " . count($configs) . " configs\n";
    }
}

