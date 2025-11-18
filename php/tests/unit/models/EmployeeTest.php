<?php

namespace tests\unit\models;

use app\models\Employee;
use Codeception\Test\Unit;
use tests\fixtures\EmployeeFixture;

/**
 * Test Employee model business logic
 */
class EmployeeTest extends Unit
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
        ];
    }

    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        $model = new Employee();
        verify($model)->instanceOf(Employee::class);
    }

    /**
     * Test employee from fixture
     */
    public function testEmployeeFromFixture()
    {
        $employee = $this->tester->grabFixture('employees', 'john_doe');

        verify($employee)->notNull();
        verify($employee->first_name)->equals('John');
        verify($employee->last_name)->equals('Doe');
        verify($employee->nic)->equals('199012345678');
        verify($employee->position)->equals('Software Engineer');
        verify($employee->department)->equals('IT');
    }

    /**
     * Test full name getter
     */
    public function testFullName()
    {
        $employee = $this->tester->grabFixture('employees', 'jane_smith');

        $fullName = $employee->getFullName();
        verify($fullName)->equals('Jane Smith');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new Employee();
        $model->validate();
        
        verify($model->hasErrors('first_name'))->true();
        verify($model->hasErrors('last_name'))->true();
        verify($model->hasErrors('nic'))->true();
        verify($model->hasErrors('phone'))->true();
        verify($model->hasErrors('position'))->true();
        verify($model->hasErrors('department'))->true();
        verify($model->hasErrors('hire_date'))->true();
    }

    /**
     * Test NIC validation pattern
     */
    public function testNicValidation()
    {
        $model = new Employee();
        
        // Valid old NIC format (unique, not in fixtures)
        $model->nic = '955555555V';
        $model->validate(['nic']);
        verify($model->hasErrors('nic'))->false();
        
        // Valid new NIC format (unique, not in fixtures)
        $model->nic = '199999999999';
        $model->validate(['nic']);
        verify($model->hasErrors('nic'))->false();
        
        // Invalid NIC
        $model->nic = 'INVALID123';
        $model->validate(['nic']);
        verify($model->hasErrors('nic'))->true();
    }

    /**
     * Test phone validation pattern
     */
    public function testPhoneValidation()
    {
        $model = new Employee();
        
        // Valid phone
        $model->phone = '0771234567';
        $model->validate(['phone']);
        verify($model->hasErrors('phone'))->false();
        
        // Invalid phone (missing leading 0)
        $model->phone = '771234567';
        $model->validate(['phone']);
        verify($model->hasErrors('phone'))->true();
        
        // Invalid phone (too short)
        $model->phone = '077123456';
        $model->validate(['phone']);
        verify($model->hasErrors('phone'))->true();
    }

    /**
     * Test NIC uniqueness rule
     */
    public function testNicUniqueRule()
    {
        $model = new Employee();
        $rules = $model->rules();
        
        $hasUniqueRule = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && in_array('nic', (array)$rule[0]) && isset($rule[1]) && $rule[1] === 'unique') {
                $hasUniqueRule = true;
                break;
            }
        }
        
        verify($hasUniqueRule)->true();
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(Employee::tableName())->stringContainsString('employee');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new Employee();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('first_name', $labels))->true();
        verify(array_key_exists('last_name', $labels))->true();
        verify(array_key_exists('nic', $labels))->true();
        verify(array_key_exists('phone', $labels))->true();
        verify(array_key_exists('position', $labels))->true();
        verify(array_key_exists('department', $labels))->true();
        verify(array_key_exists('hire_date', $labels))->true();
    }

    /**
     * Test relationships - paysheets
     */
    public function testPaysheetsRelationship()
    {
        $model = new Employee();
        verify($model->hasMethod('getPaysheets'))->true();
    }

    /**
     * Test relationships - employeePayrollDetails
     */
    public function testEmployeePayrollDetailsRelationship()
    {
        $model = new Employee();
        verify($model->hasMethod('getEmployeePayrollDetails'))->true();
    }

    /**
     * Test hire date validation
     */
    public function testHireDateValidation()
    {
        $model = new Employee();
        $model->hire_date = '2024-01-01';
        $model->validate(['hire_date']);
        
        verify($model->hasErrors('hire_date'))->false();
    }

    /**
     * Test left date is optional
     */
    public function testLeftDateOptional()
    {
        $model = new Employee();
        $model->first_name = 'John';
        $model->last_name = 'Doe';
        $model->nic = '912345678V';
        $model->phone = '0771234567';
        $model->position = 'Developer';
        $model->department = 'IT';
        $model->hire_date = '2024-01-01';
        
        $model->validate(['left_date']);
        verify($model->hasErrors('left_date'))->false();
    }

    /**
     * Test string field max length
     */
    public function testStringFieldMaxLength()
    {
        $model = new Employee();
        $model->first_name = str_repeat('a', 256); // 256 characters, max is 255
        $model->validate(['first_name']);
        
        verify($model->hasErrors('first_name'))->true();
    }
}
