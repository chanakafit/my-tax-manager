<?php

use app\models\Invoice;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\widgets\BGridView as GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\InvoiceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Invoices';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .action-column {
        width: 200px;
        text-align: center;
    }
    .btn-group-xs > .btn {
        padding: .25rem .4rem;
        font-size: .875rem;
        line-height: .8;
        border-radius: .2rem;
    }
    .status-badge {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: bold;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-paid { background: #d4edda; color: #155724; }
    .status-overdue { background: #f8d7da; color: #721c24; }
    .status-cancelled { background: #e2e3e5; color: #383d41; }
");
?>
<div class="invoice-index">
    <div class="row mb-4">
        <div class="col">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col text-right">
            <?= Html::a('<i class="fa fa-plus"></i> Create Invoice', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'invoice_number',
            [
                'attribute' => 'customerName',
                'value' => 'customer.company_name',
            ],
            'invoice_date:date',
            'due_date:date',
            [
                'attribute' => 'total_amount',
                'value' => function($model) {
                    return Yii::$app->formatter->asCurrency($model->total_amount, $model->currency_code);
                },
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::tag('span',
                        ucfirst($model->status),
                        ['class' => 'status-badge status-' . $model->status]
                    );
                },
                'filter' => Invoice::getStatusList(),
            ],
            [
                'class' => ActionColumn::class,
                'contentOptions' => ['class' => 'action-column'],
                'template' => '{actions}',
                'buttons' => [
                    'actions' => function ($url, $model, $key) {
                        $buttons = [];

                        // View button
                        $buttons[] = Html::a(
                            '<i class="fa fa-eye"></i>',
                            ['view', 'id' => $model->id],
                            ['class' => 'btn btn-info btn-group-xs', 'title' => 'View']
                        );

                        // Update button
                        if ($model->status !== Invoice::STATUS_PAID) {
                            $buttons[] = Html::a(
                                '<i class="fa fa-edit"></i>',
                                ['update', 'id' => $model->id],
                                ['class' => 'btn btn-primary btn-group-xs', 'title' => 'Update']
                            );
                        }

                        // Mark as Paid button
                        if ($model->status === Invoice::STATUS_PENDING || $model->status === Invoice::STATUS_OVERDUE) {
                            $buttons[] = Html::a(
                                '<i class="fa fa-check"></i>',
                                ['mark-as-paid', 'id' => $model->id],
                                [
                                    'class' => 'btn btn-success btn-group-xs',
                                    'title' => 'Mark as Paid',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to mark this invoice as paid?',
                                        'method' => 'post',
                                    ],
                                ]
                            );
                        }

                        // Send Email button
                        $buttons[] = Html::a(
                            '<i class="fa fa-envelope"></i>',
                            ['send-email', 'id' => $model->id],
                            [
                                'class' => 'btn btn-warning btn-group-xs',
                                'title' => 'Send Email',
                                'data' => [
                                    'confirm' => 'Send invoice to customer?',
                                    'method' => 'post',
                                ],
                            ]
                        );

                        // Download PDF button
                        $buttons[] = Html::a(
                            '<i class="fa fa-download"></i>',
                            ['download-pdf', 'id' => $model->id],
                            ['class' => 'btn btn-secondary btn-group-xs', 'title' => 'Download PDF', 'target' => '_blank', 'rel' => 'noopener noreferrer']
                        );

                        // Delete button (only for non-paid invoices)
                        if ($model->status !== Invoice::STATUS_PAID) {
                            $buttons[] = Html::a(
                                '<i class="fa fa-trash"></i>',
                                ['delete', 'id' => $model->id],
                                [
                                    'class' => 'btn btn-danger btn-group-xs',
                                    'title' => 'Delete',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this invoice?',
                                        'method' => 'post',
                                    ],
                                ]
                            );
                        }

                        return '<div class="btn-group" role="group">' . implode(' ', $buttons) . '</div>';
                    },
                ],
            ],
        ],
    ]); ?>

    <?php
    // Add summary section if needed
    $totals = $searchModel->getTotals();
    if ($totals['total_invoices'] > 0): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Summary</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Total Invoices:</strong> <?= $totals['total_invoices'] ?>
                </div>
                <div class="col-md-3">
                    <strong>Total Amount (LKR):</strong><br>
                    <?= Yii::$app->formatter->asCurrency($totals['total_amount_lkr'], 'LKR') ?>
                </div>
                <div class="col-md-3">
                    <strong>Total Tax:</strong><br>
                    <?= Yii::$app->formatter->asCurrency($totals['total_tax'], 'LKR') ?>
                </div>
                <div class="col-md-3">
                    <strong>Outstanding Amount (LKR):</strong><br>
                    <?= Yii::$app->formatter->asCurrency($totals['total_outstanding_lkr'], 'LKR') ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
