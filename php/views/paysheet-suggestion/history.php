<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Paysheet Suggestions History';
$this->params['breadcrumbs'][] = ['label' => 'Paysheet Suggestions', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'History';
?>
<div class="paysheet-suggestion-history">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> Back to Pending', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'employee_id',
                'label' => 'Employee',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::encode($model->employee->fullName) . '<br>' .
                           '<small class="text-muted">' . Html::encode($model->employee->position) . '</small>';
                },
            ],
            [
                'attribute' => 'suggested_month',
                'label' => 'Pay Period',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->formattedMonth;
                },
            ],
            [
                'attribute' => 'net_salary',
                'label' => 'Net Salary',
                'format' => 'raw',
                'value' => function ($model) {
                    return 'LKR ' . Yii::$app->formatter->asDecimal($model->net_salary, 2);
                },
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->statusLabel;
                },
            ],
            [
                'attribute' => 'actioned_by',
                'label' => 'Actioned By',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->actionedBy) {
                        return Html::encode($model->actionedBy->username);
                    }
                    return '<span class="text-muted">N/A</span>';
                },
            ],
            [
                'attribute' => 'actioned_at',
                'label' => 'Actioned At',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'notes',
                'format' => 'ntext',
            ],
        ],
    ]); ?>

</div>

