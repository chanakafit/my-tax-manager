<?php

use app\base\BaseMigration;

/**
 * Adds registry_code column to customer table
 */
class m251201_000001_add_registry_code_to_customer extends BaseMigration
{
    public function safeUp()
    {
        $this->addColumn('{{%customer}}', 'registry_code', $this->string(50)->null()->after('tax_number'));
        $this->createIndex('idx-customer-registry_code', '{{%customer}}', 'registry_code');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-customer-registry_code', '{{%customer}}');
        $this->dropColumn('{{%customer}}', 'registry_code');
    }
}

