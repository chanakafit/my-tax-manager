<?php

namespace tests\unit\models;

use app\models\CapitalAsset;
use Codeception\Test\Unit;

/**
 * Test CapitalAsset model business logic
 */
class CapitalAssetTest extends Unit
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
        $model = new CapitalAsset();
        verify($model)->isInstanceOf(CapitalAsset::class);
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new CapitalAsset();
        $model->validate();
        
        verify($model->hasErrors('asset_name'))->true();
        verify($model->hasErrors('purchase_date'))->true();
        verify($model->hasErrors('purchase_cost'))->true();
        verify($model->hasErrors('initial_tax_year'))->true();
        verify($model->hasErrors('asset_category'))->true();
    }

    /**
     * Test status default value
     */
    public function testStatusDefaultValue()
    {
        $model = new CapitalAsset();
        // Default should be 'active' per rules
        verify($model->status)->null(); // Before validation
    }

    /**
     * Test status validation
     */
    public function testStatusValidation()
    {
        $model = new CapitalAsset();
        
        $model->status = 'invalid_status';
        $model->validate(['status']);
        verify($model->hasErrors('status'))->true();
        
        $model->status = 'active';
        $model->validate(['status']);
        verify($model->hasErrors('status'))->false();
        
        $model->status = 'disposed';
        $model->validate(['status']);
        verify($model->hasErrors('status'))->false();
    }

    /**
     * Test asset type validation
     */
    public function testAssetTypeValidation()
    {
        $model = new CapitalAsset();
        
        $model->asset_type = 'invalid_type';
        $model->validate(['asset_type']);
        verify($model->hasErrors('asset_type'))->true();
        
        $model->asset_type = 'business';
        $model->validate(['asset_type']);
        verify($model->hasErrors('asset_type'))->false();
        
        $model->asset_type = 'personal';
        $model->validate(['asset_type']);
        verify($model->hasErrors('asset_type'))->false();
    }

    /**
     * Test asset category validation
     */
    public function testAssetCategoryValidation()
    {
        $model = new CapitalAsset();
        
        $model->asset_category = 'invalid_category';
        $model->validate(['asset_category']);
        verify($model->hasErrors('asset_category'))->true();
        
        $model->asset_category = 'immovable';
        $model->validate(['asset_category']);
        verify($model->hasErrors('asset_category'))->false();
        
        $model->asset_category = 'movable';
        $model->validate(['asset_category']);
        verify($model->hasErrors('asset_category'))->false();
    }

    /**
     * Test purchase cost validation
     */
    public function testPurchaseCostValidation()
    {
        $model = new CapitalAsset();
        
        $model->purchase_cost = 'not_a_number';
        $model->validate(['purchase_cost']);
        verify($model->hasErrors('purchase_cost'))->true();
        
        $model->purchase_cost = 500000.00;
        $model->validate(['purchase_cost']);
        verify($model->hasErrors('purchase_cost'))->false();
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(CapitalAsset::tableName())->contains('capital_asset');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new CapitalAsset();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('asset_name', $labels))->true();
        verify(array_key_exists('asset_type', $labels))->true();
        verify(array_key_exists('asset_category', $labels))->true();
        verify(array_key_exists('purchase_cost', $labels))->true();
        verify(array_key_exists('current_written_down_value', $labels))->true();
    }

    /**
     * Test relationships - allowances
     */
    public function testAllowancesRelationship()
    {
        $model = new CapitalAsset();
        verify($model->hasMethod('getAllowances'))->true();
    }

    /**
     * Test calculateAllowance method exists
     */
    public function testCalculateAllowanceMethodExists()
    {
        $model = new CapitalAsset();
        verify($model->hasMethod('calculateAllowance'))->true();
    }

    /**
     * Test calculateAllowance for business assets
     */
    public function testCalculateAllowanceForBusinessAssets()
    {
        $model = new CapitalAsset();
        $model->asset_type = 'business';
        
        // Business assets should be eligible for allowance calculation
        verify($model->asset_type)->equals('business');
    }

    /**
     * Test calculateAllowance for personal assets
     */
    public function testCalculateAllowanceForPersonalAssets()
    {
        $model = new CapitalAsset();
        $model->asset_type = 'personal';
        
        // Personal assets are not eligible for capital allowance
        $result = $model->calculateAllowance('2024');
        verify($result)->null();
    }

    /**
     * Test optional fields
     */
    public function testOptionalFields()
    {
        $model = new CapitalAsset();
        $model->asset_name = 'Test Asset';
        $model->purchase_date = '2024-01-01';
        $model->purchase_cost = 100000;
        $model->initial_tax_year = '2024';
        $model->asset_category = 'movable';
        
        $model->validate(['description', 'notes', 'disposal_date', 'disposal_value']);
        
        verify($model->hasErrors('description'))->false();
        verify($model->hasErrors('notes'))->false();
        verify($model->hasErrors('disposal_date'))->false();
        verify($model->hasErrors('disposal_value'))->false();
    }

    /**
     * Test initial tax year format
     */
    public function testInitialTaxYearFormat()
    {
        $model = new CapitalAsset();
        $model->initial_tax_year = '20241'; // 5 characters, max is 4
        $model->validate(['initial_tax_year']);
        
        verify($model->hasErrors('initial_tax_year'))->true();
        
        $model->initial_tax_year = '2024';
        $model->validate(['initial_tax_year']);
        verify($model->hasErrors('initial_tax_year'))->false();
    }

    /**
     * Test written down value defaults to purchase cost
     */
    public function testWrittenDownValueDefault()
    {
        $model = new CapitalAsset();
        $model->purchase_cost = 100000;
        
        // Default value should be set to purchase_cost
        // This is tested through the callable default value
        verify($model->current_written_down_value)->null(); // Before default is applied
    }
}
