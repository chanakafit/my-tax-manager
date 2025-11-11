<?php

use yii\helpers\Html;
use app\widgets\BGridView as GridView;
use miloschuman\highcharts\Highcharts;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $year int */
/* @var $taxesByType array */
/* @var $unpaidTaxes array */

$this->title = 'Tax Report';
?>

<div class="tax-record-report">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <?= Html::dropDownList(
                'year',
                $year,
                array_combine(range(date('Y'), date('Y') - 5), range(date('Y'), date('Y') - 5)),
                ['class' => 'form-control', 'id' => 'year-selector']
            ) ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <?= Highcharts::widget([
                'options' => [
                    'title' => ['text' => 'Tax Distribution by Type'],
                    'plotOptions' => [
                        'pie' => [
                            'allowPointSelect' => true,
                            'cursor' => 'pointer',
                            'dataLabels' => [
                                'enabled' => true,
                                'format' => '<b>{point.name}</b>: {point.percentage:.1f} %'
                            ]
                        ]
                    ],
                    'series' => [
                        [
                            'type' => 'pie',
                            'name' => 'Tax Amount',
                            'data' => array_map(function ($tax) {
                                return [$tax['tax_type'], (float)$tax['total_tax']];
                            }, $taxesByType)
                        ]
                    ]
                ]
            ]); ?>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Tax Summary</h3>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Tax Type</th>
                            <th>Total Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($taxesByType as $tax): ?>
                            <tr>
                                <td><?= Html::encode($tax['tax_type']) ?></td>
                                <td><?= Yii::$app->formatter->asCurrency($tax['total_tax']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>Total</th>
                            <th><?= Yii::$app->formatter->asCurrency(array_sum(array_column($taxesByType,
                                    'total_tax'))) ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title">Unpaid Taxes</h3>
                </div>
                <div class="panel-body">
                    <?= GridView::widget([
                        'dataProvider' => new \yii\data\ArrayDataProvider([
                            'allModels' => $unpaidTaxes,
                            'pagination' => false
                        ]),
                        'columns' => [
                            'tax_type',
                            'tax_period_start:date',
                            'tax_period_end:date',
                            [
                                'attribute' => 'tax_amount',
                                'format' => 'currency'
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{record-payment}',
                                'buttons' => [
                                    'record-payment' => function ($url, $model) {
                                        return Html::a(
                                            '<span class="glyphicon glyphicon-credit-card"></span>',
                                            ['record-payment', 'id' => $model->id],
                                            ['title' => 'Record Payment']
                                        );
                                    }
                                ]
                            ]
                        ],
                        'panel' => [
                            'type' => GridView::TYPE_DANGER,
                            'heading' => '<h3 class="panel-title">Pending Tax Payments</h3>',
                        ],
                        'toolbar' => false
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
$('#year-selector').change(function() {
    window.location.href = '/tax-record/report?year=' + $(this).val();
});
JS;
$this->registerJs($js);
?>
