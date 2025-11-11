<?php

use yii\db\Migration;

/**
 * Add supporting_document column to tax_year_bank_balance table
 */
class m251110_000001_add_supporting_document_to_bank_balance extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%tax_year_bank_balance}}', 'supporting_document', $this->string()->after('balance_lkr'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%tax_year_bank_balance}}', 'supporting_document');
    }
}

