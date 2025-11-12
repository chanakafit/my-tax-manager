<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PaysheetSuggestion */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Update Paysheet Suggestion: ' . $model->employee->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Paysheet Suggestions', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="paysheet-suggestion-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <strong>Pay Period:</strong> <?= $model->formattedMonth ?>
    </div>

    <div class="paysheet-suggestion-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'basic_salary')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'allowances')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'deductions')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'tax_amount')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                ]) ?>
            </div>
        </div>

        <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

        <div class="form-group">
            <?= Html::submitButton('<i class="glyphicon glyphicon-save"></i> Save Changes', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>

<?php
$this->registerJs(<<<JS
// Calculate net salary automatically
function calculateNetSalary() {
    var basic = parseFloat($('#paysheetsuggestion-basic_salary').val()) || 0;
    var allowances = parseFloat($('#paysheetsuggestion-allowances').val()) || 0;
    var deductions = parseFloat($('#paysheetsuggestion-deductions').val()) || 0;
    var tax = parseFloat($('#paysheetsuggestion-tax_amount').val()) || 0;
    
    var netSalary = basic + allowances - deductions - tax;
    
    // Display calculation preview
    var preview = '<div class="alert alert-success"><strong>Net Salary:</strong> LKR ' + netSalary.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</div>';
    $('#net-salary-preview').html(preview);
}

// Add preview container
$('.form-group:last').before('<div id="net-salary-preview"></div>');

// Bind change events
$('#paysheetsuggestion-basic_salary, #paysheetsuggestion-allowances, #paysheetsuggestion-deductions, #paysheetsuggestion-tax_amount').on('input change', calculateNetSalary);

// Initial calculation
calculateNetSalary();
JS
);
?>

