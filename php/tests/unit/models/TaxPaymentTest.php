<?php

namespace tests\unit\models;

use app\models\TaxPayment;
use Codeception\Test\Unit;

/**
 * Test TaxPayment model
 */
class TaxPaymentTest extends Unit
{
    protected $tester;

    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        $model = new TaxPayment();
        verify($model)->instanceOf(TaxPayment::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(TaxPayment::tableName())->equals('{{%tax_payment}}');
    }

    /**
     * Test payment type constants
     */
    public function testPaymentTypeConstants()
    {
        verify(TaxPayment::TYPE_QUARTERLY)->equals('quarterly');
        verify(TaxPayment::TYPE_FINAL)->equals('final');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new TaxPayment();
        $model->validate();

        verify($model->hasErrors('tax_year'))->true();
        verify($model->hasErrors('payment_date'))->true();
        verify($model->hasErrors('amount'))->true();
        verify($model->hasErrors('payment_type'))->true();
    }

    /**
     * Test valid quarterly payment
     */
    public function testValidQuarterlyPayment()
    {
        $model = new TaxPayment();
        $model->detachBehaviors();

        $model->tax_year = '2024';
        $model->payment_date = '2024-07-15';
        $model->amount = 50000.00;
        $model->payment_type = TaxPayment::TYPE_QUARTERLY;
        $model->quarter = 1;
        $model->reference_number = 'REF12345';
        $model->notes = 'Q1 payment';
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
    }

    /**
     * Test valid final payment
     */
    public function testValidFinalPayment()
    {
        $model = new TaxPayment();
        $model->detachBehaviors();

        $model->tax_year = '2024';
        $model->payment_date = '2025-03-31';
        $model->amount = 150000.00;
        $model->payment_type = TaxPayment::TYPE_FINAL;
        $model->reference_number = 'FINAL2024';
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
    }

    /**
     * Test quarter required for quarterly payment
     */
    public function testQuarterRequiredForQuarterlyPayment()
    {
        $model = new TaxPayment();
        $model->tax_year = '2024';
        $model->payment_date = '2024-07-15';
        $model->amount = 50000.00;
        $model->payment_type = TaxPayment::TYPE_QUARTERLY;
        // quarter not set

        $model->validate();
        verify($model->hasErrors('quarter'))->true();
    }

    /**
     * Test quarter validation range
     */
    public function testQuarterValidationRange()
    {
        $model = new TaxPayment();
        $model->payment_type = TaxPayment::TYPE_QUARTERLY;

        // Invalid quarter: 0
        $model->quarter = 0;
        $model->validate(['quarter']);
        verify($model->hasErrors('quarter'))->true();

        // Invalid quarter: 5
        $model->quarter = 5;
        $model->validate(['quarter']);
        verify($model->hasErrors('quarter'))->true();

        // Valid quarters: 1-4
        foreach ([1, 2, 3, 4] as $validQuarter) {
            $model->quarter = $validQuarter;
            $model->validate(['quarter']);
            verify($model->hasErrors('quarter'))->false();
        }
    }

    /**
     * Test payment type validation
     */
    public function testPaymentTypeValidation()
    {
        $model = new TaxPayment();

        // Invalid payment type
        $model->payment_type = 'invalid_type';
        $model->validate(['payment_type']);
        verify($model->hasErrors('payment_type'))->true();

        // Valid: quarterly
        $model->payment_type = TaxPayment::TYPE_QUARTERLY;
        $model->validate(['payment_type']);
        verify($model->hasErrors('payment_type'))->false();

        // Valid: final
        $model->payment_type = TaxPayment::TYPE_FINAL;
        $model->validate(['payment_type']);
        verify($model->hasErrors('payment_type'))->false();
    }

    /**
     * Test amount must be numeric
     */
    public function testAmountMustBeNumeric()
    {
        $model = new TaxPayment();
        $model->amount = 'not_a_number';

        $model->validate(['amount']);
        verify($model->hasErrors('amount'))->true();

        $model->amount = 50000.00;
        $model->validate(['amount']);
        verify($model->hasErrors('amount'))->false();
    }

    /**
     * Test tax year max length
     */
    public function testTaxYearMaxLength()
    {
        $model = new TaxPayment();

        $model->tax_year = '20245'; // 5 chars - exceeds max of 4
        $model->validate(['tax_year']);
        verify($model->hasErrors('tax_year'))->true();

        $model->tax_year = '2024'; // 4 chars - valid
        $model->validate(['tax_year']);
        verify($model->hasErrors('tax_year'))->false();
    }

    /**
     * Test reference number max length
     */
    public function testReferenceNumberMaxLength()
    {
        $model = new TaxPayment();

        $model->reference_number = str_repeat('a', 256); // 256 chars - exceeds max
        $model->validate(['reference_number']);
        verify($model->hasErrors('reference_number'))->true();

        $model->reference_number = str_repeat('a', 255); // 255 chars - valid
        $model->validate(['reference_number']);
        verify($model->hasErrors('reference_number'))->false();
    }

    /**
     * Test notes field accepts long text
     */
    public function testNotesAcceptsLongText()
    {
        $model = new TaxPayment();
        $longText = str_repeat('Payment notes. ', 200); // ~3000 chars

        $model->notes = $longText;
        $model->validate(['notes']);

        verify($model->hasErrors('notes'))->false();
    }

    /**
     * Test optional fields
     */
    public function testOptionalFields()
    {
        $model = new TaxPayment();
        $model->detachBehaviors();

        $model->tax_year = '2024';
        $model->payment_date = '2025-03-31';
        $model->amount = 150000.00;
        $model->payment_type = TaxPayment::TYPE_FINAL;
        // Optional fields not set:
        $model->reference_number = null;
        $model->notes = null;
        $model->receipt_file = null;
        $model->quarter = null;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new TaxPayment();
        $labels = $model->attributeLabels();

        verify($labels['tax_year'])->equals('Tax Year');
        verify($labels['payment_date'])->equals('Payment Date');
        verify($labels['amount'])->equals('Amount');
        verify($labels['payment_type'])->equals('Payment Type');
        verify($labels['quarter'])->equals('Quarter');
        verify($labels['reference_number'])->equals('Reference Number');
        verify($labels['notes'])->equals('Notes');
        verify($labels['uploadedFile'])->equals('Receipt');
    }

    /**
     * Test beforeSave sets quarter to 0 for final payment
     */
    public function testBeforeSaveSetsQuarterToZeroForFinalPayment()
    {
        $model = new TaxPayment();
        $model->detachBehaviors();

        $model->tax_year = '2024';
        $model->payment_date = '2025-03-31';
        $model->amount = 150000.00;
        $model->payment_type = TaxPayment::TYPE_FINAL;
        $model->quarter = 3; // Set a quarter value
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->save(false))->true();

        // Quarter should be set to 0 for final payment
        verify($model->quarter)->equals(0);
    }

    /**
     * Test quarter defaults to null
     */
    public function testQuarterDefaultsToNull()
    {
        $model = new TaxPayment();
        verify($model->quarter)->null();
    }

    /**
     * Test negative amount validation
     */
    public function testNegativeAmountValidation()
    {
        $model = new TaxPayment();
        $model->amount = -50000.00;

        // Should be accepted as numeric (business logic may handle negatives differently)
        $model->validate(['amount']);
        verify($model->hasErrors('amount'))->false();
    }

    /**
     * Test decimal amount precision
     */
    public function testDecimalAmountPrecision()
    {
        $model = new TaxPayment();
        $model->detachBehaviors();

        $model->tax_year = '2024';
        $model->payment_date = '2024-07-15';
        $model->amount = 50123.45; // Decimal amount
        $model->payment_type = TaxPayment::TYPE_QUARTERLY;
        $model->quarter = 1;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
        verify($model->amount)->equals(50123.45);
    }
}

