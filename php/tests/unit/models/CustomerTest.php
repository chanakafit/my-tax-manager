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
        verify($model)->instanceOf(Customer::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(Customer::tableName())->stringContainsString('customer');
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
     * Test invoices relationship
     */
    public function testInvoicesRelationship()
    {
        $model = new Customer();
        verify(method_exists($model, 'getInvoices'))->true();
    }

    /**
     * Test getStatusList method
     */
    public function testGetStatusList()
    {
        $statusList = Customer::getStatusList();

        verify($statusList)->isArray();
        verify($statusList)->arrayHasKey(Customer::STATUS_ACTIVE);
        verify($statusList)->arrayHasKey(Customer::STATUS_INACTIVE);
        verify($statusList[Customer::STATUS_ACTIVE])->equals('Active');
        verify($statusList[Customer::STATUS_INACTIVE])->equals('Inactive');
    }

    /**
     * Test getStatusText method
     */
    public function testGetStatusText()
    {
        $model = new Customer();
        $model->status = Customer::STATUS_ACTIVE;

        verify($model->getStatusText())->equals('Active');

        $model->status = Customer::STATUS_INACTIVE;
        verify($model->getStatusText())->equals('Inactive');
    }

    /**
     * Test getFullName method
     */
    public function testGetFullName()
    {
        $model = new Customer();
        $model->company_name = 'ABC Corp';

        verify($model->getFullName())->equals('ABC Corp');

        $model->contact_person = 'John Doe';
        verify($model->getFullName())->equals('ABC Corp (John Doe)');
    }

    /**
     * Test getFullName without contact person
     */
    public function testGetFullNameWithoutContactPerson()
    {
        $model = new Customer();
        $model->company_name = 'XYZ Ltd';
        $model->contact_person = null;

        verify($model->getFullName())->equals('XYZ Ltd');
    }

    /**
     * Test createdBy relationship
     */
    public function testCreatedByRelationship()
    {
        $model = new Customer();
        verify(method_exists($model, 'getCreatedBy'))->true();
    }

    /**
     * Test updatedBy relationship
     */
    public function testUpdatedByRelationship()
    {
        $model = new Customer();
        verify(method_exists($model, 'getUpdatedBy'))->true();
    }

    /**
     * Test status constants
     */
    public function testStatusConstants()
    {
        verify(Customer::STATUS_ACTIVE)->equals(10);
        verify(Customer::STATUS_INACTIVE)->equals(0);
    }

    /**
     * Test email validation rule
     */
    public function testEmailValidationRule()
    {
        $model = new Customer();
        $model->email = 'invalid-email';
        $model->validate(['email']);

        verify($model->hasErrors('email'))->true();

        $model->email = 'valid@email.com';
        $model->validate(['email']);
        verify($model->hasErrors('email'))->false();
    }

    /**
     * Test required fields for customer
     */
    public function testRequiredFieldsForCustomer()
    {
        $model = new Customer();
        $model->validate();

        verify($model->hasErrors('company_name'))->true();
        verify($model->hasErrors('email'))->true();
        verify($model->hasErrors('default_currency'))->true();
    }
}
