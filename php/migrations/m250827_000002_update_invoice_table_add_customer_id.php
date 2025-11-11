<?php

use yii\db\Migration;

/**
 * Class m250827_000002_update_invoice_table_add_customer_id
 */
class m250827_000002_update_invoice_table_add_customer_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add customer_id column
        $this->addColumn('{{%invoice}}', 'customer_id', $this->integer()->after('id'));

        // Create foreign key
        $this->addForeignKey(
            'fk-invoice-customer_id',
            '{{%invoice}}',
            'customer_id',
            '{{%customer}}',
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
        $this->dropForeignKey('fk-invoice-customer_id', '{{%invoice}}');
        $this->dropColumn('{{%invoice}}', 'customer_id');
    }
}
