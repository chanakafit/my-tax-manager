<?php

use app\models\EmployeeSalaryAdvance;
use app\widgets\BHtml;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\EmployeeSalaryAdvanceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Salary Advances';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employee-salary-advance-index">

    <h1><?= BHtml::encode($this->title) ?></h1>

    <p>
        <?= BHtml::a('<i class="fas fa-plus"></i> Create Salary Advance', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'employee_id',
                'value' => function ($model) {
                    return $model->employee ? $model->employee->getFullName() : '';
                },
                'filter' => \yii\helpers\ArrayHelper::map(\app\models\Employee::find()->all(), 'id', 'fullName'),
            ],
            'advance_date:date',
            'amount:currency',
            'reason',
            'notes:ntext',
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'urlCreator' => function ($action, EmployeeSalaryAdvance $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>


</div>

