<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%liability}}`.
 */
class m251109_000003_create_liability_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%liability}}', [
            'id' => $this->primaryKey(),
            'liability_type' => $this->string(20)->notNull(), // business or personal
            'liability_category' => $this->string(50)->notNull(), // loan or leasing
            'lender_name' => $this->string()->notNull(),
            'description' => $this->text(),
            'original_amount' => $this->decimal(15, 2)->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date(),
            'interest_rate' => $this->decimal(5, 2),
            'monthly_payment' => $this->decimal(15, 2),
            'status' => $this->string(20)->defaultValue('active'), // active, settled
            'settlement_date' => $this->date(),
            'notes' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create indexes
        $this->createIndex('idx-liability-liability_type', '{{%liability}}', 'liability_type');
        $this->createIndex('idx-liability-status', '{{%liability}}', 'status');
        $this->createIndex('idx-liability-start_date', '{{%liability}}', 'start_date');

        // Add comments
        $this->addCommentOnColumn('{{%liability}}', 'liability_type', 'Type: business or personal');
        $this->addCommentOnColumn('{{%liability}}', 'liability_category', 'Category: loan or leasing');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%liability}}');
    }
}

