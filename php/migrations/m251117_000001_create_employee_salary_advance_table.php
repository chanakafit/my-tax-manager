<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employee_salary_advance}}`.
 */
class m251117_000001_create_employee_salary_advance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%employee_salary_advance}}', [
            'id' => $this->primaryKey(),
            'employee_id' => $this->integer()->notNull(),
            'advance_date' => $this->date()->notNull(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'reason' => $this->string(500),
            'repayment_status' => $this->string(20)->notNull()->defaultValue('pending')
                ->comment('pending, partial, completed'),
            'repaid_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'notes' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Add foreign key for employee_id
        $this->addForeignKey(
            'fk-employee_salary_advance-employee_id',
            '{{%employee_salary_advance}}',
            'employee_id',
            '{{%employee}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add index for employee_id
        $this->createIndex(
            'idx-employee_salary_advance-employee_id',
            '{{%employee_salary_advance}}',
            'employee_id'
        );

        // Add index for advance_date
        $this->createIndex(
            'idx-employee_salary_advance-advance_date',
            '{{%employee_salary_advance}}',
            'advance_date'
        );

        // Add index for repayment_status
        $this->createIndex(
            'idx-employee_salary_advance-repayment_status',
            '{{%employee_salary_advance}}',
            'repayment_status'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-employee_salary_advance-employee_id', '{{%employee_salary_advance}}');
        $this->dropTable('{{%employee_salary_advance}}');
    }
}

