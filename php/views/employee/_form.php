<?php

use kartik\date\DatePicker;
use kartik\money\MaskMoney;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Employee $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="employee-form">

    <?php $form = \app\widgets\BActiveForm::begin(['options' => ['novalidate' => false]]); ?>

    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nic')->textInput([
            'pattern' => '([12][0-9]{11}|[0-9]{9}[vVxX])',
            'maxlength' => 12,
            'placeholder' => '199912345678 or 991234567V',
            'title' => 'Enter valid NIC (12 digits starting with 1/2 or 9 digits followed by V/X)',
            'required' => true
    ]) ?>

    <?= $form->field($model, 'phone')->textInput([
            'type' => 'tel',
            'pattern' => '0[0-9]{9}',
            'maxlength' => 10,
            'placeholder' => '0XXXXXXXXX',
            'title' => 'Enter valid Sri Lankan phone number (10 digits starting with 0)'
    ]) ?>

    <?= $form->field($model, 'position')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'department')->dropDownList(\app\helpers\Params::get('departments')) ?>

    <?= $form->field($model, 'hire_date')->widget(DatePicker::class, [
            'options' => ['placeholder' => 'Select hire date ...'],
            'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
            ]
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php \app\widgets\BActiveForm::end(); ?>

</div>
