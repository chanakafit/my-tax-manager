<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\OwnerBankAccount $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="owner-bank-account-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'account_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bank_name')->dropDownList(
        \app\models\OwnerBankAccount::getBankNameOptions(),
        ['prompt' => 'Select Bank', 'class' => 'form-control']
    ) ?>

    <?= $form->field($model, 'branch_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'swift_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_type')->dropDownList(
        \app\models\OwnerBankAccount::getAccountTypeOptions(),
        ['prompt' => 'Select Account Type', 'class' => 'form-control']
    ) ?>

    <?= $form->field($model, 'account_holder_type')->dropDownList(
        \app\models\OwnerBankAccount::getAccountHolderTypeOptions(),
        ['prompt' => 'Select Account Holder Type', 'class' => 'form-control']
    ) ?>

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
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

