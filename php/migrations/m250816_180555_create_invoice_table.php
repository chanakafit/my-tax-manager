<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice}}`.
 */
class m250816_180555_create_invoice_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invoice}}', [
            'id' => $this->primaryKey(),
            'invoice_number' => $this->string()->notNull()->unique(),
            'customer_name' => $this->string()->notNull(),
            'customer_address' => $this->text(),
            'customer_phone' => $this->string(),
            'customer_email' => $this->string(),
            'invoice_date' => $this->date()->notNull(),
            'due_date' => $this->date()->notNull(),
            'payment_term_id' => $this->integer(),
            'subtotal' => $this->decimal(10, 2)->notNull(),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(0),
            'tax_amount' => $this->decimal(10, 2)->defaultValue(0),
            'discount' => $this->decimal(10, 2)->defaultValue(0),
            'total_amount' => $this->decimal(10, 2)->notNull(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'notes' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex('idx-invoice-status', '{{%invoice}}', 'status');
        $this->createIndex('idx-invoice-date', '{{%invoice}}', 'invoice_date');
        $this->createIndex('idx-invoice-payment_term', '{{%invoice}}', 'payment_term_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%invoice}}');
    }
}
