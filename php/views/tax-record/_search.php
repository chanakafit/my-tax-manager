<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\TaxRecordSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="tax-record-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'tax_period_start') ?>

    <?= $form->field($model, 'tax_period_end') ?>

    <?= $form->field($model, 'tax_type') ?>

    <?= $form->field($model, 'tax_rate') ?>

    <?php // echo $form->field($model, 'taxable_amount') ?>

    <?php // echo $form->field($model, 'tax_amount') ?>

    <?php // echo $form->field($model, 'payment_status') ?>

    <?php // echo $form->field($model, 'payment_date') ?>

    <?php // echo $form->field($model, 'reference_number') ?>

    <?php // echo $form->field($model, 'notes') ?>

    <?php // echo $form->field($model, 'related_invoice_ids') ?>

    <?php // echo $form->field($model, 'related_expense_ids') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
