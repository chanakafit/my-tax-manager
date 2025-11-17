<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\EmployeeAttendance $model */

$this->title = 'View Attendance Record';
$this->params['breadcrumbs'][] = ['label' => 'Employee Attendance', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employee-attendance-view">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this attendance record?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Employee Details</h5>
            <p class="card-text">
                <strong>Name:</strong> 
                <?= Html::a(
                    Html::encode($model->employee ? $model->employee->fullName : 'N/A'),
                    ['/employee/view', 'id' => $model->employee_id],
                    ['class' => 'text-decoration-none']
                ) ?><br>
                <?php if ($model->employee): ?>
                    <strong>Position:</strong> <?= Html::encode($model->employee->position) ?><br>
                    <strong>Department:</strong> <?= Html::encode($model->employee->department) ?><br>
                    <?= Html::a(
                        '<i class="fas fa-calendar-check"></i> View All Attendance',
                        ['employee-index', 'employeeId' => $model->employee_id],
                        ['class' => 'btn btn-sm btn-info mt-2']
                    ) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Attendance Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <p>
                        <strong>Date:</strong> <?= Yii::$app->formatter->asDate($model->attendance_date, 'php:Y-m-d') ?><br>
                        <strong>Type:</strong> 
                        <span class="badge bg-<?php
                            $badgeClass = 'secondary';
                            if ($model->attendance_type === 'full_day') {
                                $badgeClass = 'success';
                            } elseif ($model->attendance_type === 'half_day') {
                                $badgeClass = 'warning';
                            } elseif ($model->attendance_type === 'day_1_5') {
                                $badgeClass = 'info';
                            }
                            echo $badgeClass;
                        ?>">
                            <?= Html::encode($model->attendanceTypeLabel) ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p>
                        <strong>Created:</strong> <?= Yii::$app->formatter->asDatetime($model->created_at) ?><br>
                        <strong>Updated:</strong> <?= Yii::$app->formatter->asDatetime($model->updated_at) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($model->notes): ?>
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Notes</h5>
            <p class="card-text"><?= nl2br(Html::encode($model->notes)) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-3">
        <?= Html::a('<i class="fas fa-arrow-left"></i> Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
        <?= Html::a('<i class="fas fa-edit"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this attendance record?',
                'method' => 'post',
            ],
        ]) ?>
    </div>
</div>

