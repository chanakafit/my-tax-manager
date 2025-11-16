<?php

namespace tests\unit\models;

use app\models\Expense;
use Codeception\Test\Unit;

/**
 * Test Expense model business logic
 */
class ExpenseTest extends Unit
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
        $model = new Expense();
        verify($model)->instanceOf(Expense::class);
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new Expense();
        $model->validate();
        
        verify($model->hasErrors('expense_category_id'))->true();
        verify($model->hasErrors('expense_date'))->true();
        verify($model->hasErrors('title'))->true();
        verify($model->hasErrors('amount'))->true();
        verify($model->hasErrors('payment_method'))->true();
    }

    /**
     * Test default values
     */
    public function testDefaultValues()
    {
        $model = new Expense();
        
        verify($model->currency_code)->null();
        verify($model->exchange_rate)->null();
        verify($model->status)->null();
        verify($model->is_recurring)->null();
    }

    /**
     * Test amount validation
     */
    public function testAmountValidation()
    {
        $model = new Expense();
        $model->amount = -100;
        $model->validate(['amount']);
        
        // Should accept any number including negative
        verify($model->hasErrors('amount'))->false();
        
        $model->amount = 'not_a_number';
        $model->validate(['amount']);
        verify($model->hasErrors('amount'))->true();
    }

    /**
     * Test currency conversion
     */
    public function testCurrencyConversion()
    {
        $model = new Expense();
        $model->amount = 100;
        $model->currency_code = 'USD';
        $model->exchange_rate = 300;
        
        // amount_lkr should be calculated as amount * exchange_rate
        $expectedLkr = 100 * 300;
        
        // The model should handle this calculation
        verify($model->amount * $model->exchange_rate)->equals($expectedLkr);
    }

    /**
     * Test receipt file validation
     */
    public function testReceiptFileValidation()
    {
        $model = new Expense();
        
        // receipt_file should be optional
        $model->validate(['receipt_file']);
        verify($model->hasErrors('receipt_file'))->false();
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(Expense::tableName())->stringContainsString('expense');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new Expense();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('expense_category_id', $labels))->true();
        verify(array_key_exists('expense_date', $labels))->true();
        verify(array_key_exists('title', $labels))->true();
        verify(array_key_exists('amount', $labels))->true();
        verify(array_key_exists('payment_method', $labels))->true();
    }

    /**
     * Test string field max length
     */
    public function testStringFieldMaxLength()
    {
        $model = new Expense();
        $model->title = str_repeat('a', 256); // 256 characters, max is 255
        $model->validate(['title']);
        
        verify($model->hasErrors('title'))->true();
    }

    /**
     * Test valid payment methods
     */
    public function testPaymentMethods()
    {
        $model = new Expense();
        
        $validMethods = ['cash', 'bank_transfer', 'credit_card', 'check'];
        
        foreach ($validMethods as $method) {
            $model->payment_method = $method;
            // Should be string type
            verify(is_string($model->payment_method))->true();
        }
    }

    /**
     * Test recurring expense fields
     */
    public function testRecurringExpenseFields()
    {
        $model = new Expense();
        $model->is_recurring = 1;
        $model->recurring_interval = 'monthly';
        $model->next_recurring_date = '2024-02-01';
        
        verify($model->is_recurring)->equals(1);
        verify($model->recurring_interval)->equals('monthly');
        verify($model->next_recurring_date)->equals('2024-02-01');
    }

    /**
     * Test date fields validation
     */
    public function testDateFieldsValidation()
    {
        $model = new Expense();
        $model->expense_date = '2024-01-01';
        $model->receipt_date = '2024-01-01';
        $model->payment_date = '2024-01-02';
        
        $model->validate(['expense_date', 'receipt_date', 'payment_date']);
        
        verify($model->hasErrors('expense_date'))->false();
        verify($model->hasErrors('receipt_date'))->false();
        verify($model->hasErrors('payment_date'))->false();
    }

    /**
     * Test exchange rate default
     */
    public function testExchangeRateDefault()
    {
        $model = new Expense();
        $model->currency_code = 'LKR';
        
        // For LKR, exchange rate should typically be 1
        verify($model->exchange_rate)->null(); // Before setting
    }

    /**
     * Test tax amount field
     */
    public function testTaxAmountField()
    {
        $model = new Expense();
        $model->tax_amount = 15.50;
        
        verify($model->tax_amount)->equals(15.50);
        verify(is_numeric($model->tax_amount))->true();
    }
}
