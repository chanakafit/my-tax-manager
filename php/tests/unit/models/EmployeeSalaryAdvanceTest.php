<?php

namespace tests\unit\models;

use app\models\Employee;
use app\models\EmployeeSalaryAdvance;
use Codeception\Test\Unit;

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
     * @var Employee
     */
    private $employee;

    protected function _before()
    {
        parent::_before();

        // Create a test employee
        $this->employee = new Employee();
        // Detach behaviors to prevent null created_by/updated_by in test environment
        $this->employee->detachBehaviors();
        $this->employee->first_name = 'John';
        $this->employee->last_name = 'Doe';
        $this->employee->nic = '199012345678';
        $this->employee->phone = '0771234567';
        $this->employee->position = 'Software Engineer';
        $this->employee->department = 'IT';
        $this->employee->hire_date = '2025-01-01';
        $this->employee->created_at = time();
        $this->employee->updated_at = time();
        $this->employee->created_by = 1;
        $this->employee->updated_by = 1;
        $this->employee->save(false);
    }

    protected function _after()
    {
        parent::_after();

        // Clean up
        if ($this->employee && !$this->employee->isNewRecord) {
            $this->employee->delete();
        }
    }

    /**
     * Test create salary advance
     */
    public function testCreateSalaryAdvance()
    {
        $model = new EmployeeSalaryAdvance();
        $model->employee_id = $this->employee->id;
        $model->advance_date = '2025-11-01';
        $model->amount = 50000;
        $model->reason = 'Emergency';
        $model->notes = 'Medical expenses';

        verify($model->save())->true();
        verify($model->amount)->equals(50000);
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
        $employee = $this->tester->grabFixture('employees', 'employee1');
        $model->employee_id = $employee->id;
        $model->advance_date = '2025-11-01';
        $model->amount = -1000;
        verify($model->validate())->false();
        verify($model->hasErrors('amount'))->true();

        // Test valid data
        $model->amount = 10000;
        verify($model->validate())->true();
    }

    /**
     * Test monthly overview
     */
    public function testMonthlyOverview()
    {
        // Create advances in different months
        $advance1 = new EmployeeSalaryAdvance();
        $advance1->employee_id = $this->employee->id;
        $advance1->advance_date = '2025-01-15';
        $advance1->amount = 10000;
        $advance1->save();

        $advance2 = new EmployeeSalaryAdvance();
        $advance2->employee_id = $this->employee->id;
        $advance2->advance_date = '2025-01-20';
        $advance2->amount = 15000;
        $advance2->save();

        $advance3 = new EmployeeSalaryAdvance();
        $advance3->employee_id = $this->employee->id;
        $advance3->advance_date = '2025-02-10';
        $advance3->amount = 20000;
        $advance3->save();

        $overview = EmployeeSalaryAdvance::getMonthlyOverview($this->employee->id, 2025);

        // January should have 2 advances totaling 25000
        verify($overview[1]['count'])->equals(2);
        verify($overview[1]['total'])->equals(25000);

        // February should have 1 advance totaling 20000
        verify($overview[2]['count'])->equals(1);
        verify($overview[2]['total'])->equals(20000);

        // March should have no advances
        verify($overview[3]['count'])->equals(0);
        verify($overview[3]['total'])->equals(0);
    }

    /**
     * Test year to date total
     */
    public function testYearToDateTotal()
    {
        // Create advances in 2025
        $advance1 = new EmployeeSalaryAdvance();
        $advance1->employee_id = $this->employee->id;
        $advance1->advance_date = '2025-01-15';
        $advance1->amount = 10000;
        $advance1->save();

        $advance2 = new EmployeeSalaryAdvance();
        $advance2->employee_id = $this->employee->id;
        $advance2->advance_date = '2025-06-20';
        $advance2->amount = 15000;
        $advance2->save();

        // Create advance in 2024
        $advance3 = new EmployeeSalaryAdvance();
        $advance3->employee_id = $this->employee->id;
        $advance3->advance_date = '2024-12-10';
        $advance3->amount = 20000;
        $advance3->save();

        $yearTotal = EmployeeSalaryAdvance::getYearToDateTotal($this->employee->id, 2025);
        verify($yearTotal)->equals(25000); // Only 2025 advances
    }

    /**
     * Test monthly total
     */
    public function testMonthlyTotal()
    {
        // Create advances in January 2025
        $advance1 = new EmployeeSalaryAdvance();
        $advance1->employee_id = $this->employee->id;
        $advance1->advance_date = '2025-01-15';
        $advance1->amount = 10000;
        $advance1->save();

        $advance2 = new EmployeeSalaryAdvance();
        $advance2->employee_id = $this->employee->id;
        $advance2->advance_date = '2025-01-20';
        $advance2->amount = 15000;
        $advance2->save();

        // Create advance in February
        $advance3 = new EmployeeSalaryAdvance();
        $advance3->employee_id = $this->employee->id;
        $advance3->advance_date = '2025-02-10';
        $advance3->amount = 20000;
        $advance3->save();

        $januaryTotal = EmployeeSalaryAdvance::getMonthlyTotal($this->employee->id, 2025, 1);
        verify($januaryTotal)->equals(25000);

        $februaryTotal = EmployeeSalaryAdvance::getMonthlyTotal($this->employee->id, 2025, 2);
        verify($februaryTotal)->equals(20000);
    }

    /**
     * Test available years
     */
    public function testAvailableYears()
    {
        // Create advances in different years
        $advance1 = new EmployeeSalaryAdvance();
        $advance1->employee_id = $this->employee->id;
        $advance1->advance_date = '2023-01-15';
        $advance1->amount = 10000;
        $advance1->save();

        $advance2 = new EmployeeSalaryAdvance();
        $advance2->employee_id = $this->employee->id;
        $advance2->advance_date = '2025-01-20';
        $advance2->amount = 15000;
        $advance2->save();

        $years = EmployeeSalaryAdvance::getAvailableYears($this->employee->id);

        verify(count($years))->equals(2);
        verify(in_array(2023, $years))->true();
        verify(in_array(2025, $years))->true();
    }

    /**
     * Test total advance amount for employee
     */
    public function testGetTotalAdvanceAmount()
    {
        // Create multiple advances
        $advance1 = new EmployeeSalaryAdvance();
        $advance1->employee_id = $this->employee->id;
        $advance1->advance_date = '2025-11-01';
        $advance1->amount = 50000;
        $advance1->save();

        $advance2 = new EmployeeSalaryAdvance();
        $advance2->employee_id = $this->employee->id;
        $advance2->advance_date = '2025-11-15';
        $advance2->amount = 30000;
        $advance2->save();

        $total = EmployeeSalaryAdvance::getTotalAdvanceAmount($this->employee->id);
        verify($total)->equals(80000);
    }



    /**
     * Test employee relationship
     */
    public function testEmployeeRelationship()
    {
        $salaryAdvance = new EmployeeSalaryAdvance();
        $salaryAdvance->employee_id = $this->employee->id;
        $salaryAdvance->advance_date = '2025-11-01';
        $salaryAdvance->amount = 50000;
        $salaryAdvance->save();

        verify($salaryAdvance->employee)->notNull();
        verify($salaryAdvance->employee)->instanceOf(Employee::class);
    }

    /**
     * Test update salary advance
     */
    public function testUpdateSalaryAdvance()
    {
        $salaryAdvance = new EmployeeSalaryAdvance();
        $salaryAdvance->employee_id = $this->employee->id;
        $salaryAdvance->advance_date = '2025-11-01';
        $salaryAdvance->amount = 50000;
        $salaryAdvance->save();

        $salaryAdvance->repaid_amount = 10000;
        $salaryAdvance->notes = 'Partial payment made';

        verify($salaryAdvance->save())->true();
        verify($salaryAdvance->repaid_amount)->equals(10000);
        verify($salaryAdvance->notes)->equals('Partial payment made');
    }

    /**
     * Test delete salary advance
     */
    public function testDeleteSalaryAdvance()
    {
        $salaryAdvance = new EmployeeSalaryAdvance();
        $salaryAdvance->employee_id = $this->employee->id;
        $salaryAdvance->advance_date = '2025-11-01';
        $salaryAdvance->amount = 50000;
        $salaryAdvance->save();

        $id = $salaryAdvance->id;

        verify($salaryAdvance->delete())->notEquals(false);
        verify(EmployeeSalaryAdvance::findOne($id))->null();
    }

    /**
     * Test cascade delete when employee is deleted
     */
    public function testCascadeDelete()
    {
        $testEmployee = new Employee();
        $testEmployee->detachBehaviors();
        $testEmployee->first_name = 'Jane';
        $testEmployee->last_name = 'Smith';
        $testEmployee->nic = '199112345678';
        $testEmployee->phone = '0772345678';
        $testEmployee->position = 'Manager';
        $testEmployee->department = 'HR';
        $testEmployee->hire_date = '2025-01-01';
        $testEmployee->created_at = time();
        $testEmployee->updated_at = time();
        $testEmployee->created_by = 1;
        $testEmployee->updated_by = 1;
        $testEmployee->save(false);

        $advance = new EmployeeSalaryAdvance();
        $advance->employee_id = $testEmployee->id;
        $advance->advance_date = '2025-11-01';
        $advance->amount = 50000;
        $advance->save();

        $advanceId = $advance->id;

        // Delete employee
        $testEmployee->delete();

        // Verify salary advance is also deleted
        verify(EmployeeSalaryAdvance::findOne($advanceId))->null();
    }

}

