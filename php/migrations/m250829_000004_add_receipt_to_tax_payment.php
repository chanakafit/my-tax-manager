<?php

use yii\db\Migration;

/**
 * Class m250829_000004_add_receipt_to_tax_payment
 */
class m250829_000004_add_receipt_to_tax_payment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tax_payment}}', 'receipt_file', $this->string()->after('notes'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%tax_payment}}', 'receipt_file');
    }
}
