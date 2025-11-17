<?php

namespace tests\unit\models;

use app\models\EmployeeAttendance;
use app\models\Employee;
use Codeception\Test\Unit;
use Yii;

/**
 * Test EmployeeAttendance model
 */
class EmployeeAttendanceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // Clean up test data
        EmployeeAttendance::deleteAll(['employee_id' => [9990, 9991]]);
        Employee::deleteAll(['id' => [9990, 9991]]);
    }

    protected function _after()
    {
        // Clean up test data
        EmployeeAttendance::deleteAll(['employee_id' => [9990, 9991]]);
        Employee::deleteAll(['id' => [9990, 9991]]);
    }

    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        $model = new EmployeeAttendance();
        $this->assertInstanceOf(EmployeeAttendance::class, $model);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(EmployeeAttendance::tableName())->stringContainsString('employee_attendance');
    }

    /**
     * Test required fields validation
     */
    public function testRequiredFields()
    {
        $model = new EmployeeAttendance();

        verify($model->validate())->false();
        verify($model->hasErrors('employee_id'))->true();
        verify($model->hasErrors('attendance_date'))->true();
    }

    /**
     * Test valid attendance type
     */
    public function testAttendanceTypeValidation()
    {
        $employee = $this->createTestEmployee(9990);

        // Valid types
        $model = new EmployeeAttendance();
        $model->employee_id = $employee->id;
        $model->attendance_date = date('Y-m-d');
        $model->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;

        verify($model->validate())->true();

        // Invalid type
        $model->attendance_type = 'invalid_type';
        verify($model->validate())->false();
        verify($model->hasErrors('attendance_type'))->true();
    }

    /**
     * Test default attendance type
     */
    public function testDefaultAttendanceType()
    {
        $model = new EmployeeAttendance();
        $model->loadDefaultValues();

        verify($model->attendance_type)->equals(EmployeeAttendance::TYPE_FULL_DAY);
    }

    /**
     * Test creating attendance record
     */
    public function testCreateAttendance()
    {
        $employee = $this->createTestEmployee(9990);

        $model = new EmployeeAttendance();
        $model->employee_id = $employee->id;
        $model->attendance_date = '2025-01-15';
        $model->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;
        $model->notes = 'Test attendance';

        verify($model->save())->true();
        verify($model->id)->notNull();

        // Verify it was saved
        $saved = EmployeeAttendance::findOne($model->id);
        verify($saved)->notNull();
        verify($saved->employee_id)->equals($employee->id);
        verify($saved->attendance_date)->equals('2025-01-15');
        verify($saved->attendance_type)->equals(EmployeeAttendance::TYPE_FULL_DAY);
    }

    /**
     * Test unique constraint for employee and date
     */
    public function testUniqueEmployeeDateConstraint()
    {
        $employee = $this->createTestEmployee(9990);

        // Create first attendance
        $model1 = new EmployeeAttendance();
        $model1->employee_id = $employee->id;
        $model1->attendance_date = '2025-01-15';
        $model1->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;

        verify($model1->save())->true();

        // Try to create duplicate
        $model2 = new EmployeeAttendance();
        $model2->employee_id = $employee->id;
        $model2->attendance_date = '2025-01-15';
        $model2->attendance_type = EmployeeAttendance::TYPE_HALF_DAY;

        verify($model2->save())->false();
        verify($model2->hasErrors('attendance_date'))->true();
    }

    /**
     * Test attendance value calculation
     */
    public function testAttendanceValue()
    {
        $model = new EmployeeAttendance();

        $model->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;
        verify($model->getAttendanceValue())->equals(1.0);

        $model->attendance_type = EmployeeAttendance::TYPE_HALF_DAY;
        verify($model->getAttendanceValue())->equals(0.5);

        $model->attendance_type = EmployeeAttendance::TYPE_DAY_1_5;
        verify($model->getAttendanceValue())->equals(1.5);
    }

    /**
     * Test attendance type label
     */
    public function testAttendanceTypeLabel()
    {
        $model = new EmployeeAttendance();

        $model->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;
        verify($model->getAttendanceTypeLabel())->equals('Full Day');

        $model->attendance_type = EmployeeAttendance::TYPE_HALF_DAY;
        verify($model->getAttendanceTypeLabel())->equals('Half Day');

        $model->attendance_type = EmployeeAttendance::TYPE_DAY_1_5;
        verify($model->getAttendanceTypeLabel())->equals('1.5 Days');
    }

    /**
     * Test get attendance types
     */
    public function testGetAttendanceTypes()
    {
        $types = EmployeeAttendance::getAttendanceTypes();

        verify($types)->notEmpty();
        $this->assertArrayHasKey(EmployeeAttendance::TYPE_FULL_DAY, $types);
        $this->assertArrayHasKey(EmployeeAttendance::TYPE_HALF_DAY, $types);
        $this->assertArrayHasKey(EmployeeAttendance::TYPE_DAY_1_5, $types);
    }

    /**
     * Test employee relationship
     */
    public function testEmployeeRelationship()
    {
        $employee = $this->createTestEmployee(9990);

        $model = new EmployeeAttendance();
        $model->employee_id = $employee->id;
        $model->attendance_date = '2025-01-15';
        $model->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;
        $model->save();

        $retrieved = EmployeeAttendance::findOne($model->id);
        verify($retrieved->employee)->notNull();
        verify($retrieved->employee->id)->equals($employee->id);
        verify($retrieved->employee->fullName)->equals('Test Employee');
    }

    /**
     * Test monthly summary
     */
    public function testMonthlySummary()
    {
        $employee = $this->createTestEmployee(9990);

        // Create test data for January 2025
        $this->createAttendance($employee->id, '2025-01-10', EmployeeAttendance::TYPE_FULL_DAY);
        $this->createAttendance($employee->id, '2025-01-11', EmployeeAttendance::TYPE_FULL_DAY);
        $this->createAttendance($employee->id, '2025-01-12', EmployeeAttendance::TYPE_HALF_DAY);
        $this->createAttendance($employee->id, '2025-01-13', EmployeeAttendance::TYPE_DAY_1_5);

        $summary = EmployeeAttendance::getMonthlySummary($employee->id, '2025', '01');

        verify($summary)->notEmpty();
        verify($summary['full_day']['count'])->equals(2);
        verify($summary['full_day']['days'])->equals(2.0);
        verify($summary['half_day']['count'])->equals(1);
        verify($summary['half_day']['days'])->equals(0.5);
        verify($summary['day_1_5']['count'])->equals(1);
        verify($summary['day_1_5']['days'])->equals(1.5);
        verify($summary['total_days'])->equals(4.0); // 2 + 0.5 + 1.5
    }

    /**
     * Test yearly summary
     */
    public function testYearlySummary()
    {
        $employee = $this->createTestEmployee(9990);

        // Create test data for different months
        $this->createAttendance($employee->id, '2025-01-10', EmployeeAttendance::TYPE_FULL_DAY);
        $this->createAttendance($employee->id, '2025-02-10', EmployeeAttendance::TYPE_FULL_DAY);
        $this->createAttendance($employee->id, '2025-02-11', EmployeeAttendance::TYPE_HALF_DAY);

        $summary = EmployeeAttendance::getYearlySummary($employee->id, '2025');

        verify($summary)->notEmpty();
        $this->assertArrayHasKey('01', $summary);
        $this->assertArrayHasKey('02', $summary);

        verify($summary['01']['total_days'])->equals(1.0);
        verify($summary['02']['total_days'])->equals(1.5);
    }

    /**
     * Test date range summary
     */
    public function testDateRangeSummary()
    {
        $employee = $this->createTestEmployee(9990);

        // Create test data
        $this->createAttendance($employee->id, '2025-01-10', EmployeeAttendance::TYPE_FULL_DAY);
        $this->createAttendance($employee->id, '2025-01-11', EmployeeAttendance::TYPE_FULL_DAY);
        $this->createAttendance($employee->id, '2025-01-12', EmployeeAttendance::TYPE_HALF_DAY);
        $this->createAttendance($employee->id, '2025-01-20', EmployeeAttendance::TYPE_FULL_DAY); // Outside range

        $summary = EmployeeAttendance::getDateRangeSummary($employee->id, '2025-01-10', '2025-01-15');

        verify($summary['total_records'])->equals(3); // Only 3 in range
        verify($summary['total_days'])->equals(2.5); // 1 + 1 + 0.5
    }

    /**
     * Test updating attendance
     */
    public function testUpdateAttendance()
    {
        $employee = $this->createTestEmployee(9990);

        $model = new EmployeeAttendance();
        $model->employee_id = $employee->id;
        $model->attendance_date = '2025-01-15';
        $model->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;
        $model->save();

        // Update
        $model->attendance_type = EmployeeAttendance::TYPE_HALF_DAY;
        $model->notes = 'Updated note';

        verify($model->save())->true();

        // Verify update
        $updated = EmployeeAttendance::findOne($model->id);
        verify($updated->attendance_type)->equals(EmployeeAttendance::TYPE_HALF_DAY);
        verify($updated->notes)->equals('Updated note');
    }

    /**
     * Test deleting attendance
     */
    public function testDeleteAttendance()
    {
        $employee = $this->createTestEmployee(9990);

        $model = new EmployeeAttendance();
        $model->employee_id = $employee->id;
        $model->attendance_date = '2025-01-15';
        $model->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;
        $model->save();

        $id = $model->id;

        verify($model->delete())->notEquals(false);

        // Verify deletion
        $deleted = EmployeeAttendance::findOne($id);
        verify($deleted)->null();
    }

    /**
     * Test multiple employees don't interfere
     */
    public function testMultipleEmployees()
    {
        $employee1 = $this->createTestEmployee(9990);
        $employee2 = $this->createTestEmployee(9991);

        // Same date, different employees - should be allowed
        $model1 = new EmployeeAttendance();
        $model1->employee_id = $employee1->id;
        $model1->attendance_date = '2025-01-15';
        $model1->attendance_type = EmployeeAttendance::TYPE_FULL_DAY;

        $model2 = new EmployeeAttendance();
        $model2->employee_id = $employee2->id;
        $model2->attendance_date = '2025-01-15';
        $model2->attendance_type = EmployeeAttendance::TYPE_HALF_DAY;

        verify($model1->save())->true();
        verify($model2->save())->true();

        // Verify both exist
        $count = EmployeeAttendance::find()
            ->where(['attendance_date' => '2025-01-15'])
            ->count();

        verify($count)->equals(2);
    }

    /**
     * Helper method to create test employee
     */
    private function createTestEmployee($id)
    {
        $employee = new Employee();

        // Detach all behaviors that set created_by/updated_by
        foreach ($employee->getBehaviors() as $behaviorName => $behavior) {
            $employee->detachBehavior($behaviorName);
        }

        $employee->id = $id;
        $employee->first_name = 'Test';
        $employee->last_name = 'Employee';
        $employee->nic = str_pad($id, 9, '0', STR_PAD_LEFT) . 'V';
        $employee->phone = '0' . str_pad($id, 9, '0', STR_PAD_LEFT);
        $employee->position = 'Test Position';
        $employee->department = 'Test Department';
        $employee->hire_date = '2024-01-01';
        $employee->created_at = time();
        $employee->updated_at = time();
        $employee->created_by = 1;
        $employee->updated_by = 1;
        $employee->save();

        return $employee;
    }

    /**
     * Helper method to create attendance
     */
    private function createAttendance($employeeId, $date, $type)
    {
        $model = new EmployeeAttendance();

        // Detach all behaviors that set created_by/updated_by
        foreach ($model->getBehaviors() as $behaviorName => $behavior) {
            $model->detachBehavior($behaviorName);
        }

        $model->employee_id = $employeeId;
        $model->attendance_date = $date;
        $model->attendance_type = $type;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;
        $model->save();

        return $model;
    }
}

