<?php

use yii\db\Migration;

/**
 * Updates tax configuration to reflect zero tax rate before April 1, 2025
 */
class m251109_000005_update_tax_config_for_2025_scheme extends Migration
{
    public function safeUp()
    {
        // Update existing profit_tax_rate to be valid only from 2025-04-01 onwards
        $this->update('{{%tax_config}}',
            ['valid_from' => '2025-04-01'],
            ['key' => 'profit_tax_rate']
        );

        // Insert zero tax rate configuration for periods before 2025-04-01
        $this->insert('{{%tax_config}}', [
            'name' => 'Profit Tax Rate (Pre-2025)',
            'key' => 'profit_tax_rate_pre_2025',
            'value' => 0.00,
            'description' => 'Profit tax rate for periods before April 1, 2025 (0%)',
            'is_active' => true,
            'valid_from' => '2020-01-01', // Historical start date
            'valid_until' => '2025-03-31', // Last day before new scheme
            'created_at' => time(),
            'updated_at' => time(),
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }

    public function safeDown()
    {
        // Restore original valid_from date (current date when migration was created)
        $this->update('{{%tax_config}}',
            ['valid_from' => date('Y-m-d')],
            ['key' => 'profit_tax_rate']
        );

        // Remove pre-2025 configuration
        $this->delete('{{%tax_config}}', ['key' => 'profit_tax_rate_pre_2025']);
    }
}

