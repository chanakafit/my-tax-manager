<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Employee;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var app\models\EmployeeSalaryAdvance $model */
/** @var yii\widgets\ActiveForm $form */
/** @var app\models\Employee|null $employee */
?>

<div class="employee-salary-advance-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?php if (isset($employee)): ?>
                <?= $form->field($model, 'employee_id')->hiddenInput()->label(false) ?>
                <div class="form-group">
                    <label class="control-label">Employee</label>
                    <p class="form-control-static"><strong><?= Html::encode($employee->getFullName()) ?></strong></p>
                </div>
            <?php else: ?>
                <?= $form->field($model, 'employee_id')->dropDownList(
                    ArrayHelper::map(Employee::find()->all(), 'id', 'fullName'),
                    ['prompt' => 'Select Employee...']
                ) ?>
            <?php endif; ?>

            <?= $form->field($model, 'advance_date')->widget(DatePicker::class, [
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ]
            ]) ?>

            <?= $form->field($model, 'amount')->textInput([
                'type' => 'number',
                'step' => '0.01',
                'min' => '0'
            ]) ?>
        </div>

        <div class="col-md-6">

            <?= $form->field($model, 'reason')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
        <?php if (isset($employee)): ?>
            <?= Html::a('<i class="fas fa-times"></i> Cancel',
                ['employee-index', 'employeeId' => $employee->id],
                ['class' => 'btn btn-secondary']) ?>
        <?php else: ?>
            <?= Html::a('<i class="fas fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

