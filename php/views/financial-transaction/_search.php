<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\FinancialTransactionSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="financial-transaction-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'bank_account_id') ?>

    <?= $form->field($model, 'transaction_date') ?>

    <?= $form->field($model, 'transaction_type') ?>

    <?= $form->field($model, 'amount') ?>

    <?php // echo $form->field($model, 'reference_number') ?>

    <?php // echo $form->field($model, 'related_invoice_id') ?>

    <?php // echo $form->field($model, 'related_expense_id') ?>

    <?php // echo $form->field($model, 'related_paysheet_id') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'category') ?>

    <?php // echo $form->field($model, 'payment_method') ?>

    <?php // echo $form->field($model, 'status') ?>

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
