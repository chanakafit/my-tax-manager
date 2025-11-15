<?php

namespace tests\unit\models;

use app\models\ExpenseSuggestion;
use Codeception\Test\Unit;

/**
 * Test ExpenseSuggestion model business logic
 */
class ExpenseSuggestionTest extends Unit
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
        $model = new ExpenseSuggestion();
        verify($model)->isInstanceOf(ExpenseSuggestion::class);
    }

    /**
     * Test status constants
     */
    public function testStatusConstants()
    {
        verify(ExpenseSuggestion::STATUS_PENDING)->equals('pending');
        verify(ExpenseSuggestion::STATUS_ADDED)->equals('added');
        verify(ExpenseSuggestion::STATUS_IGNORED_TEMPORARY)->equals('ignored_temporary');
        verify(ExpenseSuggestion::STATUS_IGNORED_PERMANENT)->equals('ignored_permanent');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new ExpenseSuggestion();
        $model->validate();
        
        verify($model->hasErrors('expense_category_id'))->true();
        verify($model->hasErrors('vendor_id'))->true();
        verify($model->hasErrors('suggested_month'))->true();
        verify($model->hasErrors('pattern_months'))->true();
        verify($model->hasErrors('generated_at'))->true();
    }

    /**
     * Test pattern months array getter
     */
    public function testGetPatternMonthsArray()
    {
        $model = new ExpenseSuggestion();
        $months = ['2024-01-01', '2024-02-01', '2024-03-01'];
        $model->pattern_months = json_encode($months);
        
        $result = $model->getPatternMonthsArray();
        
        verify($result)->isArray();
        verify(count($result))->equals(3);
        verify($result)->equals($months);
    }

    /**
     * Test pattern months array getter with empty data
     */
    public function testGetPatternMonthsArrayWithEmptyData()
    {
        $model = new ExpenseSuggestion();
        $model->pattern_months = null;
        
        $result = $model->getPatternMonthsArray();
        
        verify($result)->isArray();
        verify($result)->isEmpty();
    }

    /**
     * Test pattern months array setter
     */
    public function testSetPatternMonthsArray()
    {
        $model = new ExpenseSuggestion();
        $months = ['2024-01-01', '2024-02-01'];
        
        $model->setPatternMonthsArray($months);
        
        verify($model->pattern_months)->notNull();
        verify(json_decode($model->pattern_months, true))->equals($months);
    }

    /**
     * Test markAsAdded method
     */
    public function testMarkAsAdded()
    {
        $model = new ExpenseSuggestion();
        $model->expense_category_id = 1;
        $model->vendor_id = 1;
        $model->suggested_month = '2024-01-01';
        $model->pattern_months = json_encode(['2024-01-01']);
        $model->generated_at = time();
        $model->status = ExpenseSuggestion::STATUS_PENDING;
        
        // Test the method changes status
        $userId = 1;
        $model->markAsAdded($userId);
        
        verify($model->status)->equals(ExpenseSuggestion::STATUS_ADDED);
        verify($model->actioned_by)->equals($userId);
        verify($model->actioned_at)->notNull();
    }

    /**
     * Test markAsIgnored with temporary
     */
    public function testMarkAsIgnoredTemporary()
    {
        $model = new ExpenseSuggestion();
        $model->expense_category_id = 1;
        $model->vendor_id = 1;
        $model->suggested_month = '2024-01-01';
        $model->pattern_months = json_encode(['2024-01-01']);
        $model->generated_at = time();
        $model->status = ExpenseSuggestion::STATUS_PENDING;
        
        $userId = 1;
        $reason = 'Not needed this month';
        $model->markAsIgnored('temporary', $reason, $userId);
        
        verify($model->status)->equals(ExpenseSuggestion::STATUS_IGNORED_TEMPORARY);
        verify($model->ignored_reason)->equals($reason);
        verify($model->actioned_by)->equals($userId);
        verify($model->actioned_at)->notNull();
    }

    /**
     * Test markAsIgnored with permanent
     */
    public function testMarkAsIgnoredPermanent()
    {
        $model = new ExpenseSuggestion();
        $model->expense_category_id = 1;
        $model->vendor_id = 1;
        $model->suggested_month = '2024-01-01';
        $model->pattern_months = json_encode(['2024-01-01']);
        $model->generated_at = time();
        $model->status = ExpenseSuggestion::STATUS_PENDING;
        
        $userId = 1;
        $reason = 'No longer applicable';
        $model->markAsIgnored('permanent', $reason, $userId);
        
        verify($model->status)->equals(ExpenseSuggestion::STATUS_IGNORED_PERMANENT);
        verify($model->ignored_reason)->equals($reason);
        verify($model->actioned_by)->equals($userId);
        verify($model->actioned_at)->notNull();
    }

    /**
     * Test status validation
     */
    public function testStatusValidation()
    {
        $model = new ExpenseSuggestion();
        $model->status = 'invalid_status';
        $model->validate(['status']);
        
        verify($model->hasErrors('status'))->true();
    }

    /**
     * Test valid status values
     */
    public function testValidStatusValues()
    {
        $validStatuses = [
            ExpenseSuggestion::STATUS_PENDING,
            ExpenseSuggestion::STATUS_ADDED,
            ExpenseSuggestion::STATUS_IGNORED_TEMPORARY,
            ExpenseSuggestion::STATUS_IGNORED_PERMANENT,
        ];
        
        foreach ($validStatuses as $status) {
            $model = new ExpenseSuggestion();
            $model->status = $status;
            $model->validate(['status']);
            
            verify($model->hasErrors('status'))->false();
        }
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(ExpenseSuggestion::tableName())->contains('expense_suggestion');
    }

    /**
     * Test getStatusLabel returns HTML
     */
    public function testGetStatusLabel()
    {
        $model = new ExpenseSuggestion();
        $model->status = ExpenseSuggestion::STATUS_PENDING;
        
        $label = $model->getStatusLabel();
        
        verify($label)->notEmpty();
        verify($label)->contains('span');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new ExpenseSuggestion();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('expense_category_id', $labels))->true();
        verify(array_key_exists('vendor_id', $labels))->true();
        verify(array_key_exists('suggested_month', $labels))->true();
        verify(array_key_exists('status', $labels))->true();
    }
}
