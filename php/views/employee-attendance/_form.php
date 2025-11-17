<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Employee;
use app\models\EmployeeAttendance;

/** @var yii\web\View $this */
/** @var app\models\EmployeeAttendance $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="employee-attendance-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'employee_id')->dropDownList(
        Employee::getList(),
        ['prompt' => 'Select Employee...']
    ) ?>

    <?= $form->field($model, 'attendance_date')->input('date') ?>

    <?= $form->field($model, 'attendance_type')->dropDownList(
        EmployeeAttendance::getAttendanceTypes()
    ) ?>

    <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-times"></i> Cancel',
            $model->employee_id ? ['/employee-attendance/employee-index', 'employeeId' => $model->employee_id] : ['/employee-attendance/index'],
            ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

