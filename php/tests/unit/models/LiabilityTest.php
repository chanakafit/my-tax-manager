<?php

namespace tests\unit\models;

use app\models\Liability;
use Codeception\Test\Unit;

/**
 * Test Liability model
 */
class LiabilityTest extends Unit
{
    protected $tester;

    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        $model = new Liability();
        verify($model)->instanceOf(Liability::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(Liability::tableName())->equals('{{%liability}}');
    }

    /**
     * Test status constants
     */
    public function testStatusConstants()
    {
        verify(Liability::STATUS_ACTIVE)->equals('active');
        verify(Liability::STATUS_SETTLED)->equals('settled');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new Liability();
        $model->validate();

        verify($model->hasErrors('liability_type'))->true();
        verify($model->hasErrors('liability_category'))->true();
        verify($model->hasErrors('lender_name'))->true();
        verify($model->hasErrors('original_amount'))->true();
        verify($model->hasErrors('start_date'))->true();
    }

    /**
     * Test valid active liability
     */
    public function testValidActiveLiability()
    {
        $model = new Liability();
        $model->detachBehaviors();

        $model->liability_type = 'business';
        $model->liability_category = 'loan';
        $model->lender_name = 'Commercial Bank';
        $model->description = 'Business loan from bank';
        $model->original_amount = 5000000.00;
        $model->start_date = '2024-01-01';
        $model->status = Liability::STATUS_ACTIVE;
        $model->interest_rate = 12.5;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
    }

    /**
     * Test valid settled liability
     */
    public function testValidSettledLiability()
    {
        $model = new Liability();
        $model->detachBehaviors();

        $model->liability_type = 'personal';
        $model->liability_category = 'loan';
        $model->lender_name = 'Personal Bank';
        $model->description = 'Personal loan';
        $model->original_amount = 1000000.00;
        $model->start_date = '2023-01-01';
        $model->status = Liability::STATUS_SETTLED;
        $model->settlement_date = '2024-12-31';
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
    }

    /**
     * Test liability type validation
     */
    public function testLiabilityTypeValidation()
    {
        $model = new Liability();

        // Valid: business
        $model->liability_type = 'business';
        $model->validate(['liability_type']);
        verify($model->hasErrors('liability_type'))->false();

        // Valid: personal
        $model->liability_type = 'personal';
        $model->validate(['liability_type']);
        verify($model->hasErrors('liability_type'))->false();
    }

    /**
     * Test status validation
     */
    public function testStatusValidation()
    {
        $model = new Liability();

        // Invalid status
        $model->status = 'invalid_status';
        $model->validate(['status']);
        verify($model->hasErrors('status'))->true();

        // Valid: active
        $model->status = Liability::STATUS_ACTIVE;
        $model->validate(['status']);
        verify($model->hasErrors('status'))->false();

        // Valid: settled
        $model->status = Liability::STATUS_SETTLED;
        $model->validate(['status']);
        verify($model->hasErrors('status'))->false();
    }

    /**
     * Test status defaults to active
     */
    public function testStatusDefaultsToActive()
    {
        $model = new Liability();
        $model->detachBehaviors();

        $model->liability_type = 'business';
        $model->liability_category = 'loan';
        $model->lender_name = 'Test Bank';
        $model->description = 'Test liability';
        $model->original_amount = 1000000.00;
        $model->start_date = '2024-01-01';
        // status not set
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->status)->equals(Liability::STATUS_ACTIVE);
    }

    /**
     * Test numeric fields
     */
    public function testNumericFields()
    {
        $model = new Liability();

        // Original amount must be numeric
        $model->original_amount = 'not_numeric';
        $model->validate(['original_amount']);
        verify($model->hasErrors('original_amount'))->true();

        $model->original_amount = 5000000.00;
        $model->validate(['original_amount']);
        verify($model->hasErrors('original_amount'))->false();

        // Interest rate must be numeric
        $model->interest_rate = 'not_numeric';
        $model->validate(['interest_rate']);
        verify($model->hasErrors('interest_rate'))->true();

        $model->interest_rate = 12.5;
        $model->validate(['interest_rate']);
        verify($model->hasErrors('interest_rate'))->false();
    }

    /**
     * Test optional fields
     */
    public function testOptionalFields()
    {
        $model = new Liability();
        $model->detachBehaviors();

        $model->liability_type = 'business';
        $model->liability_category = 'loan';
        $model->lender_name = 'Test Bank';
        $model->description = 'Minimal liability';
        $model->original_amount = 1000000.00;
        $model->start_date = '2024-01-01';
        $model->status = Liability::STATUS_ACTIVE;
        // Optional fields:
        $model->interest_rate = null;
        $model->settlement_date = null;
        $model->notes = null;
        $model->monthly_payment = null;
        $model->end_date = null;
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
        $model = new Liability();
        $labels = $model->attributeLabels();

        verify($labels['liability_type'])->equals('Liability Type');
        verify($labels['liability_category'])->equals('Category');
        verify($labels['lender_name'])->equals('Lender Name');
        verify($labels['description'])->equals('Description');
        verify($labels['original_amount'])->equals('Original Amount');
        verify($labels['start_date'])->equals('Start Date');
        verify($labels['status'])->equals('Status');
        verify($labels['interest_rate'])->equals('Interest Rate (%)');
        verify($labels['settlement_date'])->equals('Settlement Date');
    }

    /**
     * Test string field max lengths
     */
    public function testStringFieldMaxLengths()
    {
        $model = new Liability();

        // liability_type max 50 - test that exceeding fails
        $model->liability_type = str_repeat('a', 51);
        $model->validate(['liability_type']);
        verify($model->hasErrors('liability_type'))->true();

        // Valid liability_type (must be in allowed range and under max length)
        $model->clearErrors();
        $model->liability_type = 'business'; // Valid value
        $model->validate(['liability_type']);
        verify($model->hasErrors('liability_type'))->false();

        // liability_category max 50 - test that exceeding fails
        $model->clearErrors();
        $model->liability_category = str_repeat('a', 51);
        $model->validate(['liability_category']);
        verify($model->hasErrors('liability_category'))->true();

        // Valid liability_category (must be in allowed range and under max length)
        $model->clearErrors();
        $model->liability_category = 'loan'; // Valid value
        $model->validate(['liability_category']);
        verify($model->hasErrors('liability_category'))->false();

        // lender_name max 255 - test that exceeding fails
        $model->clearErrors();
        $model->lender_name = str_repeat('a', 256);
        $model->validate(['lender_name']);
        verify($model->hasErrors('lender_name'))->true();

        // Valid lender_name (no enum restriction, just max length)
        $model->clearErrors();
        $model->lender_name = str_repeat('a', 255);
        $model->validate(['lender_name']);
        verify($model->hasErrors('lender_name'))->false();
    }

    /**
     * Test notes field accepts long text
     */
    public function testNotesAcceptsLongText()
    {
        $model = new Liability();
        $longText = str_repeat('Liability notes. ', 200); // ~3400 chars

        $model->notes = $longText;
        $model->validate(['notes']);

        verify($model->hasErrors('notes'))->false();
    }

    /**
     * Test business liability
     */
    public function testBusinessLiability()
    {
        $model = new Liability();
        $model->detachBehaviors();

        $model->liability_type = 'business';
        $model->liability_category = 'loan';
        $model->lender_name = 'International Bank';
        $model->description = 'USD loan';
        $model->original_amount = 50000.00;
        $model->start_date = '2024-01-01';
        $model->status = Liability::STATUS_ACTIVE;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
        verify($model->liability_type)->equals('business');
    }

    /**
     * Test liability with interest rate
     */
    public function testLiabilityWithInterestRate()
    {
        $model = new Liability();
        $model->detachBehaviors();

        $model->liability_type = 'business';
        $model->liability_category = 'loan';
        $model->lender_name = 'Bank of Ceylon';
        $model->description = 'Bank loan with interest';
        $model->original_amount = 10000000.00;
        $model->start_date = '2024-01-01';
        $model->status = Liability::STATUS_ACTIVE;
        $model->interest_rate = 15.75;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
        verify($model->interest_rate)->equals(15.75);
    }

    /**
     * Test settled liability with settlement date
     */
    public function testSettledLiabilityWithSettlementDate()
    {
        $model = new Liability();
        $model->detachBehaviors();

        $model->liability_type = 'personal';
        $model->liability_category = 'loan';
        $model->lender_name = 'Personal Bank';
        $model->description = 'Settled personal loan';
        $model->original_amount = 500000.00;
        $model->start_date = '2023-06-01';
        $model->status = Liability::STATUS_SETTLED;
        $model->settlement_date = '2024-11-30';
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
    }
}

