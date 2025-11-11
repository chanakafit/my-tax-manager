<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ExpenseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="expense-search">

    <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'expense_category_id') ?>

    <?= $form->field($model, 'expense_date') ?>

    <?= $form->field($model, 'title') ?>

    <?php // echo $form->field($model, 'amount') ?>

    <?php // echo $form->field($model, 'tax_amount') ?>

    <?php // echo $form->field($model, 'receipt_number') ?>

    <?php echo $form->field($model, 'payment_method') ?>

    <?php echo $form->field($model, 'status') ?>

    <?php echo $form->field($model, 'vendor_id') ?>

    <?php echo $form->field($model, 'is_recurring') ?>

    <?php echo $form->field($model, 'recurring_interval') ?>

    <?php // echo $form->field($model, 'next_recurring_date') ?>

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
