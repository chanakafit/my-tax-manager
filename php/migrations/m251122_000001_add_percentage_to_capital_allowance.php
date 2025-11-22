<?php

use yii\db\Migration;

/**
 * Add percentage column to capital_allowance table
 */
class m251122_000001_add_percentage_to_capital_allowance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%capital_allowance}}', 'percentage_claimed', $this->decimal(5, 2)->after('allowance_amount')->comment('Percentage of written down value claimed'));
        
        // Update existing records with default 20%
        $this->execute("UPDATE {{%capital_allowance}} SET percentage_claimed = 20.00 WHERE percentage_claimed IS NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%capital_allowance}}', 'percentage_claimed');
    }
}

