<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tax_record}}`.
 */
class m250816_180702_create_tax_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tax_record}}', [
            'id' => $this->primaryKey(),
            'tax_period_start' => $this->date()->notNull(),
            'tax_period_end' => $this->date()->notNull(),
            'tax_type' => $this->string()->notNull(), // VAT, Income Tax, etc.
            'tax_rate' => $this->decimal(5, 2)->notNull(),
            'taxable_amount' => $this->decimal(15, 2)->notNull(),
            'tax_amount' => $this->decimal(15, 2)->notNull(),
            'payment_status' => $this->string()->notNull()->defaultValue('pending'),
            'payment_date' => $this->date(),
            'reference_number' => $this->string(),
            'notes' => $this->text(),
            'related_invoice_ids' => $this->text(), // JSON array of invoice IDs
            'related_expense_ids' => $this->text(), // JSON array of expense IDs
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex('idx-tax_record-period', '{{%tax_record}}', ['tax_period_start', 'tax_period_end']);
        $this->createIndex('idx-tax_record-type', '{{%tax_record}}', 'tax_type');
        $this->createIndex('idx-tax_record-status', '{{%tax_record}}', 'payment_status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tax_record}}');
    }
}
