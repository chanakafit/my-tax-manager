<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%paysheet_suggestion}}`.
 */
class m251112_085025_create_paysheet_suggestion_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%paysheet_suggestion}}', [
            'id' => $this->primaryKey(),
            'employee_id' => $this->integer()->notNull(),
            'suggested_month' => $this->date()->notNull()->comment('First day of the month for which paysheet is suggested'),
            'basic_salary' => $this->decimal(10, 2)->notNull()->comment('Suggested basic salary amount'),
            'allowances' => $this->decimal(10, 2)->defaultValue(0),
            'deductions' => $this->decimal(10, 2)->defaultValue(0),
            'tax_amount' => $this->decimal(10, 2)->defaultValue(0),
            'net_salary' => $this->decimal(10, 2)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('pending, approved, rejected'),
            'notes' => $this->text(),
            'generated_at' => $this->integer()->notNull()->comment('When this suggestion was generated'),
            'actioned_at' => $this->integer(),
            'actioned_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Add foreign key to employee
        $this->addForeignKey(
            'fk-paysheet_suggestion-employee_id',
            '{{%paysheet_suggestion}}',
            'employee_id',
            '{{%employee}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add foreign key to user for actioned_by
        $this->addForeignKey(
            'fk-paysheet_suggestion-actioned_by',
            '{{%paysheet_suggestion}}',
            'actioned_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Create indexes
        $this->createIndex('idx-paysheet_suggestion-employee_id', '{{%paysheet_suggestion}}', 'employee_id');
        $this->createIndex('idx-paysheet_suggestion-suggested_month', '{{%paysheet_suggestion}}', 'suggested_month');
        $this->createIndex('idx-paysheet_suggestion-status', '{{%paysheet_suggestion}}', 'status');
        $this->createIndex('idx-paysheet_suggestion-employee_month', '{{%paysheet_suggestion}}', ['employee_id', 'suggested_month'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-paysheet_suggestion-employee_id', '{{%paysheet_suggestion}}');
        $this->dropForeignKey('fk-paysheet_suggestion-actioned_by', '{{%paysheet_suggestion}}');
        $this->dropTable('{{%paysheet_suggestion}}');
    }
}

