<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employee_payroll_details}}`.
 */
class m250817_000002_create_employee_payroll_details_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%employee_payroll_details}}', [
            'id' => $this->primaryKey(),
            'employee_id' => $this->integer()->notNull(),
            'bank_account_id' => $this->integer()->notNull(),
            'basic_salary' => $this->decimal(10, 2)->notNull(),
            'allowances' => $this->decimal(10, 2)->defaultValue(0),
            'deductions' => $this->decimal(10, 2)->defaultValue(0),
            'tax_category' => $this->string()->notNull(),
            'payment_frequency' => $this->string()->notNull()->defaultValue('monthly'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Add foreign key constraints
        $this->addForeignKey(
            'fk-employee_payroll_details-employee_id',
            '{{%employee_payroll_details}}',
            'employee_id',
            '{{%employee}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-employee_payroll_details-bank_account_id',
            '{{%employee_payroll_details}}',
            'bank_account_id',
            '{{%bank_account}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-employee_payroll_details-bank_account_id', '{{%employee_payroll_details}}');
        $this->dropForeignKey('fk-employee_payroll_details-employee_id', '{{%employee_payroll_details}}');
        $this->dropTable('{{%employee_payroll_details}}');
    }
}
