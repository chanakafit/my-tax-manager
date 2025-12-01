<?php
use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Customer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-form">
    <?php $form = \app\widgets\BActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'website')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'default_currency')->dropDownList(
                $model::getCurrencyList(),
                ['prompt' => 'Select Currency...']
            ) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'address')->textarea(['rows' => 3]) ?>
            <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'state')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'postal_code')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'country')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?= $form->field($model, 'registry_code')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'tax_number')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'notes')->textarea(['rows' => 4]) ?>
    <?= $form->field($model, 'status')->dropDownList($model::getStatusList()) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php \app\widgets\BActiveForm::end(); ?>
</div>
