<?php

namespace tests\unit\models;

use app\models\Expense;
use app\models\ExpenseCategory;
use app\models\Vendor;
use Codeception\Test\Unit;
use tests\fixtures\ExpenseFixture;
use tests\fixtures\ExpenseCategoryFixture;
use tests\fixtures\VendorFixture;

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
     * Load fixtures before each test
     */
    public function _fixtures()
    {
        return [
            'categories' => [
                'class' => ExpenseCategoryFixture::class,
            ],
            'vendors' => [
                'class' => VendorFixture::class,
            ],
            'expenses' => [
                'class' => ExpenseFixture::class,
            ],
        ];
    }

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
     * Test expense with fixture data
     */
    public function testExpenseFromFixture()
    {
        $expense = $this->tester->grabFixture('expenses', 'electricity_jan');

        verify($expense)->notNull();
        verify($expense->amount)->equals(25000.00);
        verify($expense->title)->equals('Monthly Electricity Bill - January');
        verify($expense->status)->equals('approved');
    }

    /**
     * Test category relationship
     */
    public function testCategoryRelationship()
    {
        $expense = $this->tester->grabFixture('expenses', 'rent_jan');

        verify($expense->expenseCategory)->notNull();
        verify($expense->expenseCategory)->instanceOf(ExpenseCategory::class);
        verify($expense->expenseCategory->name)->equals('Rent');
    }

    /**
     * Test vendor relationship
     */
    public function testVendorRelationship()
    {
        $expense = $this->tester->grabFixture('expenses', 'electricity_jan');

        verify($expense->vendor)->notNull();
        verify($expense->vendor)->instanceOf(Vendor::class);
        verify($expense->vendor->name)->equals('Ceylon Electricity Board');
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
        $expense = $this->tester->grabFixture('expenses', 'foreign_expense');

        // USD expense with exchange rate
        verify($expense->amount)->equals(500.00);
        verify($expense->currency_code)->equals('USD');
        verify($expense->exchange_rate)->equals(330.00);

        // Calculate LKR equivalent
        $lkrAmount = $expense->amount * $expense->exchange_rate;
        verify($lkrAmount)->equals(165000.00);
    }

    /**
     * Test recurring expense flag
     */
    public function testRecurringExpense()
    {
        $recurringExpense = $this->tester->grabFixture('expenses', 'rent_jan');
        $oneTimeExpense = $this->tester->grabFixture('expenses', 'office_supplies_oct');

        verify($recurringExpense->is_recurring)->equals(1);
        verify($oneTimeExpense->is_recurring)->equals(0);
    }

    /**
     * Test creating new expense
     */
    public function testCreateExpense()
    {
        $category = $this->tester->grabFixture('categories', 'office_supplies');
        $vendor = $this->tester->grabFixture('vendors', 'office_supplies_co');

        $expense = new Expense();
        // Detach all behaviors to prevent null user
        $expense->detachBehaviors();

        $expense->expense_category_id = $category->id;
        $expense->vendor_id = $vendor->id;
        $expense->expense_date = '2025-11-18';
        $expense->title = 'Test Expense';
        $expense->description = 'Test description';
        $expense->amount = 10000.00;
        $expense->currency_code = 'LKR';
        $expense->exchange_rate = 1.00;
        $expense->payment_method = 'cash';
        $expense->status = 'pending';
        $expense->created_at = time();
        $expense->updated_at = time();
        $expense->created_by = 1;
        $expense->updated_by = 1;

        verify($expense->save(false))->true();
        verify($expense->id)->notNull();
    }

    /**
     * Test payment method field
     */
    public function testPaymentMethodField()
    {
        $model = new Expense();
        $model->payment_method = 'bank_transfer';

        // Payment method should be a string field
        verify(is_string($model->payment_method))->true();
        verify($model->payment_method)->equals('bank_transfer');
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
}
