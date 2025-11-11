<?php

use yii\db\Migration;

/**
 * Add currency support to expense table
 */
class m250828_000001_add_currency_to_expense extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%expense}}', 'currency_code', $this->string(3)->defaultValue('LKR')->after('amount'));
        $this->addColumn('{{%expense}}', 'exchange_rate', $this->decimal(10, 4)->defaultValue(1)->after('currency_code'));
        $this->addColumn('{{%expense}}', 'amount_lkr', $this->decimal(10, 2)->after('exchange_rate'));

        // Update existing records to set amount_lkr equal to amount since they were all in LKR
        $this->update('{{%expense}}', ['amount_lkr' => new \yii\db\Expression('amount')]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%expense}}', 'currency_code');
        $this->dropColumn('{{%expense}}', 'exchange_rate');
        $this->dropColumn('{{%expense}}', 'amount_lkr');
    }
}
