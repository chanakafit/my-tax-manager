<?php

use app\widgets\BActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ExpenseCategory $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="expense-category-form">

    <?php $form = BActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'budget_limit')->textInput([
            'type' => 'number',
            'step' => '0.01',
            'min' => '0',
            'class' => 'form-control money-input',
            'placeholder' => '0.00'
    ]) ?>
    <?= $form->field($model, 'is_active')->dropDownList([
            1 => 'Active',
            0 => 'Inactive'
    ], [
            'prompt' => 'Select Status',
            'class' => 'form-control'
    ]) ?>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php BActiveForm::end(); ?>

</div>
