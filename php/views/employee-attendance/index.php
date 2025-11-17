<?php

use yii\helpers\Html;
use app\widgets\BGridView as GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\EmployeeAttendanceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Employee Attendance';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employee-attendance-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-plus"></i> Add Attendance', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'employee_name',
                'label' => 'Employee',
                'value' => function($model) {
                    return $model->employee ? $model->employee->fullName : 'N/A';
                },
                'filter' => Html::activeTextInput($searchModel, 'employee_name', ['class' => 'form-control', 'placeholder' => 'Search employee...']),
            ],
            [
                'attribute' => 'attendance_date',
                'format' => ['date', 'php:Y-m-d'],
                'filter' => Html::activeInput('date', $searchModel, 'attendance_date', ['class' => 'form-control']),
            ],
            [
                'attribute' => 'attendance_type',
                'label' => 'Type',
                'value' => function($model) {
                    return $model->attendanceTypeLabel;
                },
                'filter' => Html::activeDropDownList($searchModel, 'attendance_type',
                    \app\models\EmployeeAttendance::getAttendanceTypes(),
                    ['class' => 'form-control', 'prompt' => 'All Types']
                ),
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'notes',
                'format' => 'ntext',
                'contentOptions' => ['style' => 'max-width: 300px;'],
            ],
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:Y-m-d H:i'],
                'filter' => false,
                'contentOptions' => ['class' => 'text-muted small'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'contentOptions' => ['class' => 'action-column text-nowrap'],
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a(
                            '<i class="fas fa-eye"></i>',
                            ['view', 'id' => $model->id],
                            ['class' => 'btn btn-sm btn-info', 'title' => 'View']
                        );
                    },
                    'update' => function ($url, $model) {
                        return Html::a(
                            '<i class="fas fa-edit"></i>',
                            ['update', 'id' => $model->id],
                            ['class' => 'btn btn-sm btn-primary', 'title' => 'Edit']
                        );
                    },
                    'delete' => function ($url, $model) {
                        return Html::a(
                            '<i class="fas fa-trash"></i>',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'btn btn-sm btn-danger',
                                'title' => 'Delete',
                                'data' => [
                                    'confirm' => 'Are you sure you want to delete this attendance record?',
                                    'method' => 'post',
                                ],
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>

