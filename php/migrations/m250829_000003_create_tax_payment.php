<?php

use yii\db\Migration;

/**
 * Class m250829_000003_create_tax_payment
 */
class m250829_000003_create_tax_payment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tax_payment}}', [
            'id' => $this->primaryKey(),
            'tax_year' => $this->string(4)->notNull(),
            'payment_date' => $this->date()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'payment_type' => $this->string(20)->notNull(), // 'quarterly', 'final'
            'quarter' => $this->integer(), // 1,2,3,4 for quarterly, null for final
            'reference_number' => $this->string(),
            'notes' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->createIndex('idx-tax_payment-tax_year', '{{%tax_payment}}', 'tax_year');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tax_payment}}');
    }
}
