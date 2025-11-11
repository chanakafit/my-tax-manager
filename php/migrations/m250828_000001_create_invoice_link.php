<?php

use yii\db\Migration;

class m250828_000001_create_invoice_link extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%invoice_link}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'token' => $this->string(64)->notNull()->unique(),
            'expires_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-invoice_link-invoice_id',
            '{{%invoice_link}}',
            'invoice_id',
            '{{%invoice}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-invoice_link-token',
            '{{%invoice_link}}',
            'token',
            true
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%invoice_link}}');
    }
}
