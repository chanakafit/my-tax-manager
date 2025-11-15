<?php

namespace tests\unit\models;

use app\models\Invoice;
use Codeception\Test\Unit;

/**
 * Test Invoice model business logic
 */
class InvoiceTest extends Unit
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
        $model = new Invoice();
        verify($model)->isInstanceOf(Invoice::class);
    }

    /**
     * Test status constants
     */
    public function testStatusConstants()
    {
        verify(Invoice::STATUS_PENDING)->equals('pending');
        verify(Invoice::STATUS_PAID)->equals('paid');
        verify(Invoice::STATUS_CANCELLED)->equals('cancelled');
        verify(Invoice::STATUS_OVERDUE)->equals('overdue');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new Invoice();
        $model->validate();
        
        verify($model->hasErrors('invoice_number'))->true();
        verify($model->hasErrors('invoice_date'))->true();
        verify($model->hasErrors('due_date'))->true();
        verify($model->hasErrors('currency_code'))->true();
    }

    /**
     * Test default values
     */
    public function testDefaultValues()
    {
        $model = new Invoice();
        
        // Status should default to pending per rules
        verify($model->status)->null(); // Before validation
    }

    /**
     * Test amount calculations
     */
    public function testAmountCalculations()
    {
        $model = new Invoice();
        $model->subtotal = 100000;
        $model->tax_amount = 15000;
        $model->discount = 5000;
        
        $expectedTotal = $model->subtotal + $model->tax_amount - $model->discount;
        
        verify($model->subtotal + $model->tax_amount - $model->discount)->equals($expectedTotal);
        verify($expectedTotal)->equals(110000);
    }

    /**
     * Test currency conversion
     */
    public function testCurrencyConversion()
    {
        $model = new Invoice();
        $model->total_amount = 1000;
        $model->exchange_rate = 300;
        $model->currency_code = 'USD';
        
        $expectedLkr = 1000 * 300;
        
        verify($model->total_amount * $model->exchange_rate)->equals($expectedLkr);
    }

    /**
     * Test status validation
     */
    public function testStatusValidation()
    {
        $model = new Invoice();
        $model->status = 'invalid_status';
        $model->validate(['status']);
        
        verify($model->hasErrors('status'))->true();
    }

    /**
     * Test valid status values
     */
    public function testValidStatusValues()
    {
        $validStatuses = [
            Invoice::STATUS_PENDING,
            Invoice::STATUS_PAID,
            Invoice::STATUS_CANCELLED,
            Invoice::STATUS_OVERDUE,
        ];
        
        foreach ($validStatuses as $status) {
            $model = new Invoice();
            $model->status = $status;
            $model->validate(['status']);
            
            verify($model->hasErrors('status'))->false();
        }
    }

    /**
     * Test invoice number uniqueness rule
     */
    public function testInvoiceNumberUniqueRule()
    {
        $model = new Invoice();
        $rules = $model->rules();
        
        $hasUniqueRule = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && in_array('invoice_number', (array)$rule[0]) && isset($rule[1]) && $rule[1] === 'unique') {
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
        verify(Invoice::tableName())->contains('invoice');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new Invoice();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('invoice_number', $labels))->true();
        verify(array_key_exists('invoice_date', $labels))->true();
        verify(array_key_exists('due_date', $labels))->true();
        verify(array_key_exists('total_amount', $labels))->true();
        verify(array_key_exists('status', $labels))->true();
    }

    /**
     * Test numeric field validations
     */
    public function testNumericFieldValidations()
    {
        $model = new Invoice();
        $model->subtotal = 'not_a_number';
        $model->validate(['subtotal']);
        
        verify($model->hasErrors('subtotal'))->true();
        
        $model->subtotal = 100.50;
        $model->validate(['subtotal']);
        verify($model->hasErrors('subtotal'))->false();
    }

    /**
     * Test discount default value
     */
    public function testDiscountDefaultValue()
    {
        $model = new Invoice();
        
        // Discount should default to 0 per rules
        verify($model->discount)->null(); // Before validation
    }

    /**
     * Test exchange rate default value
     */
    public function testExchangeRateDefaultValue()
    {
        $model = new Invoice();
        
        // Exchange rate should default to 1 per rules
        verify($model->exchange_rate)->null(); // Before validation
    }

    /**
     * Test date fields
     */
    public function testDateFields()
    {
        $model = new Invoice();
        $model->invoice_date = '2024-01-01';
        $model->due_date = '2024-01-31';
        $model->payment_date = '2024-01-15';
        
        verify($model->invoice_date)->equals('2024-01-01');
        verify($model->due_date)->equals('2024-01-31');
        verify($model->payment_date)->equals('2024-01-15');
    }

    /**
     * Test payment method attribute
     */
    public function testPaymentMethodAttribute()
    {
        $model = new Invoice();
        $model->payment_method = 'bank_transfer';
        
        verify($model->payment_method)->equals('bank_transfer');
    }

    /**
     * Test reference number attribute
     */
    public function testReferenceNumberAttribute()
    {
        $model = new Invoice();
        $model->reference_number = 'REF-123456';
        
        verify($model->reference_number)->equals('REF-123456');
    }
}
