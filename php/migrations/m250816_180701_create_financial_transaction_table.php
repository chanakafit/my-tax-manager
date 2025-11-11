<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%financial_transaction}}`.
 */
class m250816_180701_create_financial_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%financial_transaction}}', [
            'id' => $this->primaryKey(),
            'bank_account_id' => $this->integer(),
            'transaction_date' => $this->date()->notNull(),
            'transaction_type' => $this->string()->notNull(), // deposit, withdrawal, transfer, payment
            'amount' => $this->decimal(15, 2)->notNull(),
            'reference_type' => $this->string(), // invoice, expense, paysheet
            'reference_number' => $this->string(),
            'related_invoice_id' => $this->integer(),
            'related_expense_id' => $this->integer(),
            'related_paysheet_id' => $this->integer(),
            'description' => $this->text(),
            'category' => $this->string(), // income, expense, transfer, payroll
            'payment_method' => $this->string(), // cash, check, wire, credit_card
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-financial_transaction-bank_account_id',
            '{{%financial_transaction}}',
            'bank_account_id',
            '{{%bank_account}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-financial_transaction-invoice_id',
            '{{%financial_transaction}}',
            'related_invoice_id',
            '{{%invoice}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-financial_transaction-expense_id',
            '{{%financial_transaction}}',
            'related_expense_id',
            '{{%expense}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-financial_transaction-paysheet_id',
            '{{%financial_transaction}}',
            'related_paysheet_id',
            '{{%paysheet}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Create indexes
        $this->createIndex('idx-financial_transaction-date', '{{%financial_transaction}}', 'transaction_date');
        $this->createIndex('idx-financial_transaction-type', '{{%financial_transaction}}', 'transaction_type');
        $this->createIndex('idx-financial_transaction-status', '{{%financial_transaction}}', 'status');
        $this->createIndex('idx-financial_transaction-category', '{{%financial_transaction}}', 'category');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%financial_transaction}}');
    }
}
