<?php

namespace tests\unit\models;

use app\models\ExpenseCategory;
use Codeception\Test\Unit;

/**
 * Test ExpenseCategory model business logic
 */
class ExpenseCategoryTest extends Unit
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
        $model = new ExpenseCategory();
        verify($model)->instanceOf(ExpenseCategory::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(ExpenseCategory::tableName())->stringContainsString('expense_category');
    }

    /**
     * Test attribute labels exist
     */
    public function testAttributeLabelsExist()
    {
        $model = new ExpenseCategory();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(count($labels))->greaterThan(0);
    }

    /**
     * Test relationships - expenses
     */
    public function testExpensesRelationship()
    {
        $model = new ExpenseCategory();
        verify($model->hasMethod('getExpenses'))->true();
    }
}
