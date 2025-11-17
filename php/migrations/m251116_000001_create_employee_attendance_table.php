<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employee_attendance}}`.
 */
class m251116_000001_create_employee_attendance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%employee_attendance}}', [
            'id' => $this->primaryKey(),
            'employee_id' => $this->integer()->notNull(),
            'attendance_date' => $this->date()->notNull(),
            'attendance_type' => $this->string(20)->notNull()->defaultValue('full_day')
                ->comment('full_day, half_day, or day_1_5'),
            'notes' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        // Add foreign key for employee_id
        $this->addForeignKey(
            'fk-employee_attendance-employee_id',
            '{{%employee_attendance}}',
            'employee_id',
            '{{%employee}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add unique index to prevent duplicate attendance records for same employee on same date
        $this->createIndex(
            'idx-employee_attendance-unique',
            '{{%employee_attendance}}',
            ['employee_id', 'attendance_date'],
            true
        );

        // Add index for faster date range queries
        $this->createIndex(
            'idx-employee_attendance-date',
            '{{%employee_attendance}}',
            'attendance_date'
        );

        // Add index for employee_id
        $this->createIndex(
            'idx-employee_attendance-employee_id',
            '{{%employee_attendance}}',
            'employee_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-employee_attendance-employee_id', '{{%employee_attendance}}');
        $this->dropTable('{{%employee_attendance}}');
    }
}

