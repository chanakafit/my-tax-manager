<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\SystemConfig;

/** @var yii\web\View $this */
/** @var app\models\SystemConfig $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="system-config-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'config_key')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'config_type')->dropDownList([
        SystemConfig::TYPE_STRING => 'String',
        SystemConfig::TYPE_INTEGER => 'Integer',
        SystemConfig::TYPE_BOOLEAN => 'Boolean',
        SystemConfig::TYPE_JSON => 'JSON',
        SystemConfig::TYPE_ARRAY => 'Array',
    ]) ?>

    <?php
    // Render appropriate input based on config type
    if ($model->config_type === SystemConfig::TYPE_INTEGER): ?>
        <?= $form->field($model, 'config_value')->textInput(['type' => 'number', 'class' => 'form-control'])->label('Config Value') ?>
    <?php elseif ($model->config_type === SystemConfig::TYPE_BOOLEAN): ?>
        <?= $form->field($model, 'config_value')->checkbox(['uncheck' => '0', 'value' => '1'])->label('Config Value') ?>
    <?php elseif ($model->config_type === SystemConfig::TYPE_JSON || $model->config_type === SystemConfig::TYPE_ARRAY): ?>
        <?= $form->field($model, 'config_value')->textarea(['class' => 'form-control', 'rows' => 6])->label('Config Value')->hint('Enter valid JSON format') ?>
    <?php else: ?>
        <?= $form->field($model, 'config_value')->textInput(['class' => 'form-control'])->label('Config Value') ?>
    <?php endif; ?>

    <?= $form->field($model, 'category')->dropDownList([
        SystemConfig::CATEGORY_BUSINESS => 'Business',
        SystemConfig::CATEGORY_BANKING => 'Banking',
        SystemConfig::CATEGORY_SYSTEM => 'System',
        SystemConfig::CATEGORY_INVOICE => 'Invoice',
    ]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_editable')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>



