<?php

namespace tests\unit\models;

use app\models\PaysheetSuggestion;
use Codeception\Test\Unit;

/**
 * Test PaysheetSuggestion model business logic
 */
class PaysheetSuggestionTest extends Unit
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
        $model = new PaysheetSuggestion();
        verify($model)->instanceOf(PaysheetSuggestion::class);
    }

    /**
     * Test status constants
     */
    public function testStatusConstants()
    {
        verify(PaysheetSuggestion::STATUS_PENDING)->equals('pending');
        verify(PaysheetSuggestion::STATUS_APPROVED)->equals('approved');
        verify(PaysheetSuggestion::STATUS_REJECTED)->equals('rejected');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new PaysheetSuggestion();
        $model->validate();
        
        verify($model->hasErrors('employee_id'))->true();
        verify($model->hasErrors('suggested_month'))->true();
        verify($model->hasErrors('basic_salary'))->true();
        verify($model->hasErrors('net_salary'))->true();
        verify($model->hasErrors('generated_at'))->true();
    }

    /**
     * Test net salary calculation
     */
    public function testNetSalaryCalculation()
    {
        $model = new PaysheetSuggestion();
        $model->basic_salary = 100000;
        $model->allowances = 10000;
        $model->deductions = 5000;
        $model->tax_amount = 2000;
        
        $expectedNet = 100000 + 10000 - 5000 - 2000;
        
        verify($model->basic_salary + $model->allowances - $model->deductions - $model->tax_amount)->equals($expectedNet);
    }

    /**
     * Test reject method
     */
    public function testReject()
    {
        // Mock user login for BlameableBehavior
        $user = new \app\models\User();
        $user->id = 1;
        \Yii::$app->user->login($user);

        $model = new PaysheetSuggestion();
        $model->employee_id = 1;
        $model->suggested_month = '2024-01-01';
        $model->basic_salary = 50000;
        $model->net_salary = 50000;
        $model->generated_at = time();
        $model->status = PaysheetSuggestion::STATUS_PENDING;
        $model->created_by = 1;
        $model->updated_by = 1;
        $model->created_at = time();
        $model->updated_at = time();

        $userId = 1;
        $reason = 'Employee on leave';
        $model->reject($userId, $reason);
        
        verify($model->status)->equals(PaysheetSuggestion::STATUS_REJECTED);
        verify($model->actioned_by)->equals($userId);
        verify($model->actioned_at)->notNull();
        verify($model->notes)->equals($reason);
    }

    /**
     * Test getFormattedMonth
     */
    public function testGetFormattedMonth()
    {
        $model = new PaysheetSuggestion();
        $model->suggested_month = '2024-01-01';
        
        $formatted = $model->getFormattedMonth();
        
        verify($formatted)->stringContainsString('January');
        verify($formatted)->stringContainsString('2024');
    }

    /**
     * Test canEdit when pending
     */
    public function testCanEditWhenPending()
    {
        $model = new PaysheetSuggestion();
        $model->status = PaysheetSuggestion::STATUS_PENDING;
        
        verify($model->canEdit())->true();
    }

    /**
     * Test canEdit when approved
     */
    public function testCanEditWhenApproved()
    {
        $model = new PaysheetSuggestion();
        $model->status = PaysheetSuggestion::STATUS_APPROVED;
        
        verify($model->canEdit())->false();
    }

    /**
     * Test canEdit when rejected
     */
    public function testCanEditWhenRejected()
    {
        $model = new PaysheetSuggestion();
        $model->status = PaysheetSuggestion::STATUS_REJECTED;
        
        verify($model->canEdit())->false();
    }

    /**
     * Test canDelete when pending
     */
    public function testCanDeleteWhenPending()
    {
        $model = new PaysheetSuggestion();
        $model->status = PaysheetSuggestion::STATUS_PENDING;
        
        verify($model->canDelete())->true();
    }

    /**
     * Test canDelete when rejected
     */
    public function testCanDeleteWhenRejected()
    {
        $model = new PaysheetSuggestion();
        $model->status = PaysheetSuggestion::STATUS_REJECTED;
        
        verify($model->canDelete())->true();
    }

    /**
     * Test canDelete when approved
     */
    public function testCanDeleteWhenApproved()
    {
        $model = new PaysheetSuggestion();
        $model->status = PaysheetSuggestion::STATUS_APPROVED;
        
        verify($model->canDelete())->false();
    }

    /**
     * Test status validation
     */
    public function testStatusValidation()
    {
        $model = new PaysheetSuggestion();
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
            PaysheetSuggestion::STATUS_PENDING,
            PaysheetSuggestion::STATUS_APPROVED,
            PaysheetSuggestion::STATUS_REJECTED,
        ];
        
        foreach ($validStatuses as $status) {
            $model = new PaysheetSuggestion();
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
        verify(PaysheetSuggestion::tableName())->stringContainsString('paysheet_suggestion');
    }

    /**
     * Test getStatusLabel returns HTML
     */
    public function testGetStatusLabel()
    {
        $model = new PaysheetSuggestion();
        $model->status = PaysheetSuggestion::STATUS_PENDING;
        
        $label = $model->getStatusLabel();
        
        verify($label)->notEmpty();
        verify($label)->stringContainsString('span');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new PaysheetSuggestion();
        $labels = $model->attributeLabels();
        
        verify($labels)->isArray();
        verify(array_key_exists('employee_id', $labels))->true();
        verify(array_key_exists('suggested_month', $labels))->true();
        verify(array_key_exists('basic_salary', $labels))->true();
        verify(array_key_exists('net_salary', $labels))->true();
        verify(array_key_exists('status', $labels))->true();
    }
}
