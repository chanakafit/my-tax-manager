<?php

use yii\db\Migration;

/**
 * Class m250829_000001_add_total_fields_to_tax_record
 */
class m250829_000001_add_total_fields_to_tax_record extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tax_record}}', 'total_income', $this->decimal(15, 2)->defaultValue(0));
        $this->addColumn('{{%tax_record}}', 'total_expenses', $this->decimal(15, 2)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%tax_record}}', 'total_income');
        $this->dropColumn('{{%tax_record}}', 'total_expenses');
    }
}
