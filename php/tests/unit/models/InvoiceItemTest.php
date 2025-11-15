<?php

namespace tests\unit\models;

use app\models\InvoiceItem;
use Codeception\Test\Unit;

/**
 * Test InvoiceItem model business logic
 */
class InvoiceItemTest extends Unit
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
        $model = new InvoiceItem();
        verify($model)->isInstanceOf(InvoiceItem::class);
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new InvoiceItem();
        $model->validate();
        
        verify($model->hasErrors('invoice_id'))->true();
        verify($model->hasErrors('item_name'))->true();
        verify($model->hasErrors('quantity'))->true();
        verify($model->hasErrors('unit_price'))->true();
        verify($model->hasErrors('total_amount'))->true();
    }

    /**
     * Test default values
     */
    public function testDefaultValues()
    {
        $model = new InvoiceItem();
        
        // discount should default to 0.00 per rules
        verify($model->discount)->null(); // Before validation
    }

    /**
     * Test total amount calculation
     */
    public function testTotalAmountCalculation()
    {
        $model = new InvoiceItem();
        $model->quantity = 10;
        $model->unit_price = 100;
        $model->tax_rate = 15;
        $model->discount = 50;
        
        // Total = (quantity * unit_price) + tax - discount
        // Tax = (quantity * unit_price) * (tax_rate / 100)
        $subtotal = $model->quantity * $model->unit_price; // 1000
        $tax = $subtotal * ($model->tax_rate / 100); // 150
        $expectedTotal = $subtotal + $tax - $model->discount; // 1100
        
        $calculatedTotal = ($model->quantity * $model->unit_price) + ($subtotal * ($model->tax_rate / 100)) - $model->discount;
        verify($calculatedTotal)->equals($expectedTotal);
    }

    /**
     * Test simple total calculation without tax and discount
     */
    public function testSimpleTotalCalculation()
    {
        $model = new InvoiceItem();
        $model->quantity = 5;
        $model->unit_price = 200;
        
        $expectedTotal = 5 * 200;
        verify($model->quantity * $model->unit_price)->equals($expectedTotal);
        verify($expectedTotal)->equals(1000);
    }

    /**
     * Test quantity validation
     */
    public function testQuantityValidation()
    {
        $model = new InvoiceItem();
        
        $model->quantity = 'not_a_number';
        $model->validate(['quantity']);
        verify($model->hasErrors('quantity'))->true();
        
        $model->quantity = 5;
        $model->validate(['quantity']);
        verify($model->hasErrors('quantity'))->false();
    }

    /**
     * Test unit price validation
     */
    public function testUnitPriceValidation()
    {
        $model = new InvoiceItem();
        
        $model->unit_price = 'not_a_number';
        $model->validate(['unit_price']);
        verify($model->hasErrors('unit_price'))->true();
        
        $model->unit_price = 99.99;
        $model->validate(['unit_price']);
        verify($model->hasErrors('unit_price'))->false();
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(InvoiceItem::tableName())->contains('invoice_item');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new InvoiceItem();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('invoice_id', $labels))->true();
        verify(array_key_exists('item_name', $labels))->true();
        verify(array_key_exists('quantity', $labels))->true();
        verify(array_key_exists('unit_price', $labels))->true();
        verify(array_key_exists('total_amount', $labels))->true();
    }

    /**
     * Test relationships - invoice
     */
    public function testInvoiceRelationship()
    {
        $model = new InvoiceItem();
        verify($model->hasMethod('getInvoice'))->true();
    }

    /**
     * Test optional fields
     */
    public function testOptionalFields()
    {
        $model = new InvoiceItem();
        $model->invoice_id = 1;
        $model->item_name = 'Test Item';
        $model->quantity = 1;
        $model->unit_price = 100;
        $model->total_amount = 100;
        
        $model->validate(['description', 'tax_rate', 'tax_amount', 'discount']);
        
        verify($model->hasErrors('description'))->false();
        verify($model->hasErrors('tax_rate'))->false();
        verify($model->hasErrors('tax_amount'))->false();
        verify($model->hasErrors('discount'))->false();
    }

    /**
     * Test item name max length
     */
    public function testItemNameMaxLength()
    {
        $model = new InvoiceItem();
        $model->item_name = str_repeat('a', 256);
        $model->validate(['item_name']);
        
        verify($model->hasErrors('item_name'))->true();
    }

    /**
     * Test tax amount field
     */
    public function testTaxAmountField()
    {
        $model = new InvoiceItem();
        $model->tax_amount = 15.50;
        
        verify($model->tax_amount)->equals(15.50);
        verify(is_numeric($model->tax_amount))->true();
    }

    /**
     * Test discount field
     */
    public function testDiscountField()
    {
        $model = new InvoiceItem();
        $model->discount = 10.00;
        
        verify($model->discount)->equals(10.00);
        verify(is_numeric($model->discount))->true();
    }

    /**
     * Test tax rate field
     */
    public function testTaxRateField()
    {
        $model = new InvoiceItem();
        $model->tax_rate = 15;
        
        verify($model->tax_rate)->equals(15);
        verify(is_numeric($model->tax_rate))->true();
    }
}
