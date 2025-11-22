<?php

use yii\db\Migration;

class m251122_062839_add_percentage_to_capital_allowance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251122_062839_add_percentage_to_capital_allowance cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251122_062839_add_percentage_to_capital_allowance cannot be reverted.\n";

        return false;
    }
    */
}
