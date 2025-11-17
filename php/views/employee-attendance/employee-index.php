<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Employee $employee */
/** @var app\models\EmployeeAttendanceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $monthlySummary */
/** @var array $yearlySummary */
/** @var string $selectedYear */
/** @var string $selectedMonth */

$this->title = 'Attendance: ' . $employee->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['/employee/index']];
$this->params['breadcrumbs'][] = ['label' => $employee->fullName, 'url' => ['/employee/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = 'Attendance';
?>
<div class="employee-attendance-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-user"></i> Back to Employee', ['/employee/view', 'id' => $employee->id], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('<i class="fas fa-plus"></i> Add Attendance', ['create', 'employeeId' => $employee->id], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar-alt"></i> Monthly Summary
            </h5>
        </div>
        <div class="card-body">
            <form method="get" class="form-inline mb-3">
                <label class="mr-2">View Summary:</label>
                <select name="month" class="form-control mr-2">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $selectedMonth == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-control mr-2">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> View</button>
            </form>

            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= $monthlySummary['full_day']['count'] ?></h3>
                            <p class="mb-0">Full Days</p>
                            <small>(<?= $monthlySummary['full_day']['days'] ?> days)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3><?= $monthlySummary['half_day']['count'] ?></h3>
                            <p class="mb-0">Half Days</p>
                            <small>(<?= $monthlySummary['half_day']['days'] ?> days)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3><?= $monthlySummary['day_1_5']['count'] ?></h3>
                            <p class="mb-0">1.5 Days</p>
                            <small>(<?= $monthlySummary['day_1_5']['days'] ?> days)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3><?= number_format($monthlySummary['total_days'], 1) ?></h3>
                            <p class="mb-0">Total Days</p>
                            <small>&nbsp;</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yearly Breakdown -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-chart-bar"></i> Yearly Breakdown - <?= $selectedYear ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Month</th>
                            <th>Full Days</th>
                            <th>Half Days</th>
                            <th>1.5 Days</th>
                            <th>Total Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $yearTotal = ['full' => 0, 'half' => 0, 'one_five' => 0, 'total' => 0];
                        foreach ($yearlySummary as $month => $summary):
                            $yearTotal['full'] += $summary['full_day']['days'];
                            $yearTotal['half'] += $summary['half_day']['days'];
                            $yearTotal['one_five'] += $summary['day_1_5']['days'];
                            $yearTotal['total'] += $summary['total_days'];
                        ?>
                            <tr>
                                <td><?= date('F', mktime(0, 0, 0, (int)$month, 1)) ?></td>
                                <td><?= $summary['full_day']['count'] ?> (<?= $summary['full_day']['days'] ?> days)</td>
                                <td><?= $summary['half_day']['count'] ?> (<?= $summary['half_day']['days'] ?> days)</td>
                                <td><?= $summary['day_1_5']['count'] ?> (<?= $summary['day_1_5']['days'] ?> days)</td>
                                <td><strong><?= number_format($summary['total_days'], 1) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-primary font-weight-bold">
                            <td><strong>TOTAL</strong></td>
                            <td><strong><?= number_format($yearTotal['full'], 1) ?> days</strong></td>
                            <td><strong><?= number_format($yearTotal['half'], 1) ?> days</strong></td>
                            <td><strong><?= number_format($yearTotal['one_five'], 1) ?> days</strong></td>
                            <td><strong><?= number_format($yearTotal['total'], 1) ?> days</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Attendance Records -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Attendance Records
            </h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'attendance_date',
                        'format' => ['date', 'php:Y-m-d'],
                        'filter' => Html::activeInput('date', $searchModel, 'attendance_date', ['class' => 'form-control']),
                    ],
                    [
                        'attribute' => 'attendance_type',
                        'value' => function($model) {
                            return $model->attendanceTypeLabel;
                        },
                        'filter' => Html::activeDropDownList($searchModel, 'attendance_type',
                            \app\models\EmployeeAttendance::getAttendanceTypes(),
                            ['class' => 'form-control', 'prompt' => 'All Types']
                        ),
                    ],
                    [
                        'attribute' => 'notes',
                        'format' => 'ntext',
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update} {delete}',
                        'buttons' => [
                            'update' => function ($url, $model) {
                                return Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'Edit',
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Delete',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this attendance record?',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

