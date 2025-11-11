<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tax_config}}`.
 */
class m250817_000001_create_tax_config_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%tax_config}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'key' => $this->string()->notNull()->unique(),
            'value' => $this->decimal(10, 2)->notNull(),
            'description' => $this->text(),
            'is_active' => $this->boolean()->defaultValue(true),
            'valid_from' => $this->date()->notNull(),
            'valid_until' => $this->date(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Insert default tax configurations
        $this->batchInsert('{{%tax_config}}',
            ['name', 'key', 'value', 'description', 'valid_from', 'created_at', 'updated_at', 'created_by', 'updated_by'],
            [
                ['Profit Tax Rate', 'profit_tax_rate', 15.00, 'Standard profit tax rate percentage', date('Y-m-d'), time(), time(), 1, 1],
                ['Annual Tax Relief', 'annual_tax_relief', 500000.00, 'Annual tax relief amount', date('Y-m-d'), time(), time(), 1, 1],
            ]
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%tax_config}}');
    }
}
