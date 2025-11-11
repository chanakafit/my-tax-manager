<?php

use yii\db\Migration;

class m250830_000001_add_ird_ref_to_tax_record extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%tax_record}}', 'ird_ref', $this->string()->null()->after('tax_code'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%tax_record}}', 'ird_ref');
    }
}
