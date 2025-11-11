<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice_item}}`.
 */
class m250816_180606_create_invoice_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invoice_item}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'item_name' => $this->string()->notNull(),
            'description' => $this->text(),
            'quantity' => $this->decimal(10, 2)->notNull(),
            'unit_price' => $this->decimal(10, 2)->notNull(),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(0),
            'tax_amount' => $this->decimal(10, 2)->defaultValue(0),
            'discount' => $this->decimal(10, 2)->defaultValue(0),
            'total_amount' => $this->decimal(10, 2)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Add foreign key
        $this->addForeignKey(
            'fk-invoice_item-invoice_id',
            '{{%invoice_item}}',
            'invoice_id',
            '{{%invoice}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Create indexes
        $this->createIndex('idx-invoice_item-invoice_id', '{{%invoice_item}}', 'invoice_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%invoice_item}}');
    }
}
