<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\BGridView as GridView;
use yii\data\ArrayDataProvider;

$this->title = 'Tax Record: ' . $model->tax_period_start . ' - ' . $model->tax_period_end;
$this->params['breadcrumbs'][] = ['label' => 'Tax Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tax-record-view">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
            <div>
                <?php if ($model->payment_status !== 'paid'): ?>
                    <?= Html::a('Make payment', ['tax-year/make-payment', 'taxCode' => $model->tax_code], [
                        'class' => 'btn btn-success',
                    ]) ?>
                <?php endif; ?>
                <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'tax_code',
                    'ird_ref',
                    'tax_period_start:date',
                    'tax_period_end:date',
                    'tax_type',
                    [
                        'attribute' => 'total_income',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'total_expenses',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'taxable_amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'label' => 'Tax Rate',
                        'value' => $model->tax_rate . '%',
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'tax_amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'payment_status',
                        'format' => 'raw',
                        'value' => Html::tag('span',
                            ucfirst($model->payment_status),
                            ['class' => 'badge bg-' . ($model->payment_status == 'paid' ? 'success' : 'warning')]
                        ),
                    ],
                    'payment_date:date',
                    'reference_number',
                ],
            ]) ?>
        </div>
    </div>

    <?php if ($relatedInvoices): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Related Invoices</h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $relatedInvoices,
                    'pagination' => false,
                ]),
                'columns' => [
                    'invoice_number',
                    'invoice_date:date',
                    [
                        'attribute' => 'total_amount_lkr',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                        'headerOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', ['/invoice/view', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-info',
                                    'title' => 'View Invoice',
                                ]);
                            },
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($relatedExpenses): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Related Expenses</h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $relatedExpenses,
                    'pagination' => false,
                ]),
                'columns' => [
                    'expense_date:date',
                    'description',
                    [
                        'attribute' => 'amount_lkr',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                        'headerOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', ['/expense/view', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-info',
                                    'title' => 'View Expense',
                                ]);
                            },
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($relatedPaysheets): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Related Paysheets</h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $relatedPaysheets,
                    'pagination' => false,
                ]),
                'columns' => [
                    [
                        'attribute' => 'employee_name',
                        'value' => function($model) {
                            return $model->employee ? $model->employee->fullName : 'N/A';
                        },
                    ],
                    [
                        'attribute' => 'pay_period_start',
                        'format' => 'date',
                    ],
                    [
                        'attribute' => 'pay_period_end',
                        'format' => 'date',
                    ],
                    [
                        'attribute' => 'net_salary',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                        'headerOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::tag('span',
                                ucfirst($model->status),
                                ['class' => 'badge bg-' . ($model->status === 'paid' ? 'success' : 'warning')]
                            );
                        },
                        'filter' => ['pending' => 'Pending', 'paid' => 'Paid'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', ['/paysheet/view', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-info',
                                    'title' => 'View Paysheet',
                                ]);
                            },
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
    <?php endif; ?>
</div>