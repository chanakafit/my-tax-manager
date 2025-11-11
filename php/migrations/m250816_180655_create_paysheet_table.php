<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%paysheet}}`.
 */
class m250816_180655_create_paysheet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%paysheet}}', [
            'id' => $this->primaryKey(),
            'employee_id' => $this->integer()->notNull(),
            'pay_period_start' => $this->date()->notNull(),
            'pay_period_end' => $this->date()->notNull(),
            'payment_date' => $this->date()->notNull(),
            'basic_salary' => $this->decimal(10, 2)->notNull(),
            'allowances' => $this->decimal(10, 2)->defaultValue(0),
            'deductions' => $this->decimal(10, 2)->defaultValue(0),
            'tax_amount' => $this->decimal(10, 2)->defaultValue(0),
            'net_salary' => $this->decimal(10, 2)->notNull(),
            'payment_method' => $this->string()->notNull(),
            'payment_reference' => $this->string(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'notes' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Add foreign key to employee
        $this->addForeignKey(
            'fk-paysheet-employee_id',
            '{{%paysheet}}',
            'employee_id',
            '{{%employee}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // Create indexes
        $this->createIndex('idx-paysheet-employee_id', '{{%paysheet}}', 'employee_id');
        $this->createIndex('idx-paysheet-payment_date', '{{%paysheet}}', 'payment_date');
        $this->createIndex('idx-paysheet-status', '{{%paysheet}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%paysheet}}');
    }
}
