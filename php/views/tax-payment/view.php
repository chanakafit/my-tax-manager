<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Tax Payment Details';
$this->params['breadcrumbs'][] = ['label' => 'Tax Years', 'url' => ['tax-year/index']];
$this->params['breadcrumbs'][] = ['label' => 'Tax Year ' . $model->tax_year . '/' . ($model->tax_year + 1), 'url' => ['tax-year/view', 'year' => $model->tax_year]];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);
?>
<div class="tax-payment-view">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
            <div>
                <?= Html::a('<i class="fas fa-pencil"></i> Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'tax_year',
                        'value' => $model->tax_year . '/' . ($model->tax_year + 1),
                    ],
                    [
                        'attribute' => 'payment_type',
                        'value' => ucfirst($model->payment_type) . ($model->quarter ? " (Q{$model->quarter})" : ''),
                    ],
                    'payment_date:date',
                    [
                        'attribute' => 'amount',
                        'format' => ['decimal', 2],
                    ],
                    'reference_number',
                    'notes:ntext',
                    [
                        'attribute' => 'receipt_file',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->receipt_file) {
                                $url = '/' . $model->receipt_file;
                                $ext = strtolower(pathinfo($model->receipt_file, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    return Html::img($url, ['style' => 'max-width:1000px;']);
                                } elseif ($ext == 'pdf') {
                                    return '<embed src="' . $url . '" type="application/pdf" width="1000" height="400px" />';
                                } else {
                                    return Html::a('Download Receipt', $url, ['target' => '_blank', 'class' => 'btn btn-sm btn-info']);
                                }
                            }
                            return '<span class="text-muted">No receipt uploaded</span>';
                        },
                    ],
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>
</div>
