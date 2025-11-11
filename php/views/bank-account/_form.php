<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\BankAccount $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="bank-account-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'account_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bank_name')->dropDownList(
        \app\models\BankAccount::getBankNameOptions(),
        ['prompt' => 'Select Bank', 'class' => 'form-control']
    ) ?>

    <?= $form->field($model, 'branch_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'swift_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_type')->dropDownList([
        'savings' => 'Savings Account',
        'current' => 'Current Account',
        'other' => 'Other'
    ], [
        'prompt' => 'Select Account Type',
        'class' => 'form-control'
    ]) ?>

    <?= $form->field($model, 'account_holder_type')->dropDownList([
        'business' => 'Business Account',
        'personal' => 'Personal Account',
    ], [
        'prompt' => 'Select Account Holder Type',
        'class' => 'form-control'
    ]) ?>

    <?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'is_active')->dropDownList([
        1 => 'Active',
        0 => 'Inactive'
    ], [
        'prompt' => 'Select Status',
        'class' => 'form-control'
    ]) ?>
    <?= $form->field($model, 'notes')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
