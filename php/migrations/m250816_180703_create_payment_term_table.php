<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payment_term}}`.
 */
class m250816_180703_create_payment_term_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%payment_term}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'days' => $this->integer()->notNull(),
            'description' => $this->text(),
            'is_default' => $this->boolean()->defaultValue(false),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Insert default payment terms
        $this->batchInsert('{{%payment_term}}',
            ['name', 'days', 'description', 'is_default', 'created_at', 'updated_at', 'created_by', 'updated_by'],
            [
                ['Due on Receipt', 0, 'Payment is due immediately', true, time(), time(), 1, 1],
                ['Net 10', 10, 'Payment is due within 10 days', false, time(), time(), 1, 1],
                ['Net 15', 15, 'Payment is due within 15 days', false, time(), time(), 1, 1],
                ['Net 30', 30, 'Payment is due within 30 days', false, time(), time(), 1, 1],
                ['Net 45', 45, 'Payment is due within 45 days', false, time(), time(), 1, 1],
                ['Net 60', 60, 'Payment is due within 60 days', false, time(), time(), 1, 1],
            ]
        );

        // Add foreign key for payment terms
        $this->addForeignKey(
            'fk-invoice-payment_term_id',
            '{{%invoice}}',
            'payment_term_id',
            '{{%payment_term}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk-invoice-payment_term_id', '{{%invoice}}');
        $this->dropTable('{{%payment_term}}');
    }
}
