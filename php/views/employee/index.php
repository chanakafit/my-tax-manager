<?php

use app\models\Employee;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\widgets\BGridView as GridView;

/** @var yii\web\View $this */
/** @var app\models\EmployeeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Employees';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employee-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Employee', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= \app\widgets\BGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'heading' => 'Employee List',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'first_name',
            'last_name',
            'nic',
            'phone',
            //'position',
            //'department',
            //'hire_date',
            //'salary',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'template' => '{view} {update} {delete} {payroll} {attendance} {advance}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>',
                            ['view', 'id' => $model->id],
                            [
                                'title' => 'View',
                                'class' => 'btn btn-sm btn-info',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-pencil-alt"></i>',
                            ['update', 'id' => $model->id],
                            [
                                'title' => 'Update',
                                'class' => 'btn btn-sm btn-primary',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>',
                            ['delete', 'id' => $model->id],
                            [
                                'title' => 'Delete',
                                'class' => 'btn btn-sm btn-danger',
                                'data-pjax' => '0',
                                'data-confirm' => 'Are you sure you want to delete this employee?',
                                'data-method' => 'post',
                            ]
                        );
                    },
                    'payroll' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-money-check"></i>',
                            ['update-payroll', 'employee_id' => $model->id],
                            [
                                'title' => 'Update Payroll Details',
                                'class' => 'btn btn-sm btn-warning',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'attendance' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-calendar-check"></i>',
                            ['/employee-attendance/employee-index', 'employeeId' => $model->id],
                            [
                                'title' => 'Manage Attendance',
                                'class' => 'btn btn-sm btn-success',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                    'advance' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-money-bill-wave"></i>',
                            ['/employee-salary-advance/employee-index', 'employeeId' => $model->id],
                            [
                                'title' => 'Salary Advances',
                                'class' => 'btn btn-sm btn-dark',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                ],
                'urlCreator' => function ($action, Employee $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>


</div>

<?php
$this->registerCss(<<<CSS
/* Style for action column buttons */
.grid-view td .btn {
    margin: 2px;
}
.grid-view td .btn i {
    font-size: 14px;
}
CSS
);
?>

