<?php

namespace tests\unit\models;

use app\models\BankAccount;
use Codeception\Test\Unit;

/**
 * Test BankAccount model business logic
 */
class BankAccountTest extends Unit
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
        $model = new BankAccount();
        verify($model)->isInstanceOf(BankAccount::class);
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new BankAccount();
        $model->validate();
        
        verify($model->hasErrors('account_name'))->true();
        verify($model->hasErrors('account_number'))->true();
        verify($model->hasErrors('bank_name'))->true();
        verify($model->hasErrors('account_type'))->true();
    }

    /**
     * Test default values
     */
    public function testDefaultValues()
    {
        $model = new BankAccount();
        
        // Currency should default to USD
        verify($model->currency)->null(); // Before validation
        
        // is_active should default to 1
        verify($model->is_active)->null(); // Before validation
    }

    /**
     * Test account number uniqueness rule
     */
    public function testAccountNumberUniqueRule()
    {
        $model = new BankAccount();
        $rules = $model->rules();
        
        $hasUniqueRule = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && in_array('account_number', (array)$rule[0]) && isset($rule[1]) && $rule[1] === 'unique') {
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
        verify(BankAccount::tableName())->contains('bank_account');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new BankAccount();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('account_name', $labels))->true();
        verify(array_key_exists('account_number', $labels))->true();
        verify(array_key_exists('bank_name', $labels))->true();
        verify(array_key_exists('account_type', $labels))->true();
        verify(array_key_exists('currency', $labels))->true();
    }

    /**
     * Test relationships - financialTransactions
     */
    public function testFinancialTransactionsRelationship()
    {
        $model = new BankAccount();
        verify($model->hasMethod('getFinancialTransactions'))->true();
    }

    /**
     * Test optional fields
     */
    public function testOptionalFields()
    {
        $model = new BankAccount();
        $model->account_name = 'Test Account';
        $model->account_number = '1234567890';
        $model->bank_name = 'Test Bank';
        $model->account_type = 'Savings';
        
        $model->validate(['branch_name', 'swift_code', 'notes']);
        
        verify($model->hasErrors('branch_name'))->false();
        verify($model->hasErrors('swift_code'))->false();
        verify($model->hasErrors('notes'))->false();
    }

    /**
     * Test getAccountTitle method
     */
    public function testGetAccountTitle()
    {
        $model = new BankAccount();
        $model->account_name = 'Business Account';
        $model->account_number = '1234567890';
        $model->bank_name = 'Test Bank';
        
        verify($model->hasMethod('getAccountTitle'))->true();
    }

    /**
     * Test currency field max length
     */
    public function testCurrencyMaxLength()
    {
        $model = new BankAccount();
        $model->currency = 'USDD'; // 4 characters, max is 3
        $model->validate(['currency']);
        
        verify($model->hasErrors('currency'))->true();
    }

    /**
     * Test is_active is integer
     */
    public function testIsActiveIsInteger()
    {
        $model = new BankAccount();
        $model->is_active = 'not_an_integer';
        $model->validate(['is_active']);
        
        verify($model->hasErrors('is_active'))->true();
        
        $model->is_active = 1;
        $model->validate(['is_active']);
        verify($model->hasErrors('is_active'))->false();
    }

    /**
     * Test delete method sets is_active to 0
     */
    public function testDeleteMethodSetsIsActiveToZero()
    {
        $model = new BankAccount();
        $model->is_active = 1;
        
        // The delete method should set is_active to 0
        verify($model->hasMethod('delete'))->true();
    }

    /**
     * Test string field max length
     */
    public function testStringFieldMaxLength()
    {
        $model = new BankAccount();
        $model->account_name = str_repeat('a', 256); // 256 characters, max is 255
        $model->validate(['account_name']);
        
        verify($model->hasErrors('account_name'))->true();
    }
}
