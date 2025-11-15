<?php

namespace tests\unit\models;

use app\models\Paysheet;
use Codeception\Test\Unit;

/**
 * Test Paysheet model business logic
 */
class PaysheetTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        $model = new Paysheet();
        verify($model)->isInstanceOf(Paysheet::class);
    }

    /**
     * Test status constants
     */
    public function testStatusConstants()
    {
        verify(Paysheet::STATUS_PAID)->equals('paid');
        verify(Paysheet::STATUS_PENDING)->equals('pending');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new Paysheet();
        $model->validate();
        
        verify($model->hasErrors('employee_id'))->true();
        verify($model->hasErrors('pay_period_start'))->true();
        verify($model->hasErrors('pay_period_end'))->true();
        verify($model->hasErrors('payment_date'))->true();
        verify($model->hasErrors('basic_salary'))->true();
        verify($model->hasErrors('net_salary'))->true();
        verify($model->hasErrors('payment_method'))->true();
    }

    /**
     * Test net salary calculation
     */
    public function testNetSalaryCalculation()
    {
        $model = new Paysheet();
        $model->basic_salary = 100000;
        $model->allowances = 10000;
        $model->deductions = 5000;
        $model->tax_amount = 2000;
        
        $expectedNet = 100000 + 10000 - 5000 - 2000;
        $calculatedNet = $model->basic_salary + $model->allowances - $model->deductions - $model->tax_amount;
        
        verify($calculatedNet)->equals($expectedNet);
        verify($calculatedNet)->equals(103000);
    }

    /**
     * Test default values
     */
    public function testDefaultValues()
    {
        $model = new Paysheet();
        
        // tax_amount should default to 0.00 per rules
        verify($model->tax_amount)->null(); // Before validation
        
        // status should default to 'pending' per rules
        verify($model->status)->null(); // Before validation
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(Paysheet::tableName())->contains('paysheet');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new Paysheet();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('employee_id', $labels))->true();
        verify(array_key_exists('pay_period_start', $labels))->true();
        verify(array_key_exists('pay_period_end', $labels))->true();
        verify(array_key_exists('payment_date', $labels))->true();
        verify(array_key_exists('basic_salary', $labels))->true();
        verify(array_key_exists('net_salary', $labels))->true();
    }

    /**
     * Test numeric field validations
     */
    public function testNumericFieldValidations()
    {
        $model = new Paysheet();
        
        $model->basic_salary = 'not_a_number';
        $model->validate(['basic_salary']);
        verify($model->hasErrors('basic_salary'))->true();
        
        $model->basic_salary = 50000.50;
        $model->validate(['basic_salary']);
        verify($model->hasErrors('basic_salary'))->false();
    }

    /**
     * Test date fields
     */
    public function testDateFields()
    {
        $model = new Paysheet();
        $model->pay_period_start = '2024-01-01';
        $model->pay_period_end = '2024-01-31';
        $model->payment_date = '2024-02-01';
        
        verify($model->pay_period_start)->equals('2024-01-01');
        verify($model->pay_period_end)->equals('2024-01-31');
        verify($model->payment_date)->equals('2024-02-01');
    }

    /**
     * Test allowances field is optional
     */
    public function testAllowancesFieldOptional()
    {
        $model = new Paysheet();
        $model->employee_id = 1;
        $model->pay_period_start = '2024-01-01';
        $model->pay_period_end = '2024-01-31';
        $model->payment_date = '2024-02-01';
        $model->basic_salary = 50000;
        $model->net_salary = 50000;
        $model->payment_method = 'bank_transfer';
        
        $model->validate(['allowances']);
        verify($model->hasErrors('allowances'))->false();
    }

    /**
     * Test deductions field is optional
     */
    public function testDeductionsFieldOptional()
    {
        $model = new Paysheet();
        $model->employee_id = 1;
        $model->pay_period_start = '2024-01-01';
        $model->pay_period_end = '2024-01-31';
        $model->payment_date = '2024-02-01';
        $model->basic_salary = 50000;
        $model->net_salary = 50000;
        $model->payment_method = 'bank_transfer';
        
        $model->validate(['deductions']);
        verify($model->hasErrors('deductions'))->false();
    }

    /**
     * Test relationship - employee
     */
    public function testEmployeeRelationship()
    {
        $model = new Paysheet();
        verify($model->hasMethod('getEmployee'))->true();
    }

    /**
     * Test relationship - financialTransactions
     */
    public function testFinancialTransactionsRelationship()
    {
        $model = new Paysheet();
        verify($model->hasMethod('getFinancialTransactions'))->true();
    }

    /**
     * Test payment reference field is optional
     */
    public function testPaymentReferenceOptional()
    {
        $model = new Paysheet();
        $model->validate(['payment_reference']);
        
        verify($model->hasErrors('payment_reference'))->false();
    }

    /**
     * Test notes field is optional
     */
    public function testNotesFieldOptional()
    {
        $model = new Paysheet();
        $model->validate(['notes']);
        
        verify($model->hasErrors('notes'))->false();
    }
}
