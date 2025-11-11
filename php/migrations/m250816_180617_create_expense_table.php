<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%expense}}`.
 */
class m250816_180617_create_expense_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%expense}}', [
            'id' => $this->primaryKey(),
            'expense_category_id' => $this->integer()->notNull(),
            'expense_date' => $this->date()->notNull(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'tax_amount' => $this->decimal(10, 2)->defaultValue(0),
            'receipt_number' => $this->string(),
            'receipt_path' => $this->string(),
            'payment_method' => $this->string()->notNull(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'vendor_id' => $this->integer(),
            'is_recurring' => $this->boolean()->defaultValue(false),
            'recurring_interval' => $this->string(),
            'next_recurring_date' => $this->date(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex('idx-expense-category_id', '{{%expense}}', 'expense_category_id');
        $this->createIndex('idx-expense-date', '{{%expense}}', 'expense_date');
        $this->createIndex('idx-expense-status', '{{%expense}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%expense}}');
    }
}
