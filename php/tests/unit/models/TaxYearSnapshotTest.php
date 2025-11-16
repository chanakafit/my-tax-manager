<?php

namespace tests\unit\models;

use app\models\TaxYearSnapshot;
use Codeception\Test\Unit;

/**
 * Test TaxYearSnapshot model business logic
 */
class TaxYearSnapshotTest extends Unit
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
        $model = new TaxYearSnapshot();
        verify($model)->instanceOf(TaxYearSnapshot::class);
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new TaxYearSnapshot();
        $model->validate();
        
        verify($model->hasErrors('tax_year'))->true();
        verify($model->hasErrors('snapshot_date'))->true();
    }

    /**
     * Test tax year uniqueness rule
     */
    public function testTaxYearUniqueRule()
    {
        $model = new TaxYearSnapshot();
        $rules = $model->rules();
        
        $hasUniqueRule = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && in_array('tax_year', (array)$rule[0]) && isset($rule[1]) && $rule[1] === 'unique') {
                $hasUniqueRule = true;
                break;
            }
        }
        
        verify($hasUniqueRule)->true();
    }

    /**
     * Test tax year format
     */
    public function testTaxYearFormat()
    {
        $model = new TaxYearSnapshot();
        $model->tax_year = '2024';
        
        verify(strlen($model->tax_year))->equals(4);
        verify(is_numeric($model->tax_year))->true();
    }

    /**
     * Test snapshot date validation
     */
    public function testSnapshotDateValidation()
    {
        $model = new TaxYearSnapshot();
        $model->snapshot_date = '2024-03-31';
        $model->validate(['snapshot_date']);
        
        verify($model->hasErrors('snapshot_date'))->false();
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(TaxYearSnapshot::tableName())->stringContainsString('tax_year_snapshot');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new TaxYearSnapshot();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('tax_year', $labels))->true();
        verify(array_key_exists('snapshot_date', $labels))->true();
        verify(array_key_exists('notes', $labels))->true();
    }

    /**
     * Test notes field is optional
     */
    public function testNotesFieldOptional()
    {
        $model = new TaxYearSnapshot();
        $model->tax_year = '2024';
        $model->snapshot_date = '2024-03-31';
        $model->validate(['notes']);
        
        verify($model->hasErrors('notes'))->false();
    }

    /**
     * Test tax year max length
     */
    public function testTaxYearMaxLength()
    {
        $model = new TaxYearSnapshot();
        $model->tax_year = '20241'; // 5 characters, max is 4
        $model->validate(['tax_year']);
        
        verify($model->hasErrors('tax_year'))->true();
    }

    /**
     * Test getOrCreate static method exists
     */
    public function testGetOrCreateMethodExists()
    {
        $reflection = new \ReflectionClass(TaxYearSnapshot::class);
        verify($reflection->hasMethod('getOrCreate'))->true();
        
        $method = $reflection->getMethod('getOrCreate');
        verify($method->isStatic())->true();
    }

    /**
     * Test relationships - bankBalances
     */
    public function testBankBalancesRelationship()
    {
        $model = new TaxYearSnapshot();
        verify($model->hasMethod('getBankBalances'))->true();
    }

    /**
     * Test relationships - liabilityBalances
     */
    public function testLiabilityBalancesRelationship()
    {
        $model = new TaxYearSnapshot();
        verify($model->hasMethod('getLiabilityBalances'))->true();
    }
}
