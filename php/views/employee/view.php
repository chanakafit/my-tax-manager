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

</div>
