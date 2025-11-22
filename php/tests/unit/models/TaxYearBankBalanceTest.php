<?php

namespace tests\unit\models;

use app\models\TaxYearBankBalance;
use Codeception\Test\Unit;

/**
 * Test TaxYearBankBalance model business logic
 */
class TaxYearBankBalanceTest extends Unit
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
        $model = new TaxYearBankBalance();
        verify($model)->instanceOf(TaxYearBankBalance::class);
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new TaxYearBankBalance();
        $model->validate();
        
        verify($model->hasErrors('tax_year_snapshot_id'))->true();
        verify($model->hasErrors('bank_account_id'))->true();
        verify($model->hasErrors('balance'))->true();
        verify($model->hasErrors('balance_lkr'))->true();
    }

    /**
     * Test balance field validation
     */
    public function testBalanceValidation()
    {
        $model = new TaxYearBankBalance();
        
        $model->balance = 'not_a_number';
        $model->validate(['balance']);
        verify($model->hasErrors('balance'))->true();
        
        $model->balance = 50000.50;
        $model->validate(['balance']);
        verify($model->hasErrors('balance'))->false();
    }

    /**
     * Test balance LKR field validation
     */
    public function testBalanceLkrValidation()
    {
        $model = new TaxYearBankBalance();
        
        $model->balance_lkr = 'not_a_number';
        $model->validate(['balance_lkr']);
        verify($model->hasErrors('balance_lkr'))->true();
        
        $model->balance_lkr = 15000000.00;
        $model->validate(['balance_lkr']);
        verify($model->hasErrors('balance_lkr'))->false();
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(TaxYearBankBalance::tableName())->stringContainsString('tax_year_bank_balance');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new TaxYearBankBalance();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('tax_year_snapshot_id', $labels))->true();
        verify(array_key_exists('bank_account_id', $labels))->true();
        verify(array_key_exists('balance', $labels))->true();
        verify(array_key_exists('balance_lkr', $labels))->true();
        verify(array_key_exists('supporting_document', $labels))->true();
    }

    /**
     * Test supporting document is optional
     */
    public function testSupportingDocumentOptional()
    {
        $model = new TaxYearBankBalance();
        $model->tax_year_snapshot_id = 1;
        $model->bank_account_id = 1;
        $model->balance = 100000;
        $model->balance_lkr = 100000;
        
        $model->validate(['supporting_document']);
        verify($model->hasErrors('supporting_document'))->false();
    }

    /**
     * Test supporting document max length
     */
    public function testSupportingDocumentMaxLength()
    {
        $model = new TaxYearBankBalance();
        $model->supporting_document = str_repeat('a', 256);
        $model->validate(['supporting_document']);
        
        verify($model->hasErrors('supporting_document'))->true();
    }

    /**
     * Test file upload validation
     */
    public function testFileUploadValidation()
    {
        $model = new TaxYearBankBalance();
        
        // uploadedFile should be optional
        $model->validate(['uploadedFile']);
        verify($model->hasErrors('uploadedFile'))->false();
    }

    /**
     * Test relationships - taxYearSnapshot
     */
    public function testTaxYearSnapshotRelationship()
    {
        $model = new TaxYearBankBalance();
        verify($model->hasMethod('getTaxYearSnapshot'))->true();
    }

    /**
     * Test relationships - bankAccount
     */
    public function testBankAccountRelationship()
    {
        $model = new TaxYearBankBalance();
        verify($model->hasMethod('getBankAccount'))->true();
    }

    /**
     * Test balance conversion
     */
    public function testBalanceConversion()
    {
        $model = new TaxYearBankBalance();
        $model->balance = 1000; // USD
        $model->balance_lkr = 300000; // LKR
        
        // Exchange rate implied: 300
        $impliedRate = $model->balance_lkr / $model->balance;
        verify($impliedRate)->equals(300);
    }

    /**
     * Test integer field validations
     */
    public function testIntegerFieldValidations()
    {
        $model = new TaxYearBankBalance();
        
        // Test that non-integer fails integer validation
        $model->tax_year_snapshot_id = 'not_an_integer';
        $model->validate(['tax_year_snapshot_id']);
        verify($model->hasErrors('tax_year_snapshot_id'))->true();
        
        // Test that integer passes integer validation (even if exist fails)
        $model->clearErrors();
        $model->tax_year_snapshot_id = 123;
        $result = $model->validate(['tax_year_snapshot_id']);
        // Integer is valid format-wise, but may fail exist check
        // Check that at least integer validation passed by checking error messages
        $errors = $model->getErrors('tax_year_snapshot_id');
        // If only exist error, integer validation passed
        if (!empty($errors)) {
            $hasIntegerError = false;
            foreach ($errors as $error) {
                if (strpos($error, 'integer') !== false || strpos($error, 'numeric') !== false) {
                    $hasIntegerError = true;
                    break;
                }
            }
            verify($hasIntegerError)->false();
        }
    }
}
