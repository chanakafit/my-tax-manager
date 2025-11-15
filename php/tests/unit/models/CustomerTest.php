<?php

namespace tests\unit\models;

use app\models\Customer;
use Codeception\Test\Unit;

/**
 * Test Customer model business logic
 */
class CustomerTest extends Unit
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
        $model = new Customer();
        verify($model)->isInstanceOf(Customer::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(Customer::tableName())->contains('customer');
    }

    /**
     * Test attribute labels exist
     */
    public function testAttributeLabelsExist()
    {
        $model = new Customer();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(count($labels))->greaterThan(0);
    }

    /**
     * Test relationships - invoices
     */
    public function testInvoicesRelationship()
    {
        $model = new Customer();
        verify($model->hasMethod('getInvoices'))->true();
    }
}
