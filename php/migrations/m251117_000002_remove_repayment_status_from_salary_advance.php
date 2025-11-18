<?php

use yii\db\Migration;

/**
 * Removes repayment status fields from salary advance table
 */
class m251117_000002_remove_repayment_status_from_salary_advance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Drop index on repayment_status first
        $this->dropIndex('idx-employee_salary_advance-repayment_status', '{{%employee_salary_advance}}');

        // Drop repayment related columns
        $this->dropColumn('{{%employee_salary_advance}}', 'repayment_status');
        $this->dropColumn('{{%employee_salary_advance}}', 'repaid_amount');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Add columns back
        $this->addColumn('{{%employee_salary_advance}}', 'repayment_status', $this->string(20)->notNull()->defaultValue('pending')->comment('pending, partial, completed'));
        $this->addColumn('{{%employee_salary_advance}}', 'repaid_amount', $this->decimal(10, 2)->notNull()->defaultValue(0));

        // Re-create index
        $this->createIndex(
            'idx-employee_salary_advance-repayment_status',
            '{{%employee_salary_advance}}',
            'repayment_status'
        );
    }
}

