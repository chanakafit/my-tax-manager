<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\TaxPayment */

$this->title = 'Update Tax Payment';
$this->params['breadcrumbs'][] = ['label' => 'Tax Years', 'url' => ['tax-year/index']];
$this->params['breadcrumbs'][] = ['label' => 'Tax Year ' . $model->tax_year . '/' . ($model->tax_year + 1), 'url' => ['tax-year/view', 'year' => $model->tax_year]];
$this->params['breadcrumbs'][] = ['label' => 'View Payment', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="tax-payment-update">

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-edit"></i> <?= Html::encode($this->title) ?>
                <small class="float-right">Tax Year: <?= $model->tax_year ?>/<?= $model->tax_year + 1 ?></small>
            </h4>
        </div>

        <div class="card-body">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'payment_date')->widget(DatePicker::class, [
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ]
                    ]) ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'placeholder' => 'e.g., 50000.00'
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'payment_type')->dropDownList([
                        'quarterly' => 'Quarterly',
                        'final' => 'Final'
                    ], ['prompt' => 'Select Payment Type']) ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'quarter')->dropDownList([
                        1 => 'Quarter 1',
                        2 => 'Quarter 2',
                        3 => 'Quarter 3',
                        4 => 'Quarter 4'
                    ], [
                        'prompt' => 'Select Quarter (for quarterly payments)',
                        'disabled' => $model->payment_type !== 'quarterly'
                    ]) ?>
                </div>
            </div>

            <?= $form->field($model, 'reference_number')->textInput([
                'maxlength' => true,
                'placeholder' => 'Payment reference or transaction number'
            ]) ?>

            <?= $form->field($model, 'notes')->textarea([
                'rows' => 3,
                'placeholder' => 'Optional notes about this payment'
            ]) ?>

            <?php if ($model->receipt_file): ?>
            <div class="alert alert-info">
                <i class="fas fa-file"></i> <strong>Current Receipt:</strong>
                <?= Html::a('View Current Receipt', ['download-receipt', 'id' => $model->id], [
                    'target' => '_blank',
                    'class' => 'btn btn-sm btn-info ml-2'
                ]) ?>
                <br>
                <small class="text-muted">Upload a new file to replace the current receipt</small>
            </div>
            <?php endif; ?>

            <?= $form->field($model, 'uploadedFile')->widget(FileInput::class, [
                'options' => ['accept' => '.pdf,.png,.jpg,.jpeg'],
                'pluginOptions' => [
                    'showPreview' => true,
                    'showCaption' => true,
                    'showRemove' => true,
                    'showUpload' => false,
                    'showCancel' => false,
                    'browseClass' => 'btn btn-primary',
                    'browseIcon' => '<i class="fas fa-folder-open"></i> ',
                    'browseLabel' =>  'Browse',
                    'allowedFileExtensions' => ['pdf', 'png', 'jpg', 'jpeg'],
                    'maxFileSize' => 2048, // 2 MB
                    'msgSizeTooLarge' => 'File "{name}" ({size} KB) exceeds maximum allowed upload size of 2 MB.',
                ]
            ])->label('Receipt (Optional - Replace existing)') ?>

            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i>
                <strong>Supported File Types:</strong> PDF, PNG, JPG, JPEG<br>
                <strong>Maximum File Size:</strong> 2 MB
            </div>

            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-save"></i> Update Payment', [
                    'class' => 'btn btn-success'
                ]) ?>
                <?= Html::a('<i class="fas fa-times"></i> Cancel', ['view', 'id' => $model->id], [
                    'class' => 'btn btn-secondary'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>

<?php
$this->registerJs(<<<JS
    // Show/hide quarter field based on payment type
    $('#taxpayment-payment_type').on('change', function() {
        var quarterField = $('#taxpayment-quarter');
        if ($(this).val() === 'quarterly') {
            quarterField.prop('disabled', false).closest('.form-group').show();
        } else {
            quarterField.prop('disabled', true).val('').closest('.form-group').hide();
        }
    }).trigger('change');
JS
);
?>

