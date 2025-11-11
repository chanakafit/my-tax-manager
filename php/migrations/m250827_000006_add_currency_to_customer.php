<?php

use yii\db\Migration;

/**
 * Class m250827_000006_add_currency_to_customer
 */
class m250827_000006_add_currency_to_customer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer}}', 'default_currency', $this->string(3)->notNull()->defaultValue('LKR'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer}}', 'default_currency');
    }
}
