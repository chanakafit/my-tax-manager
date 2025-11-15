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
        verify($model)->isInstanceOf(Vendor::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(Vendor::tableName())->contains('vendor');
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
     * Test relationships - expenses
     */
    public function testExpensesRelationship()
    {
        $model = new Vendor();
        verify($model->hasMethod('getExpenses'))->true();
    }
}
