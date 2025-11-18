<?php

use app\models\CapitalAllowance;
use app\models\FinancialTransaction;
use yii\helpers\Html;
use app\widgets\BGridView as GridView;
use yii\data\ArrayDataProvider;

/** @var yii\web\View $this */
/** @var int $year */


$this->title = 'Tax Year ' . $year . '/' . ($year + 1);
$this->params['breadcrumbs'][] = ['label' => 'Tax Years', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="tax-year-view">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
                    <div>
                        <?= Html::a('<i class="fas fa-file-invoice"></i> Tax Return Submission', ['/tax-return/index', 'year' => $year], [
                                'class' => 'btn btn-success me-2',
                        ]) ?>
                        <?= Html::a('<i class="fas fa-plus"></i> Record Tax Payment', ['make-payment', 'year' => $year], [
                                'class' => 'btn btn-primary',
                        ]) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Total Income:</th>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($summary['total_income']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Expenses:</th>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($summary['total_expenses']) ?></td>
                                </tr>
                                <tr>
                                    <th>Capital Allowances:</th>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($summary['total_capital_allowances']) ?></td>
                                </tr>
                                <tr>
                                    <th>Taxable Amount:</th>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($summary['total_income'] - $summary['total_expenses'] - $summary['total_capital_allowances']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Tax Amount:</th>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($summary['total_tax_amount']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Paid Amount:</th>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($summary['total_paid_amount']) ?></td>
                                </tr>
                                <tr class="table-<?= $summary['balance_due'] > 0 ? 'danger' : 'success' ?>">
                                    <th>Balance Due:</th>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency(abs($summary['balance_due'])) ?>
                                        <?= $summary['balance_due'] > 0 ? '(Unpaid)' : '(Overpaid)' ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Records -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quarterly Tax Records</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $summary['tax_records'],
                                    'pagination' => false,
                            ]),
                            'columns' => [
                                    'tax_code',
                                    [
                                            'attribute' => 'tax_period_start',
                                            'format' => 'date',
                                    ],
                                    [
                                            'attribute' => 'tax_period_end',
                                            'format' => 'date',
                                    ],
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
                                            'attribute' => 'tax_amount',
                                            'format' => ['decimal', 2],
                                            'contentOptions' => ['class' => 'text-end'],
                                    ],
                                    [
                                            'class' => 'yii\grid\ActionColumn',
                                            'template' => '{view}',
                                            'buttons' => [
                                                    'view' => function ($url, $model) {
                                                        return Html::a(
                                                                '<i class="fas fa-eye"></i>',
                                                                ['/tax-record/view', 'id' => $model->id],
                                                                ['class' => 'btn btn-sm btn-info']
                                                        );
                                                    },
                                            ],
                                    ],
                            ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Payments -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tax Payments</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $summary['tax_payments'],
                                    'pagination' => false,
                            ]),
                            'columns' => [
                                    [
                                            'attribute' => 'payment_date',
                                            'format' => 'date',
                                    ],
                                    [
                                            'attribute' => 'payment_type',
                                            'value' => function ($model) {
                                                return ucfirst($model->payment_type) .
                                                        ($model->quarter ? " (Q{$model->quarter})" : '');
                                            },
                                    ],
                                    [
                                            'attribute' => 'amount',
                                            'format' => ['decimal', 2],
                                            'contentOptions' => ['class' => 'text-end'],
                                    ],
                                    'reference_number',
                                    'notes:ntext',
                                    [
                                            'class' => 'yii\grid\ActionColumn',
                                            'template' => '{view}',
                                            'buttons' => [
                                                    'view' => function ($url, $model) {
                                                        return Html::a(
                                                                '<i class="fas fa-eye"></i>',
                                                                ['/tax-payment/view', 'id' => $model->id],
                                                                ['class' => 'btn btn-sm btn-info']
                                                        );
                                                    },
                                            ],
                                    ],
                            ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Capital Assets -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Capital Assets</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                    'allModels' => CapitalAllowance::find()
                                            ->joinWith('capitalAsset')
                                            ->where(['tax_code' => $year . '0'])
                                            ->all(),
                                    'pagination' => false,
                            ]),
                            'columns' => [
                                    [
                                            'attribute' => 'capitalAsset.asset_name',
                                            'label' => 'Asset',
                                    ],
                                    [
                                            'attribute' => 'capitalAsset.purchase_cost',
                                            'format' => ['decimal', 2],
                                            'contentOptions' => ['class' => 'text-end'],
                                    ],
                                    [
                                            'attribute' => 'year_number',
                                            'label' => 'Allowance Year',
                                    ],
                                    [
                                            'attribute' => 'allowance_amount',
                                            'format' => ['decimal', 2],
                                            'contentOptions' => ['class' => 'text-end'],
                                    ],
                                    [
                                            'attribute' => 'written_down_value',
                                            'format' => ['decimal', 2],
                                            'contentOptions' => ['class' => 'text-end'],
                                    ],
                                    [
                                            'class' => 'yii\grid\ActionColumn',
                                            'template' => '{view}',
                                            'buttons' => [
                                                    'view' => function ($url, $model) {
                                                        return Html::a(
                                                                '<i class="fas fa-eye"></i>',
                                                                ['/capital-asset/view', 'id' => $model->capital_asset_id],
                                                                ['class' => 'btn btn-sm btn-info']
                                                        );
                                                    },
                                            ],
                                    ],
                            ],
                            'showFooter' => true,
                            'summary' => false,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Invoices</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $invoices,
                                    'pagination' => false,
                            ]),
                            'columns' => [
                                    'invoice_number',
                                    [
                                            'attribute' => 'invoice_date',
                                            'format' => 'date',
                                    ],
                                    [
                                            'attribute' => 'payment_date',
                                            'format' => 'date',
                                    ],
                                    [
                                            'attribute' => 'customer.company_name',
                                            'label' => 'Customer',
                                    ],
                                    [
                                            'attribute' => 'total_amount_lkr',
                                            'format' => ['decimal', 2],
                                            'contentOptions' => ['class' => 'text-end'],
                                    ],
                                    [
                                            'class' => 'yii\grid\ActionColumn',
                                            'template' => '{view}',
                                            'buttons' => [
                                                    'view' => function ($url, $model) {
                                                        return Html::a(
                                                                '<i class="fas fa-eye"></i>',
                                                                ['/invoice/view', 'id' => $model->id],
                                                                ['class' => 'btn btn-sm btn-info']
                                                        );
                                                    },
                                            ],
                                    ],
                            ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Expenses</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $expenses,
                                    'pagination' => false,
                            ]),
                            'columns' => [
                                    [
                                            'attribute' => 'expense_date',
                                            'format' => 'date',
                                    ],
                                    'title',
                                    [
                                            'attribute' => 'expense_category_id',
                                            'value' => function ($model) {
                                                /** @var \app\models\Expense $model */
                                                return $model->expenseCategory ? $model->expenseCategory->name : null;
                                            },
                                            'label' => 'Category',
                                    ],
                                    [
                                            'attribute' => 'amount_lkr',
                                            'format' => ['decimal', 2],
                                            'contentOptions' => ['class' => 'text-end'],
                                    ],
                                    'receipt_number',
                                    [
                                            'attribute' => 'payment_method',
                                            'value' => fn($model) => FinancialTransaction::PAYMENT_METHODS[$model->payment_method]
                                    ],
                                    [
                                            'attribute' => 'status',
                                            'format' => 'raw',
                                            'value' => function ($model) {
                                                return Html::tag('span',
                                                        ucfirst($model->status),
                                                        ['class' => 'badge bg-' . ($model->status === 'paid' ? 'success' : 'warning')]
                                                );
                                            }
                                    ],
                                    [
                                            'class' => 'yii\grid\ActionColumn',
                                            'template' => '{view}',
                                            'buttons' => [
                                                    'view' => function ($url, $model) {
                                                        return Html::a(
                                                                '<i class="fas fa-eye"></i>',
                                                                ['/expense/view', 'id' => $model->id],
                                                                ['class' => 'btn btn-sm btn-info']
                                                        );
                                                    },
                                            ],
                                    ],
                            ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Paysheets -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Paysheets</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $paysheets,
                                    'pagination' => false,
                            ]),
                            'columns' => [
                                    [
                                            'attribute' => 'employee.fullName',
                                            'label' => 'Employee',
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
                                    ],
                                    [
                                            'class' => 'yii\grid\ActionColumn',
                                            'template' => '{view}',
                                            'buttons' => [
                                                    'view' => function ($url, $model) {
                                                        return Html::a(
                                                                '<i class="fas fa-eye"></i>',
                                                                ['/paysheet/view', 'id' => $model->id],
                                                                ['class' => 'btn btn-sm btn-info']
                                                        );
                                                    },
                                            ],
                                    ],
                            ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
