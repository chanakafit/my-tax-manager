<?php

use yii\db\Migration;

class m250830_000002_create_capital_asset extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%capital_asset}}', [
            'id' => $this->primaryKey(),
            'asset_name' => $this->string()->notNull(),
            'description' => $this->text(),
            'purchase_date' => $this->date()->notNull(),
            'purchase_cost' => $this->decimal(10, 2)->notNull(),
            'initial_tax_year' => $this->string(4)->notNull(), // First tax year of allowance
            'current_written_down_value' => $this->decimal(10, 2)->notNull(), // Remaining value after allowances
            'status' => $this->string()->defaultValue('active'), // active/disposed
            'disposal_date' => $this->date(),
            'disposal_value' => $this->decimal(10, 2),
            'notes' => $this->text(),
            'receipt_file' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Create table for capital allowance records
        $this->createTable('{{%capital_allowance}}', [
            'id' => $this->primaryKey(),
            'capital_asset_id' => $this->integer()->notNull(),
            'tax_year' => $this->string(4)->notNull(),
            'tax_code' => $this->string()->notNull(), // Links to tax record
            'allowance_amount' => $this->decimal(10, 2)->notNull(),
            'written_down_value' => $this->decimal(10, 2)->notNull(), // Value after this allowance
            'year_number' => $this->integer()->notNull(), // 1-5 indicating which year of allowance
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Add foreign key
        $this->addForeignKey(
            'fk-capital_allowance-capital_asset_id',
            '{{%capital_allowance}}',
            'capital_asset_id',
            '{{%capital_asset}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Create index for faster queries
        $this->createIndex(
            'idx-capital_allowance-tax_code',
            '{{%capital_allowance}}',
            'tax_code'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-capital_allowance-capital_asset_id', '{{%capital_allowance}}');
        $this->dropTable('{{%capital_allowance}}');
        $this->dropTable('{{%capital_asset}}');
    }
}
