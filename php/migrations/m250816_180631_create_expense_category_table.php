<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%expense_category}}`.
 */
class m250816_180631_create_expense_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%expense_category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'description' => $this->text(),
            'budget_limit' => $this->decimal(10, 2)->defaultValue(0),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Insert some default categories
        $this->batchInsert('{{%expense_category}}',
            ['name', 'description', 'created_at', 'updated_at', 'created_by', 'updated_by'],
            [
                ['Supplies and Materials', 'General office supplies and stationery', time(), time(), 1, 1],
                ['Utilities', 'Electricity, water, internet, etc.', time(), time(), 1, 1],
                ['Rent', 'Office and workspace rent', time(), time(), 1, 1],
                ['Payroll', 'Employee salaries and wages', time(), time(), 1, 1],
                ['Marketing', 'Advertising and promotion expenses', time(), time(), 1, 1],
                ['Travel', 'Business travel and accommodation', time(), time(), 1, 1],
                ['Professional Services', 'Consultants, legal, and accounting fees', time(), time(), 1, 1],
                ['Maintenance and Repairs', 'Upkeep of office equipment and facilities', time(), time(), 1, 1],
                ['Insurance', 'Business insurance premiums', time(), time(), 1, 1],
                ['Miscellaneous', 'Other uncategorized expenses', time(), time(), 1, 1],
                ['Computer, Software and Internet', 'Expenses related to computers, software licenses, and internet services', time(), time(), 1, 1]
            ]
        );

        // Add foreign key to expense_category
        $this->addForeignKey(
            'fk-expense-category_id',
            '{{%expense}}',
            'expense_category_id',
            '{{%expense_category}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk-expense-category_id', '{{%expense}}');
        $this->dropTable('{{%expense_category}}');
    }
}
