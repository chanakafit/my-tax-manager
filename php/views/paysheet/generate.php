<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Generate Paysheets';
$this->params['breadcrumbs'][] = ['label' => 'Paysheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$months = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
];

$currentYear = date('Y');
$years = range($currentYear - 5, $currentYear + 5);
$years = array_combine($years, $years);
?>

<div class="paysheet-generate">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'employee_ids')->listBox($employeeList, [
                'multiple' => true,
                'size' => 10,
                'class' => 'form-control'
            ])->hint('Hold Ctrl/Cmd to select multiple employees') ?>

            <?= $form->field($model, 'month')->dropDownList($months, [
                'prompt' => 'Select month (optional)...',
                'class' => 'form-control'
            ])->hint('Leave empty for yearly paysheet') ?>

            <?= $form->field($model, 'year')->dropDownList($years, [
                'class' => 'form-control'
            ]) ?>

            <div class="form-group">
                <?= Html::submitButton('Generate Paysheets', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
