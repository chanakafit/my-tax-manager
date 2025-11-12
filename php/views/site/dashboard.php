<?php

use app\models\FinancialTransaction;
use app\widgets\ExpenseHealthCheckWidget;
use app\widgets\PaysheetHealthCheckWidget;
use yii\helpers\Html;
use kartik\grid\GridView;
use miloschuman\highcharts\Highcharts;
use yii\data\ActiveDataProvider;

/** @var $yearlyData array */
/** @var $yearlyExpenses array */
/** @var $taxSummary array */
/** @var $recentTransactions ActiveDataProvider */
/** @var $monthlyTrends array */
/** @var $taxYearString string */
/** @var $taxYearStart \DateTime */
/** @var $taxYearEnd \DateTime */

$this->title = "Financial Dashboard - Tax Year {$taxYearString}";
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-dashboard">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><?= Html::encode($this->title) ?></h2>
                <div class="tax-year-info text-muted">
                    <?= Yii::$app->formatter->asDate($taxYearStart, 'medium') ?> -
                    <?= Yii::$app->formatter->asDate($taxYearEnd, 'medium') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Health Check Widget -->
    <div class="row mb-4">
        <div class="col-md-12">
            <?= ExpenseHealthCheckWidget::widget(['showDetails' => true, 'limit' => 5]) ?>
        </div>
    </div>

    <!-- Paysheet Health Check Widget -->
    <div class="row mb-4">
        <div class="col-md-12">
            <?= PaysheetHealthCheckWidget::widget(['showDetails' => true, 'limit' => 5]) ?>
        </div>
    </div>

    <!-- Cumulative Balance -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cumulative Balance</h3>
                </div>
                <div class="card-body">
                    <?php
                    // Prepare data for cumulative balance chart
                    $chartData = array_map(function($item) {
                        return [
                            strtotime($item['date']) * 1000, // Convert to milliseconds for Highcharts
                            (float)$item['balance']
                        ];
                    }, $cumulativeData);

                    echo Highcharts::widget([
                        'options' => [
                            'title' => ['text' => 'Balance Progression'],
                            'chart' => [
                                'type' => 'area',
                                'height' => 400,
                                'zoomType' => 'x'
                            ],
                            'xAxis' => [
                                'type' => 'datetime',
                                'title' => ['text' => 'Date'],
                                'labels' => [
                                    'format' => '{value:%Y-%m-%d}'
                                ],
                                'minRange' => 24 * 3600 * 1000 // One day
                            ],
                            'yAxis' => [
                                'title' => ['text' => 'Balance (LKR)'],
                                'labels' => [
                                    'formatter' => new \yii\web\JsExpression('function() { 
                                        return "LKR " + Highcharts.numberFormat(this.value, 0);
                                    }')
                                ],
                                'plotLines' => [[
                                    'value' => 0,
                                    'color' => '#666',
                                    'width' => 1,
                                    'zIndex' => 5
                                ]]
                            ],
                            'tooltip' => [
                                'formatter' => new \yii\web\JsExpression('function() {
                                    return Highcharts.dateFormat("%Y-%m-%d", this.x) + "<br/>" +
                                           "Balance: LKR " + Highcharts.numberFormat(this.y, 0);
                                }')
                            ],
                            'plotOptions' => [
                                'area' => [
                                    'fillColor' => [
                                        'linearGradient' => [
                                            'x1' => 0,
                                            'y1' => 0,
                                            'x2' => 0,
                                            'y2' => 1
                                        ],
                                        'stops' => [
                                            [0, '#28a745'],
                                            [1, new \yii\web\JsExpression('Highcharts.color("#28a745").setOpacity(0).get("rgba")')]
                                        ]
                                    ],
                                    'marker' => [
                                        'radius' => 2
                                    ],
                                    'lineWidth' => 1,
                                    'color' => '#28a745',
                                    'states' => [
                                        'hover' => [
                                            'lineWidth' => 2
                                        ]
                                    ],
                                    'threshold' => null
                                ]
                            ],
                            'series' => [[
                                'name' => 'Balance',
                                'data' => $chartData
                            ]],
                            'credits' => ['enabled' => false]
                        ]
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Year Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Income (Tax Year <?= Html::encode($taxYearString) ?>)</h5>
                    <h3 class="mb-0">
                        <?= Yii::$app->formatter->asCurrency(
                            array_sum(array_column(
                                array_filter($monthlyTrends, function($trend) {
                                    return $trend['category'] === FinancialTransaction::CATEGORY_INCOME;
                                }),
                                'total_amount'
                            )),
                            'LKR'
                        ) ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Expenses (Tax Year <?= Html::encode($taxYearString) ?>)</h5>
                    <h3 class="mb-0">
                        <?= Yii::$app->formatter->asCurrency(
                            array_sum(array_column(
                                array_filter($monthlyTrends, function($trend) {
                                    return in_array($trend['category'], [FinancialTransaction::CATEGORY_EXPENSE, FinancialTransaction::CATEGORY_PAYROLL]);
                                }),
                                'total_amount'
                            )),
                            'LKR'
                        ) ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Tax Summary (Tax Year <?= Html::encode($taxYearString) ?>)</h5>
                    <h3 class="mb-0">
                        <?= Yii::$app->formatter->asCurrency(
                            array_sum(array_column($taxSummary, 'tax_amount')),
                            'LKR'
                        ) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Transaction Trends</h3>
                </div>
                <div class="card-body">
                    <?php
                    // Prepare data for monthly trends chart
                    $months = [
                            4 =>'Apr',
                            5 =>'May',
                            6 =>'Jun',
                            7 =>'Jul',
                            8 =>'Aug',
                            9 =>'Sep',
                            10 =>'Oct',
                            11 =>'Nov',
                            12 =>'Dec',
                            1 =>'Jan',
                            2 =>'Feb',
                            3 =>'Mar'
                    ];
                    $monthlyData = [];
                    foreach ($months as $index => $name) {
                        $monthlyData['income'][$name] = 0;
                        $monthlyData['expense'][$name] = 0;
                        $monthlyData['payroll'][$name] = 0;
                        foreach ($monthlyTrends as $monthlyTrend) {
                            if ($monthlyTrend['month'] === $index && $monthlyTrend['category'] == FinancialTransaction::CATEGORY_INCOME) {
                                $monthlyData['income'][$name] = (float)$monthlyTrend['total_amount'];
                            }
                            if ($monthlyTrend['month'] == $index && $monthlyTrend['category'] == FinancialTransaction::CATEGORY_EXPENSE) {
                                $monthlyData['expense'][$name] = (float)$monthlyTrend['total_amount'];
                            }
                            if ($monthlyTrend['month'] == $index && $monthlyTrend['category'] == FinancialTransaction::CATEGORY_PAYROLL) {
                                $monthlyData['payroll'][$name] = (float)$monthlyTrend['total_amount'];
                            }
                        }
                    }
                    echo Highcharts::widget([
                        'options' => [
                            'title' => ['text' => 'Monthly Transactions'],
                            'chart' => [
                                'type' => 'spline',
                                'height' => 400
                            ],
                            'xAxis' => [
                                'categories' => array_values($months),
                                'title' => ['text' => 'Month']
                            ],
                            'yAxis' => [
                                'title' => ['text' => 'Amount (LKR)'],
                                'labels' => [
                                    'formatter' => new \yii\web\JsExpression('function() { 
                                        return "LKR " + Highcharts.numberFormat(this.value, 0);
                                    }')
                                ]
                            ],
                            'tooltip' => [
                                'shared' => true,
                                'crosshairs' => true,
                                'formatter' => new \yii\web\JsExpression('function() {
                                    return "<b>" + this.x + "</b><br/>" +
                                           this.series.name + ": LKR " + Highcharts.numberFormat(this.y, 0);
                                }')
                            ],
                            'plotOptions' => [
                                'spline' => [
                                    'marker' => [
                                        'enabled' => true
                                    ],
                                    'lineWidth' => 3
                                ]
                            ],
                            'series' => [
                                [
                                    'name' => 'Income',
                                    'color' => '#28a745',
                                    'data' => array_values($monthlyData['income'] ?? array_fill(0, 12, 0))
                                ],
                                [
                                    'name' => 'Expenses',
                                    'color' => '#dc3545',
                                    'data' => array_values($monthlyData['expense'] ?? array_fill(0, 12, 0))
                                ],
                                [
                                    'name' => 'Payroll',
                                    'color' => '#0dcaf0',
                                    'data' => array_values($monthlyData['payroll'] ?? array_fill(0, 12, 0))
                                ]
                            ],
                            'credits' => ['enabled' => false]
                        ]
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Summary Grid -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tax Summary</h3>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => new \yii\data\ArrayDataProvider([
                            'allModels' => $taxSummary,
                            'pagination' => false,
                        ]),
                        'columns' => [
                            'tax_code',
                            [
                                'attribute' => 'taxable_amount',
                                'format' => ['currency', 'LKR'],
                            ],
                            [
                                'attribute' => 'tax_amount',
                                'format' => ['currency', 'LKR'],
                            ],
                            'payment_status',
                        ],
                        'responsive' => true,
                        'hover' => true,
                        'panel' => [
                            'type' => GridView::TYPE_INFO,
                            'heading' => false
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Transactions</h3>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => $recentTransactions,
                        'columns' => [
                            'transaction_date:date',
                            'description',
                            [
                                    'attribute' => 'category',
                                    'value' => function ($model) {
                                        return FinancialTransaction::CATEGORIES[$model->category];
                                    }
                            ],
                            [
                                'attribute' => 'amount_lkr',
                                'format' => ['currency', 'LKR'],
                                'contentOptions' => function ($model) {
                                    return ['class' => $model->category === FinancialTransaction::CATEGORY_INCOME ? 'text-success' : 'text-danger'];
                                }
                            ],
                            'status',
                        ],
                        'responsive' => true,
                        'hover' => true,
                        'panel' => [
                            'type' => GridView::TYPE_DEFAULT,
                            'heading' => false
                        ],
                        'toolbar' => false
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
