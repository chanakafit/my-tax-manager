<?php

namespace tests\unit\models;

use app\models\TaxRecord;
use Codeception\Test\Unit;

/**
 * Test TaxRecord model
 */
class TaxRecordTest extends Unit
{
    protected $tester;

    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        $model = new TaxRecord();
        verify($model)->instanceOf(TaxRecord::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(TaxRecord::tableName())->equals('{{%tax_record}}');
    }

    /**
     * Test tax type constants
     */
    public function testTaxTypeConstants()
    {
        verify(TaxRecord::TYPE_VAT)->equals('VAT');
        verify(TaxRecord::TYPE_INCOME)->equals('Income Tax');
        verify(TaxRecord::TYPE_PAYROLL)->equals('Payroll Tax');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new TaxRecord();
        $model->validate();

        verify($model->hasErrors('tax_period_start'))->true();
        verify($model->hasErrors('tax_period_end'))->true();
        verify($model->hasErrors('tax_type'))->true();
        verify($model->hasErrors('tax_rate'))->true();
        verify($model->hasErrors('taxable_amount'))->true();
        verify($model->hasErrors('tax_amount'))->true();
    }

    /**
     * Test valid tax record
     */
    public function testValidTaxRecord()
    {
        $model = new TaxRecord();
        $model->detachBehaviors();

        $model->tax_period_start = '2024-04-01';
        $model->tax_period_end = '2025-03-31';
        $model->tax_type = TaxRecord::TYPE_INCOME;
        $model->tax_rate = 24.0;
        $model->taxable_amount = 1000000.00;
        $model->tax_amount = 240000.00;
        $model->payment_status = 'pending';
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
    }

    /**
     * Test tax type validation
     */
    public function testTaxTypeValidation()
    {
        $model = new TaxRecord();

        // Invalid tax type
        $model->tax_type = 'Invalid Type';
        $model->validate(['tax_type']);
        verify($model->hasErrors('tax_type'))->true();

        // Valid: VAT
        $model->tax_type = TaxRecord::TYPE_VAT;
        $model->validate(['tax_type']);
        verify($model->hasErrors('tax_type'))->false();

        // Valid: Income Tax
        $model->tax_type = TaxRecord::TYPE_INCOME;
        $model->validate(['tax_type']);
        verify($model->hasErrors('tax_type'))->false();

        // Valid: Payroll Tax
        $model->tax_type = TaxRecord::TYPE_PAYROLL;
        $model->validate(['tax_type']);
        verify($model->hasErrors('tax_type'))->false();
    }

    /**
     * Test payment status validation
     */
    public function testPaymentStatusValidation()
    {
        $model = new TaxRecord();

        // Invalid status
        $model->payment_status = 'invalid_status';
        $model->validate(['payment_status']);
        verify($model->hasErrors('payment_status'))->true();

        // Valid: pending
        $model->payment_status = 'pending';
        $model->validate(['payment_status']);
        verify($model->hasErrors('payment_status'))->false();

        // Valid: paid
        $model->payment_status = 'paid';
        $model->validate(['payment_status']);
        verify($model->hasErrors('payment_status'))->false();
    }

    /**
     * Test payment status defaults to pending
     */
    public function testPaymentStatusDefaultsToPending()
    {
        $model = new TaxRecord();
        $model->detachBehaviors();

        $model->tax_period_start = '2020-04-01';
        $model->tax_period_end = '2021-03-31';
        $model->tax_type = TaxRecord::TYPE_INCOME;
        $model->tax_rate = 24.0;
        $model->taxable_amount = 1000000.00;
        $model->tax_amount = 240000.00;
        $model->tax_code = 'TP' . substr(time(), -8);
        // payment_status not set
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->payment_status)->equals('pending');
    }

    /**
     * Test numeric fields
     */
    public function testNumericFields()
    {
        $model = new TaxRecord();

        // Tax rate must be numeric
        $model->tax_rate = 'not_numeric';
        $model->validate(['tax_rate']);
        verify($model->hasErrors('tax_rate'))->true();

        $model->tax_rate = 24.5;
        $model->validate(['tax_rate']);
        verify($model->hasErrors('tax_rate'))->false();

        // Taxable amount must be numeric
        $model->taxable_amount = 'not_numeric';
        $model->validate(['taxable_amount']);
        verify($model->hasErrors('taxable_amount'))->true();

        $model->taxable_amount = 1000000.00;
        $model->validate(['taxable_amount']);
        verify($model->hasErrors('taxable_amount'))->false();

        // Tax amount must be numeric
        $model->tax_amount = 'not_numeric';
        $model->validate(['tax_amount']);
        verify($model->hasErrors('tax_amount'))->true();

        $model->tax_amount = 240000.00;
        $model->validate(['tax_amount']);
        verify($model->hasErrors('tax_amount'))->false();
    }

    /**
     * Test optional fields
     */
    public function testOptionalFields()
    {
        $model = new TaxRecord();
        $model->detachBehaviors();

        $model->tax_period_start = '2024-04-01';
        $model->tax_period_end = '2025-03-31';
        $model->tax_type = TaxRecord::TYPE_VAT;
        $model->tax_rate = 8.0;
        $model->taxable_amount = 500000.00;
        $model->tax_amount = 40000.00;
        $model->payment_status = 'pending';
        // Optional fields:
        $model->tax_code = null;
        $model->ird_ref = null;
        $model->payment_date = null;
        $model->reference_number = null;
        $model->notes = null;
        $model->related_invoice_ids = null;
        $model->related_expense_ids = null;
        $model->related_paysheet_ids = null;
        $model->total_income = null;
        $model->total_expenses = null;
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
        $model = new TaxRecord();
        $labels = $model->attributeLabels();

        verify($labels['tax_period_start'])->equals('Tax Period Start');
        verify($labels['tax_period_end'])->equals('Tax Period End');
        verify($labels['tax_type'])->equals('Tax Type');
        verify($labels['tax_rate'])->equals('Tax Rate (%)');
        verify($labels['taxable_amount'])->equals('Taxable Amount');
        verify($labels['tax_amount'])->equals('Tax Amount');
        verify($labels['payment_status'])->equals('Payment Status');
    }

    /**
     * Test with related IDs stored as JSON
     */
    public function testRelatedIdsAsJson()
    {
        $model = new TaxRecord();
        $model->detachBehaviors();

        $model->tax_period_start = '2021-04-01';
        $model->tax_period_end = '2022-03-31';
        $model->tax_type = TaxRecord::TYPE_INCOME;
        $model->tax_rate = 24.0;
        $model->taxable_amount = 1000000.00;
        $model->tax_amount = 240000.00;
        $model->payment_status = 'pending';
        $model->tax_code = 'TJ' . substr(time(), -8);
        $model->related_invoice_ids = json_encode([1, 2, 3]);
        $model->related_expense_ids = json_encode([10, 20, 30]);
        $model->related_paysheet_ids = json_encode([100, 200]);
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
    }

    /**
     * Test total income and expenses
     */
    public function testTotalIncomeAndExpenses()
    {
        $model = new TaxRecord();
        $model->detachBehaviors();

        $model->tax_period_start = '2022-04-01';
        $model->tax_period_end = '2023-03-31';
        $model->tax_type = TaxRecord::TYPE_INCOME;
        $model->tax_rate = 24.0;
        $model->taxable_amount = 1000000.00;
        $model->tax_amount = 240000.00;
        $model->payment_status = 'paid';
        $model->tax_code = 'TI' . substr(time(), -8);
        $model->total_income = 1500000.00;
        $model->total_expenses = 500000.00;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
        verify($model->total_income)->equals(1500000.00);
        verify($model->total_expenses)->equals(500000.00);
    }

    /**
     * Test VAT tax record
     */
    public function testVATTaxRecord()
    {
        $model = new TaxRecord();
        $model->detachBehaviors();

        $model->tax_period_start = '2019-10-01';
        $model->tax_period_end = '2019-12-31';
        $model->tax_type = TaxRecord::TYPE_VAT;
        $model->tax_rate = 8.0;
        $model->taxable_amount = 250000.00;
        $model->tax_amount = 20000.00;
        $model->payment_status = 'paid';
        $model->payment_date = '2020-01-15';
        $model->reference_number = 'VAT-Q4-2019';
        $model->tax_code = 'TV' . substr(time(), -8);
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
    }

    /**
     * Test payroll tax record
     */
    public function testPayrollTaxRecord()
    {
        $model = new TaxRecord();
        $model->detachBehaviors();

        $model->tax_period_start = '2018-04-01';
        $model->tax_period_end = '2019-03-31';
        $model->tax_type = TaxRecord::TYPE_PAYROLL;
        $model->tax_rate = 14.0;
        $model->taxable_amount = 2000000.00;
        $model->tax_amount = 280000.00;
        $model->payment_status = 'pending';
        $model->notes = 'Annual payroll tax';
        $model->tax_code = 'TP' . substr(time(), -8);
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
    }

    /**
     * Test string field max lengths
     */
    public function testStringFieldMaxLengths()
    {
        $model = new TaxRecord();

        // tax_type max 255
        $model->tax_type = str_repeat('a', 256);
        $model->validate(['tax_type']);
        verify($model->hasErrors('tax_type'))->true();

        // reference_number max 255
        $model->reference_number = str_repeat('a', 256);
        $model->validate(['reference_number']);
        verify($model->hasErrors('reference_number'))->true();

        // tax_code max 255
        $model->tax_code = str_repeat('a', 256);
        $model->validate(['tax_code']);
        verify($model->hasErrors('tax_code'))->true();
    }
}

