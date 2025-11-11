<?php

use yii\db\Migration;

class m250817_000003_add_receipt_fields_to_expense_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%expense}}', 'receipt_file', $this->string());
        $this->addColumn('{{%expense}}', 'receipt_date', $this->date());
        $this->addColumn('{{%expense}}', 'payment_date', $this->date());
        $this->addColumn('{{%expense}}', 'payment_reference', $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%expense}}', 'receipt_file');
        $this->dropColumn('{{%expense}}', 'receipt_date');
        $this->dropColumn('{{%expense}}', 'payment_date');
        $this->dropColumn('{{%expense}}', 'payment_reference');
    }
}
