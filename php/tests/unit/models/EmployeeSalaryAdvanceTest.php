<?php

namespace tests\unit\models;

use app\models\Employee;
use app\models\EmployeeSalaryAdvance;
use Codeception\Test\Unit;
use tests\fixtures\EmployeeFixture;
use tests\fixtures\EmployeeSalaryAdvanceFixture;

/**
 * Test EmployeeSalaryAdvance model
 */
class EmployeeSalaryAdvanceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Load fixtures before each test
     */
    public function _fixtures()
    {
        return [
            'employees' => [
                'class' => EmployeeFixture::class,
            ],
            'advances' => [
                'class' => EmployeeSalaryAdvanceFixture::class,
            ],
        ];
    }

    /**
     * Test create salary advance
     */
    public function testCreateSalaryAdvance()
    {
        $employee = $this->tester->grabFixture('employees', 'john_doe');

        $model = new EmployeeSalaryAdvance();
        $model->employee_id = $employee->id;
        $model->advance_date = '2025-11-18';
        $model->amount = 50000;
        $model->reason = 'Emergency';
        $model->notes = 'Medical expenses';

        verify($model->save())->true();
        verify($model->amount)->equals(50000.00);
        verify($model->reason)->equals('Emergency');
    }

    /**
     * Test validation rules
     */
    public function testValidation()
    {
        $model = new EmployeeSalaryAdvance();

        // Test required fields
        verify($model->validate())->false();
        verify($model->hasErrors('employee_id'))->true();
        verify($model->hasErrors('advance_date'))->true();
        verify($model->hasErrors('amount'))->true();

        // Test negative amount
        $employee = $this->tester->grabFixture('employees', 'john_doe');
        $model->employee_id = $employee->id;
        $model->advance_date = '2025-11-18';
        $model->amount = -1000;
        verify($model->validate())->false();
        verify($model->hasErrors('amount'))->true();

        // Test valid data
        $model->amount = 10000;
        verify($model->validate())->true();
    }

    /**
     * Test employee relationship
     */
    public function testEmployeeRelationship()
    {
        $advance = $this->tester->grabFixture('advances', 'john_jan_advance');

        verify($advance->employee)->notNull();
        verify($advance->employee)->instanceOf(Employee::class);
        verify($advance->employee->first_name)->equals('John');
        verify($advance->employee->last_name)->equals('Doe');
    }

    /**
     * Test monthly overview
     */
    public function testMonthlyOverview()
    {
        $employee = $this->tester->grabFixture('employees', 'john_doe');

        // Fixtures already have: jan (50k), feb (30k) advances for john_doe
        $overview = EmployeeSalaryAdvance::getMonthlyOverview($employee->id, 2025);

        // January should have 1 advance totaling 50000
        verify($overview[1]['count'])->equals(1);
        verify($overview[1]['total'])->equals(50000.00);

        // February should have 1 advance totaling 30000
        verify($overview[2]['count'])->equals(1);
        verify($overview[2]['total'])->equals(30000.00);

        // March should have no advances (but fixture has one for jane)
        verify($overview[3]['count'])->equals(0);
        verify($overview[3]['total'])->equals(0);

        // November should have 1 advance
        verify($overview[11]['count'])->equals(1);
        verify($overview[11]['total'])->equals(25000.00);
    }

    /**
     * Test year to date total
     */
    public function testYearToDateTotal()
    {
        $employee = $this->tester->grabFixture('employees', 'john_doe');

        // john_doe has advances in jan (50k), feb (30k), nov (25k) = 105k total for 2025
        $yearTotal = EmployeeSalaryAdvance::getYearToDateTotal($employee->id, 2025);
        verify($yearTotal)->equals(105000.00);
    }

    /**
     * Test monthly total
     */
    public function testMonthlyTotal()
    {
        $employee = $this->tester->grabFixture('employees', 'john_doe');

        // January: 50000
        $januaryTotal = EmployeeSalaryAdvance::getMonthlyTotal($employee->id, 2025, 1);
        verify($januaryTotal)->equals(50000.00);

        // February: 30000
        $februaryTotal = EmployeeSalaryAdvance::getMonthlyTotal($employee->id, 2025, 2);
        verify($februaryTotal)->equals(30000.00);

        // November: 25000
        $novemberTotal = EmployeeSalaryAdvance::getMonthlyTotal($employee->id, 2025, 11);
        verify($novemberTotal)->equals(25000.00);

        // March: 0 (no advances)
        $marchTotal = EmployeeSalaryAdvance::getMonthlyTotal($employee->id, 2025, 3);
        verify($marchTotal)->equals(0);
    }

    /**
     * Test available years
     */
    public function testAvailableYears()
    {
        $employee = $this->tester->grabFixture('employees', 'john_doe');

        // All advances in fixtures are from 2025
        $years = EmployeeSalaryAdvance::getAvailableYears($employee->id);

        verify(count($years))->greaterThan(0);
        verify(in_array(2025, $years))->true();
    }

    /**
     * Test total advance amount for employee
     */
    public function testGetTotalAdvanceAmount()
    {
        $employee = $this->tester->grabFixture('employees', 'john_doe');

        // john_doe has 3 advances: 50k + 30k + 25k = 105k
        $total = EmployeeSalaryAdvance::getTotalAdvanceAmount($employee->id);
        verify($total)->equals(105000.00);
    }

    /**
     * Test update salary advance
     */
    public function testUpdateSalaryAdvance()
    {
        $advance = $this->tester->grabFixture('advances', 'john_jan_advance');

        $advance->amount = 60000.00;
        $advance->reason = 'Updated reason';
        verify($advance->save())->true();

        $updatedAdvance = EmployeeSalaryAdvance::findOne($advance->id);
        verify($updatedAdvance->amount)->equals(60000.00);
        verify($updatedAdvance->reason)->equals('Updated reason');
    }

    /**
     * Test delete salary advance
     */
    public function testDeleteSalaryAdvance()
    {
        $advance = $this->tester->grabFixture('advances', 'robert_nov_advance');
        $advanceId = $advance->id;

        verify($advance->delete())->notNull();
        verify(EmployeeSalaryAdvance::findOne($advanceId))->null();
    }

    /**
     * Test cascade delete when employee is deleted
     */
    public function testCascadeDeleteOnEmployeeDelete()
    {
        $employee = $this->tester->grabFixture('employees', 'robert_brown');
        $advanceId = $this->tester->grabFixture('advances', 'robert_nov_advance')->id;

        // Delete related paysheets first (if any) to avoid foreign key constraint
        \app\models\Paysheet::deleteAll(['employee_id' => $employee->id]);

        // Delete employee
        verify($employee->delete())->notNull();

        // Advance should also be deleted due to CASCADE
        verify(EmployeeSalaryAdvance::findOne($advanceId))->null();
    }
}

