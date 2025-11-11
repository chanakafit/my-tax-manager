<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;
use app\helpers\Params;

/** @var yii\web\View $this */
/** @var app\models\Vendor $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vendor-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contact')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'currency_code')->dropDownList(
        Params::get('currencies'),
        ['prompt' => 'Select Currency']
    ) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
