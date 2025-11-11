<?php

use yii\db\Migration;

/**
 * Class m250829_000001_add_customer_emails
 */
class m250829_000001_add_customer_emails extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%customer_email}}', [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'email' => $this->string()->notNull(),
            'type' => $this->string(10)->notNull()->defaultValue('to'), // 'to', 'cc', 'bcc'
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-customer_email-customer_id',
            '{{%customer_email}}',
            'customer_id',
            '{{%customer}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-customer_email-email', '{{%customer_email}}', 'email');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('customer_email');
    }
}
