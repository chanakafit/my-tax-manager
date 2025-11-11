<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bank_account}}`.
 */
class m250816_180700_create_bank_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bank_account}}', [
            'id' => $this->primaryKey(),
            'account_name' => $this->string()->notNull(),
            'account_number' => $this->string()->notNull(),
            'bank_name' => $this->string()->notNull(),
            'branch_name' => $this->string(),
            'swift_code' => $this->string(),
            'account_type' => $this->string()->notNull(),
            'currency' => $this->string(3)->notNull()->defaultValue('LKR'),
            'is_active' => $this->boolean()->defaultValue(true),
            'notes' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex('idx-bank_account-account_number', '{{%bank_account}}', 'account_number', true);
        $this->createIndex('idx-bank_account-is_active', '{{%bank_account}}', 'is_active');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bank_account}}');
    }
}
