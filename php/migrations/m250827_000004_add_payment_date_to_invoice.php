<?php

use yii\db\Migration;

/**
 * Class m250827_000004_add_payment_date_to_invoice
 */
class m250827_000004_add_payment_date_to_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invoice}}', 'payment_date', $this->date()->null()->after('due_date'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoice}}', 'payment_date');
    }
}
