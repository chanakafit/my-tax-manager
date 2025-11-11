<?php

use yii\db\Migration;

/**
 * Class m250829_000002_add_tax_code_to_tax_record
 */
class m250829_000002_add_tax_code_to_tax_record extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tax_record}}', 'tax_code', $this->string(10)->after('tax_type'));
        $this->createIndex('idx-tax_record-tax_code', '{{%tax_record}}', 'tax_code', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-tax_record-tax_code', '{{%tax_record}}');
        $this->dropColumn('{{%tax_record}}', 'tax_code');
    }
}
