<?php

use app\models\EmployeeSalaryAdvance;
use app\widgets\BHtml;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\EmployeeSalaryAdvance $model */

$this->title = 'Salary Advance #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Salary Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="employee-salary-advance-view">

    <h1><?= BHtml::encode($this->title) ?></h1>

    <p>
        <?= BHtml::a('<i class="fas fa-edit"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= BHtml::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this salary advance?',
                'method' => 'post',
            ],
        ]) ?>
        <?= BHtml::a('<i class="fas fa-user"></i> View Employee', ['/employee/view', 'id' => $model->employee_id], ['class' => 'btn btn-info']) ?>
        <?= BHtml::a('<i class="fas fa-list"></i> Back to List', ['employee-index', 'employeeId' => $model->employee_id], ['class' => 'btn btn-secondary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'employee_id',
                'value' => $model->employee ? $model->employee->getFullName() : '',
            ],
            'advance_date:date',
            'amount:currency',
            'reason',
            'notes:ntext',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>

