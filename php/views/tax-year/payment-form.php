<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;
use kartik\file\FileInput;
use kartik\date\DatePicker;

$this->title = 'Record Tax Payment';
$this->params['breadcrumbs'][] = ['label' => 'Tax Years', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Generate fiscal years list (current year - 2 to current year)
$fiscalYears = [];
$currentYear = (int)date('Y');
for ($year = $currentYear; $year >= $currentYear - 2; $year--) {
    $fiscalYears[$year] = $year . '/' . ($year + 1);
}

?>
<div class="tax-payment-form">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <?php $form = \app\widgets\BActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Payment Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form->field($model, 'tax_year')->dropDownList($fiscalYears, [
                                        'prompt' => 'Select Tax Year',
                                        'class' => 'form-control'
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form->field($model, 'payment_type')->dropDownList([
                                        'quarterly' => 'Quarterly Payment',
                                        'final' => 'Final Payment'
                                    ], [
                                        'prompt' => 'Select Payment Type',
                                        'onchange' => 'toggleQuarter(this.value)',
                                        'class' => 'form-control'
                                    ]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form->field($model, 'quarter')->dropDownList([
                                        1 => 'Q1 (Apr-Jun)',
                                        2 => 'Q2 (Jul-Sep)',
                                        3 => 'Q3 (Oct-Dec)',
                                        4 => 'Q4 (Jan-Mar)'
                                    ], [
                                        'prompt' => 'Select Quarter',
                                        'id' => 'quarter-field',
                                        'class' => 'form-control'
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form->field($model, 'payment_date')->widget(DatePicker::class, [
                                        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                                        'pluginOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd',
                                            'todayHighlight' => true,
                                            'todayBtn' => true,
                                        ]
                                    ]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form->field($model, 'amount')->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'class' => 'form-control'
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form->field($model, 'reference_number')->textInput(['class' => 'form-control']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Additional Information</h5>
                        </div>
                        <div class="card-body">
                            <?= $form->field($model, 'uploadedFile')->widget(FileInput::class, [
                                'options' => ['accept' => 'image/*,.pdf'],
                                'pluginOptions' => [
                                    'showPreview' => true,
                                    'showCaption' => true,
                                    'showRemove' => true,
                                    'showUpload' => false,
                                    'allowedFileExtensions' => ['pdf', 'png', 'jpg', 'jpeg'],
                                    'maxFileSize' => 2048,
                                    'browseClass' => 'btn btn-primary',
                                    'removeClass' => 'btn btn-danger',
                                ]
                            ]) ?>

                            <?= $form->field($model, 'notes')->textarea([
                                'rows' => 4,
                                'class' => 'form-control',
                                'style' => 'margin-top: 10px;'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <div class="d-flex justify-content-end">
                    <?= Html::a('Cancel', ['view', 'year' => $model->tax_year], ['class' => 'btn btn-secondary me-2']) ?>
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                </div>
            </div>

            <?php \app\widgets\BActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$script = <<<JS
function toggleQuarter(value) {
    const quarterField = document.getElementById('quarter-field');
    const quarterContainer = quarterField.closest('.form-group');
    
    if (value === 'quarterly') {
        quarterContainer.style.display = 'block';
        quarterField.required = true;
    } else {
        quarterContainer.style.display = 'none';
        quarterField.required = false;
        quarterField.value = '';
    }
}

// Run on page load for initial state
document.addEventListener('DOMContentLoaded', function() {
    const paymentType = document.querySelector('[name="TaxPayment[payment_type]"]');
    if (paymentType.value) {
        toggleQuarter(paymentType.value);
    }
});
JS;
$this->registerJs($script, \yii\web\View::POS_HEAD);
?>
