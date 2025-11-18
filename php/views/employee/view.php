<?php

/** @var yii\web\View $this */
/** @var app\models\Employee $model */

/** @var app\models\EmployeePayrollDetails $payrollDetailModel */

use app\widgets\BHtml;

$this->title = $model->getFullName();
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="employee-view">

    <h1><?= BHtml::encode($this->title) ?></h1>

    <p>
        <?= BHtml::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= BHtml::a('Paysheets', ['paysheet/index', 'PaysheetSearch[employee_id]' => $model->id], ['class' => 'btn btn-info']) ?>
        <?= BHtml::a('Update Payroll Details', ['update-payroll', 'employee_id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?= BHtml::a('<i class="fas fa-calendar-check"></i> Manage Attendance', ['/employee-attendance/employee-index', 'employeeId' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= BHtml::a('<i class="fas fa-money-bill-wave"></i> Salary Advances', ['/employee-salary-advance/employee-index', 'employeeId' => $model->id], ['class' => 'btn btn-dark']) ?>
    </p>

    <?= \app\widgets\BDetailView::widget([
            'model' => $model,
            'heading' => 'Employee Details',
            'attributes' => [
                    'id',
                    'first_name',
                    'last_name',
                    'nic',
                    'phone',
                    'position',
                    'department',
                    'hire_date',
                    'left_date',
                    'created_at:datetime',
                    'updated_at:datetime',
            ],
    ]) ?>
    <br/>
    <?= \app\widgets\BDetailView::widget([
            'model' => $payrollDetailModel,
            'heading' => 'Payroll Details',
            'attributes' => [
                [
                    'label' => 'Bank Account',
                    'value' => $payrollDetailModel->bankAccount ? $payrollDetailModel->bankAccount->accountTitle : 'N/A',
                ],
                'basic_salary',
                'allowances',
                'deductions',
                'tax_category',
                'payment_frequency'
            ],
    ]) ?>

    <br/>

    <!-- Salary Advance Summary -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                <i class="fas fa-money-bill-wave"></i> Salary Advance Summary
            </h5>
        </div>
        <div class="card-body">
            <?php
            $currentYear = date('Y');
            $yearTotal = \app\models\EmployeeSalaryAdvance::getYearToDateTotal($model->id, $currentYear);
            $allTimeTotal = \app\models\EmployeeSalaryAdvance::getTotalAdvanceAmount($model->id);
            $currentMonth = date('n');
            $currentMonthTotal = \app\models\EmployeeSalaryAdvance::getMonthlyTotal($model->id, $currentYear, $currentMonth);
            $advanceCount = \app\models\EmployeeSalaryAdvance::find()
                ->where(['employee_id' => $model->id])
                ->count();
            ?>
            <div class="row">
                <div class="col-md-3">
                    <strong>Year <?= $currentYear ?> Total:</strong>
                    <h5 class="text-primary"><?= Yii::$app->formatter->asCurrency($yearTotal) ?></h5>
                </div>
                <div class="col-md-3">
                    <strong>This Month (<?= date('F') ?>):</strong>
                    <h5 class="text-info"><?= Yii::$app->formatter->asCurrency($currentMonthTotal) ?></h5>
                </div>
                <div class="col-md-3">
                    <strong>All Time Total:</strong>
                    <h5 class="text-success"><?= Yii::$app->formatter->asCurrency($allTimeTotal) ?></h5>
                </div>
                <div class="col-md-3">
                    <strong>Total Advances:</strong>
                    <h5 class="text-warning"><?= $advanceCount ?></h5>
                </div>
            </div>
            <div class="mt-3">
                <?= BHtml::a('<i class="fas fa-calendar-alt"></i> View Monthly Overview',
                    ['/employee-salary-advance/employee-index', 'employeeId' => $model->id],
                    ['class' => 'btn btn-sm btn-dark']) ?>
                <?= BHtml::a('<i class="fas fa-plus"></i> Add New Advance',
                    ['/employee-salary-advance/create', 'employeeId' => $model->id],
                    ['class' => 'btn btn-sm btn-success']) ?>
            </div>
        </div>
    </div>

</div>
