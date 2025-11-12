<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Pending Paysheet Suggestions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paysheet-suggestion-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <i class="glyphicon glyphicon-info-sign"></i>
        <strong>Paysheet Health Check</strong>: These are automatically generated salary paysheets for employees based on their payroll details.
        Please review and approve them to create actual paysheets, or reject/delete if not needed.
    </div>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-time"></i> View History', ['history'], ['class' => 'btn btn-default']) ?>
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
                    return Html::a(
                        Html::encode($model->employee->fullName),
                        ['employee/view', 'id' => $model->employee_id],
                        ['target' => '_blank']
                    ) . '<br><small class="text-muted">' . Html::encode($model->employee->position) . '</small>';
                },
            ],
            [
                'attribute' => 'suggested_month',
                'label' => 'Pay Period',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<strong>' . $model->formattedMonth . '</strong>';
                },
            ],
            [
                'attribute' => 'basic_salary',
                'label' => 'Basic Salary',
                'format' => 'raw',
                'value' => function ($model) {
                    $html = '<strong>LKR ' . Yii::$app->formatter->asDecimal($model->basic_salary, 2) . '</strong>';
                    if ($model->allowances > 0) {
                        $html .= '<br><small class="text-success">+ Allowances: LKR ' . Yii::$app->formatter->asDecimal($model->allowances, 2) . '</small>';
                    }
                    if ($model->deductions > 0) {
                        $html .= '<br><small class="text-danger">- Deductions: LKR ' . Yii::$app->formatter->asDecimal($model->deductions, 2) . '</small>';
                    }
                    return $html;
                },
            ],
            [
                'attribute' => 'net_salary',
                'label' => 'Net Salary',
                'format' => 'raw',
                'value' => function ($model) {
                    $html = '<strong class="text-primary">LKR ' . Yii::$app->formatter->asDecimal($model->net_salary, 2) . '</strong>';
                    if ($model->tax_amount > 0) {
                        $html .= '<br><small class="text-muted">Tax: LKR ' . Yii::$app->formatter->asDecimal($model->tax_amount, 2) . '</small>';
                    }
                    return $html;
                },
            ],
            [
                'attribute' => 'generated_at',
                'label' => 'Generated',
                'format' => 'datetime',
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{approve} {update} {reject} {delete}',
                'contentOptions' => ['style' => 'white-space: nowrap; min-width: 200px;'],
                'buttons' => [
                    'approve' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-ok"></span> Approve',
                            ['approve', 'id' => $model->id],
                            [
                                'class' => 'btn btn-success btn-xs',
                                'title' => 'Approve and set payment date',
                                'style' => 'margin: 2px;',
                            ]
                        );
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-pencil"></span> Edit',
                            ['update', 'id' => $model->id],
                            [
                                'class' => 'btn btn-info btn-xs',
                                'title' => 'Edit amounts',
                                'style' => 'margin: 2px;',
                            ]
                        );
                    },
                    'reject' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-remove"></span> Reject',
                            ['reject', 'id' => $model->id],
                            [
                                'class' => 'btn btn-warning btn-xs',
                                'title' => 'Reject this suggestion',
                                'data-confirm' => 'Are you sure you want to reject this suggestion?',
                                'data-method' => 'post',
                                'style' => 'margin: 2px;',
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span> Delete',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'btn btn-danger btn-xs',
                                'title' => 'Delete this suggestion',
                                'data-confirm' => 'Are you sure you want to delete this suggestion?',
                                'data-method' => 'post',
                                'style' => 'margin: 2px;',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

</div>

<style>
.btn-xs {
    padding: 3px 8px;
    font-size: 12px;
    line-height: 1.5;
    border-radius: 3px;
}

.grid-view .btn {
    white-space: nowrap;
}

.grid-view td .glyphicon {
    margin-right: 3px;
}
</style>

