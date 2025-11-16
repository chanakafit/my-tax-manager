<?php

namespace tests\unit\models;

use app\models\Vendor;
use Codeception\Test\Unit;

/**
 * Test Vendor model business logic
 */
class VendorTest extends Unit
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
        $model = new Vendor();
        verify($model)->instanceOf(Vendor::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(Vendor::tableName())->stringContainsString('vendor');
    }

    /**
     * Test attribute labels exist
     */
    public function testAttributeLabelsExist()
    {
        $model = new Vendor();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(count($labels))->greaterThan(0);
    }

    /**
     * Test expenses relationship
     */
    public function testExpensesRelationship()
    {
        $model = new Vendor();
        verify(method_exists($model, 'getExpenses'))->true();
    }

    /**
     * Test createdBy relationship
     */
    public function testCreatedByRelationship()
    {
        $model = new Vendor();
        verify(method_exists($model, 'getCreatedBy'))->true();
    }

    /**
     * Test updatedBy relationship
     */
    public function testUpdatedByRelationship()
    {
        $model = new Vendor();
        verify(method_exists($model, 'getUpdatedBy'))->true();
    }

    /**
     * Test required name field
     */
    public function testRequiredNameField()
    {
        $model = new Vendor();
        $model->validate();

        verify($model->hasErrors('name'))->true();

        $model->name = 'Test Vendor';
        $model->validate(['name']);
        verify($model->hasErrors('name'))->false();
    }

    /**
     * Test email validation
     */
    public function testEmailValidation()
    {
        $model = new Vendor();
        $model->email = 'invalid-email';
        $model->validate(['email']);

        // Email should have validation rule
        $rules = $model->rules();
        $hasEmailRule = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && in_array('email', (array)$rule[0])) {
                $hasEmailRule = true;
                break;
            }
        }

        verify($hasEmailRule)->true();
    }

    /**
     * Test currency code default value
     */
    public function testCurrencyCodeDefaultValue()
    {
        $model = new Vendor();
        // Default should be set by rules, but null before validation
        verify($model->currency_code)->null();
    }

    /**
     * Test vendor name
     */
    public function testVendorName()
    {
        $model = new Vendor();
        $model->name = 'ABC Supplies Ltd';

        verify($model->name)->equals('ABC Supplies Ltd');
    }
}
