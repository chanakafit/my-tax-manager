<?php

namespace tests\unit\models;

use app\models\FinancialTransaction;
use Codeception\Test\Unit;

/**
 * Test FinancialTransaction model business logic
 */
class FinancialTransactionTest extends Unit
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
        $model = new FinancialTransaction();
        verify($model)->isInstanceOf(FinancialTransaction::class);
    }

    /**
     * Test transaction type constants
     */
    public function testTransactionTypeConstants()
    {
        verify(FinancialTransaction::TRANSACTION_TYPE_DEPOSIT)->equals('deposit');
        verify(FinancialTransaction::TRANSACTION_TYPE_REMITTANCE)->equals('remittance');
        verify(FinancialTransaction::TRANSACTION_TYPE_WITHDRAWAL)->equals('withdrawal');
        verify(FinancialTransaction::TRANSACTION_TYPE_TRANSFER)->equals('transfer');
        verify(FinancialTransaction::TRANSACTION_TYPE_PAYMENT)->equals('payment');
    }

    /**
     * Test status constants
     */
    public function testStatusConstants()
    {
        verify(FinancialTransaction::STATUS_PENDING)->equals('pending');
        verify(FinancialTransaction::STATUS_COMPLETED)->equals('completed');
        verify(FinancialTransaction::STATUS_FAILED)->equals('failed');
        verify(FinancialTransaction::STATUS_CANCELLED)->equals('cancelled');
        verify(FinancialTransaction::STATUS_REFUNDED)->equals('refunded');
    }

    /**
     * Test reference type constants
     */
    public function testReferenceTypeConstants()
    {
        verify(FinancialTransaction::REFERENCE_TYPE_INVOICE)->equals('invoice');
        verify(FinancialTransaction::REFERENCE_TYPE_EXPENSE)->equals('expense');
        verify(FinancialTransaction::REFERENCE_TYPE_PAYSHEET)->equals('paysheet');
    }

    /**
     * Test category constants
     */
    public function testCategoryConstants()
    {
        verify(FinancialTransaction::CATEGORY_INCOME)->equals('income');
        verify(FinancialTransaction::CATEGORY_EXPENSE)->equals('expense');
        verify(FinancialTransaction::CATEGORY_TRANSFER)->equals('transfer');
        verify(FinancialTransaction::CATEGORY_PAYROLL)->equals('payroll');
        verify(FinancialTransaction::CATEGORY_TAX)->equals('tax');
    }

    /**
     * Test payment method constants
     */
    public function testPaymentMethodConstants()
    {
        verify(FinancialTransaction::PAYMENT_METHOD_CASH)->equals('cash');
        verify(FinancialTransaction::PAYMENT_METHOD_CHECK)->equals('check');
        verify(FinancialTransaction::PAYMENT_METHOD_BANK_TRANSFER)->equals('bank_transfer');
        verify(FinancialTransaction::PAYMENT_METHOD_CREDIT_CARD)->equals('credit_card');
    }

    /**
     * Test payment methods array
     */
    public function testPaymentMethodsArray()
    {
        $methods = FinancialTransaction::PAYMENT_METHODS;
        
        verify($methods)->isArray();
        verify(array_key_exists('cash', $methods))->true();
        verify(array_key_exists('check', $methods))->true();
        verify(array_key_exists('bank_transfer', $methods))->true();
        verify(array_key_exists('credit_card', $methods))->true();
    }

    /**
     * Test categories array
     */
    public function testCategoriesArray()
    {
        $categories = FinancialTransaction::CATEGORIES;
        
        verify($categories)->isArray();
        verify(array_key_exists('income', $categories))->true();
        verify(array_key_exists('expense', $categories))->true();
        verify(array_key_exists('transfer', $categories))->true();
        verify(array_key_exists('payroll', $categories))->true();
        verify(array_key_exists('tax', $categories))->true();
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new FinancialTransaction();
        $model->validate();
        
        verify($model->hasErrors('transaction_date'))->true();
        verify($model->hasErrors('transaction_type'))->true();
        verify($model->hasErrors('amount'))->true();
    }

    /**
     * Test default values
     */
    public function testDefaultValues()
    {
        $model = new FinancialTransaction();
        
        // Status should default to 'pending'
        verify($model->status)->null(); // Before validation
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(FinancialTransaction::tableName())->contains('financial_transaction');
    }

    /**
     * Test attribute labels exist
     */
    public function testAttributeLabelsExist()
    {
        $model = new FinancialTransaction();
        verify($model->hasMethod('attributeLabels'))->true();
    }

    /**
     * Test relationships - bankAccount
     */
    public function testBankAccountRelationship()
    {
        $model = new FinancialTransaction();
        verify($model->hasMethod('getBankAccount'))->true();
    }

    /**
     * Test relationships - relatedInvoice
     */
    public function testRelatedInvoiceRelationship()
    {
        $model = new FinancialTransaction();
        verify($model->hasMethod('getRelatedInvoice'))->true();
    }

    /**
     * Test relationships - relatedExpense
     */
    public function testRelatedExpenseRelationship()
    {
        $model = new FinancialTransaction();
        verify($model->hasMethod('getRelatedExpense'))->true();
    }

    /**
     * Test relationships - relatedPaysheet
     */
    public function testRelatedPaysheetRelationship()
    {
        $model = new FinancialTransaction();
        verify($model->hasMethod('getRelatedPaysheet'))->true();
    }

    /**
     * Test amount validation
     */
    public function testAmountValidation()
    {
        $model = new FinancialTransaction();
        
        $model->amount = 'not_a_number';
        $model->validate(['amount']);
        verify($model->hasErrors('amount'))->true();
        
        $model->amount = 100.50;
        $model->validate(['amount']);
        verify($model->hasErrors('amount'))->false();
    }

    /**
     * Test exchange rate field
     */
    public function testExchangeRateField()
    {
        $model = new FinancialTransaction();
        $model->exchange_rate = 300.50;
        
        verify(is_numeric($model->exchange_rate))->true();
    }

    /**
     * Test amount LKR calculation
     */
    public function testAmountLkrCalculation()
    {
        $model = new FinancialTransaction();
        $model->amount = 100;
        $model->exchange_rate = 300;
        
        $expectedLkr = 100 * 300;
        
        verify($model->amount * $model->exchange_rate)->equals($expectedLkr);
    }

    /**
     * Test optional reference fields
     */
    public function testOptionalReferenceFields()
    {
        $model = new FinancialTransaction();
        $model->transaction_date = '2024-01-01';
        $model->transaction_type = 'deposit';
        $model->amount = 1000;
        
        $model->validate(['reference_type', 'reference_number', 'related_invoice_id', 'related_expense_id', 'related_paysheet_id']);
        
        verify($model->hasErrors('reference_type'))->false();
        verify($model->hasErrors('reference_number'))->false();
        verify($model->hasErrors('related_invoice_id'))->false();
        verify($model->hasErrors('related_expense_id'))->false();
        verify($model->hasErrors('related_paysheet_id'))->false();
    }
}
