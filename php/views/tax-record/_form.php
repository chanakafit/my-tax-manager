<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;

?>

<div class="tax-record-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Tax Details</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'tax_code')->textInput(['readonly' => true]) ?>

                    <?= $form->field($model, 'ird_ref')->textInput() ?>

                    <?= $form->field($model, 'tax_period_start')->textInput(['readonly' => true]) ?>

                    <?= $form->field($model, 'tax_period_end')->textInput(['readonly' => true]) ?>

                    <?= $form->field($model, 'total_income')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'readonly' => true
                    ]) ?>

                    <?= $form->field($model, 'total_expenses')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'readonly' => true
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Tax Calculation</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'tax_rate')->textInput([
                        'type' => 'number',
                        'step' => '0.01'
                    ]) ?>

                    <?= $form->field($model, 'taxable_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01'
                    ]) ?>

                    <?= $form->field($model, 'tax_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01'
                    ]) ?>

                    <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-4">
        <div class="d-flex justify-content-end">
            <?= Html::a('Cancel', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary me-2']) ?>
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
    // Auto-calculate tax amount when rate or taxable amount changes
    $('#taxrecord-tax_rate, #taxrecord-taxable_amount').on('change keyup', function() {
        var rate = parseFloat($('#taxrecord-tax_rate').val()) || 0;
        var amount = parseFloat($('#taxrecord-taxable_amount').val()) || 0;
        var taxAmount = (amount * rate) / 100;
        $('#taxrecord-tax_amount').val(taxAmount.toFixed(2));
    });
JS;
$this->registerJs($js);
?>
