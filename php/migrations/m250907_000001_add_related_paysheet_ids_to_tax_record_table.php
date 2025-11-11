<?php

use yii\db\Migration;

class m250907_000001_add_related_paysheet_ids_to_tax_record_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%tax_record}}', 'related_paysheet_ids', $this->string()->after('related_expense_ids')->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%tax_record}}', 'related_paysheet_ids');
    }
}
