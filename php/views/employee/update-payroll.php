<?php

/* @var $this yii\web\View */
/* @var $model app\models\EmployeePayrollDetails */
/* @var $bankAccountList array */

use app\widgets\BActiveForm;
use app\widgets\BHtml;

$this->title = 'Update Payroll Details: ' . $model->employee->getFullName();
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->employee->getFullName(), 'url' => ['view', 'id' => $model->employee_id]];
$this->params['breadcrumbs'][] = 'Update Payroll';
?>
<div class="employee-payroll-details-update">
    <h1><?= BHtml::encode($this->title) ?></h1>

    <div class="employee-payroll-details-form">

        <?php $form = BActiveForm::begin(); ?>

        <?= $form->field($model, 'bank_account_id')->dropDownList($bankAccountList, ['prompt' => 'Select Bank Account']) ?>

       <?= $form->field($model, 'basic_salary')->input('number', [
           'class' => 'form-control',
           'placeholder' => 'Enter basic salary',
           'step' => '0.01',
           'min' => '0',
       ]) ?>

        <?= $form->field($model, 'allowances')->input('number', [
            'class' => 'form-control',
            'placeholder' => 'Enter allowances amount',
            'step' => '0.01',
            'min' => '0',
        ]) ?>

        <?= $form->field($model, 'deductions')->input('number', [
            'class' => 'form-control',
            'placeholder' => 'Enter deductions amount',
            'step' => '0.01',
            'min' => '0',
        ]) ?>

        <?= $form->field($model, 'tax_category')->dropDownList([
            'A' => 'Category A (0%)',
            'B' => 'Category B (10%)',
            'C' => 'Category C (20%)',
            'D' => 'Category D (30%)',
            'E' => 'Category E (35%)',
        ], [
            'prompt' => 'Select Tax Category',
            'class' => 'form-control'
        ]) ?>

        <?= $form->field($model, 'payment_frequency')->dropDownList([
            'monthly' => 'Monthly',
            'bi-weekly' => 'Bi-Weekly',
            'weekly' => 'Weekly',
            'hourly' => 'Hourly',
            'daily' => 'Daily'
        ], [
            'prompt' => 'Select Payment Frequency',
            'class' => 'form-control'
        ]) ?>

        <div class="form-group">
            <?= BHtml::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>

        <?php BActiveForm::end(); ?>

    </div>
</div>
