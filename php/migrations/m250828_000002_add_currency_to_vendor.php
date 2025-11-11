<?php

use yii\db\Migration;

/**
 * Add currency_code to vendor table
 */
class m250828_000002_add_currency_to_vendor extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%vendor}}', 'currency_code', $this->string(3)->defaultValue('LKR')->after('address'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%vendor}}', 'currency_code');
    }
}
