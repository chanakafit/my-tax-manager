<?php

use yii\db\Migration;

/**
 * Class m250827_000005_add_currency_to_invoice
 */
class m250827_000005_add_currency_to_invoice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invoice}}', 'currency_code', $this->string(3)->notNull()->defaultValue('LKR')->after('payment_date'));
        $this->addColumn('{{%invoice}}', 'exchange_rate', $this->decimal(10, 2)->after('currency_code'));
        $this->addColumn('{{%invoice}}', 'total_amount_lkr', $this->decimal(10, 2)->after('total_amount'));
        $this->addColumn('{{%financial_transaction}}', 'exchange_rate', $this->decimal(10, 2)->after('amount'));
        $this->addColumn('{{%financial_transaction}}', 'amount_lkr', $this->decimal(10, 2)->after('exchange_rate'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoice}}', 'currency_code');
        $this->dropColumn('{{%invoice}}', 'exchange_rate');
        $this->dropColumn('{{%invoice}}', 'total_amount_lkr');
        $this->dropColumn('{{%financial_transaction}}', 'exchange_rate');
        $this->dropColumn('{{%financial_transaction}}', 'amount_lkr');
    }
}
