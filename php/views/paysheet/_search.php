<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Employee;

/* @var $this yii\web\View */
/* @var $model app\models\PaysheetSearch */
/* @var $form yii\widgets\ActiveForm */

$employeeList = Employee::getList();
?>

<div class="paysheet-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'employee_id')->dropDownList($employeeList, [
                'prompt' => 'Select Employee',
                'class' => 'form-control'
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'year')->dropDownList(
                array_combine(range(date('Y')-5, date('Y')), range(date('Y')-5, date('Y'))),
                ['prompt' => 'Select Year']
            ) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'month')->dropDownList([
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
            ], ['prompt' => 'Select Month']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'status')->dropDownList([
                'pending' => 'Pending',
                'processing' => 'Processing',
                'paid' => 'Paid',
                'cancelled' => 'Cancelled'
            ], ['prompt' => 'Select Status']) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Reset', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
