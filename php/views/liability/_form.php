<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Liability;

?>

<div class="liability-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'lender_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'liability_type')->dropDownList(Liability::getLiabilityTypeOptions(), ['prompt' => 'Select Type']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'liability_category')->dropDownList(Liability::getLiabilityCategoryOptions(), ['prompt' => 'Select Category']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'original_amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'start_date')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'end_date')->textInput(['type' => 'date']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'interest_rate')->textInput(['type' => 'number', 'step' => '0.01'])->hint('Annual interest rate in percentage') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'monthly_payment')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'status')->dropDownList(Liability::getStatusOptions()) ?>
        </div>
    </div>

    <?php if ($model->status == Liability::STATUS_SETTLED): ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'settlement_date')->textInput(['type' => 'date']) ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('<i class="fas fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

