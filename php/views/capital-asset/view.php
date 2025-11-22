<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\BGridView as GridView;
use yii\data\ArrayDataProvider;

$this->title = $model->asset_name;
$this->params['breadcrumbs'][] = ['label' => 'Capital Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="capital-asset-view">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Asset Details</h4>
                    <?php if ($model->status === 'active'): ?>
                        <?= Html::a('Dispose Asset', ['dispose', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'asset_name',
                            'description:ntext',
                            'purchase_date:date',
                            [
                                'attribute' => 'purchase_cost',
                                'format' => ['decimal', 2],
                            ],
                            'initial_tax_year',
                            [
                                'attribute' => 'current_written_down_value',
                                'format' => ['decimal', 2],
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => Html::tag('span',
                                    ucfirst($model->status),
                                    ['class' => 'badge bg-' . ($model->status === 'active' ? 'success' : 'secondary')]
                                ),
                            ],
                            'disposal_date:date',
                            [
                                'attribute' => 'disposal_value',
                                'format' => ['decimal', 2],
                                'visible' => $model->status === 'disposed',
                            ],
                            'notes:ntext',
                            [
                                'attribute' => 'receipt_file',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->receipt_file) {
                                        $url = '/' . $model->receipt_file;
                                        $ext = strtolower(pathinfo($model->receipt_file, PATHINFO_EXTENSION));
                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                            return Html::img($url, ['style' => 'max-width:300px;']);
                                        } elseif ($ext === 'pdf') {
                                            return Html::a(
                                                '<i class="fas fa-file-pdf"></i> View Receipt',
                                                $url,
                                                ['class' => 'btn btn-info', 'target' => '_blank']
                                            );
                                        }
                                    }
                                    return '<span class="text-muted">No receipt uploaded</span>';
                                },
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-3">Capital Allowances</h4>
                    <?php if ($model->status === 'active' && count($allowances) < 5): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Original Asset Value:</strong> <?= Yii::$app->formatter->asCurrency($model->purchase_cost, 'LKR') ?> &nbsp;|&nbsp;
                        <strong>Current Written Down Value:</strong> <?= Yii::$app->formatter->asCurrency($model->current_written_down_value, 'LKR') ?>
                    </div>
                    <div>
                        <?php $nextYear = date('Y'); ?>
                        <?= Html::beginForm(['calculate-allowance', 'id' => $model->id], 'post', ['class' => 'row g-2']); ?>
                            <div class="col-md-4">
                                <?= Html::dropDownList('taxYear', null,
                                    array_combine(range($nextYear-2, $nextYear+1), range($nextYear-2, $nextYear+1)),
                                    ['class' => 'form-control', 'prompt' => 'Select Tax Year']
                                ) ?>
                            </div>
                            <div class="col-md-4">
                                <?= Html::textInput('percentage', '20', [
                                    'class' => 'form-control',
                                    'placeholder' => 'Percentage (1-100)',
                                    'type' => 'number',
                                    'min' => '0.01',
                                    'max' => '100',
                                    'step' => '0.01',
                                    'required' => true
                                ]) ?>
                                <small class="text-muted">% of original asset value (<?= Yii::$app->formatter->asCurrency($model->purchase_cost, 'LKR') ?>)</small>
                            </div>
                            <div class="col-md-4">
                                <?= Html::submitButton('<i class="fas fa-calculator"></i> Add Allowance', ['class' => 'btn btn-primary w-100']) ?>
                            </div>
                        <?= Html::endForm(); ?>
                    </div>
                    <?php elseif (count($allowances) >= 5): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Maximum 5 years of capital allowances have been claimed for this asset.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($allowances)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No capital allowances have been added yet. Use the form above to add your first allowance.
                        </div>
                    <?php else: ?>
                        <?= GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                'allModels' => $allowances,
                                'pagination' => false,
                            ]),
                            'columns' => [
                                'tax_year',
                                [
                                    'attribute' => 'year_number',
                                    'label' => 'Year',
                                    'contentOptions' => ['class' => 'text-center'],
                                ],
                                [
                                    'attribute' => 'percentage_claimed',
                                    'label' => '% Claimed',
                                    'format' => 'raw',
                                    'contentOptions' => ['class' => 'text-center'],
                                    'value' => function($model) {
                                        $percentage = $model->percentage_claimed ? $model->percentage_claimed : 20.0;
                                        return number_format($percentage, 2) . '%';
                                    },
                                ],
                                [
                                    'attribute' => 'allowance_amount',
                                    'label' => 'Allowance',
                                    'format' => ['decimal', 2],
                                    'contentOptions' => ['class' => 'text-end'],
                                    'footer' => '<strong>Total: ' . Yii::$app->formatter->asCurrency(array_sum(array_column($allowances, 'allowance_amount')), 'LKR') . '</strong>',
                                    'footerOptions' => ['class' => 'text-end'],
                                ],
                                [
                                    'attribute' => 'written_down_value',
                                    'label' => 'Written Down Value',
                                    'format' => ['decimal', 2],
                                    'contentOptions' => ['class' => 'text-end'],
                                    'footer' => '<strong>Current: ' . Yii::$app->formatter->asCurrency($model->current_written_down_value, 'LKR') . '</strong>',
                                    'footerOptions' => ['class' => 'text-end'],
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{delete}',
                                    'header' => 'Actions',
                                    'buttons' => [
                                        'delete' => function ($url, $model, $key) {
                                            return Html::a('<i class="fas fa-trash"></i>',
                                                ['delete-allowance', 'id' => $model->id],
                                                [
                                                    'class' => 'btn btn-sm btn-danger',
                                                    'title' => 'Delete Allowance',
                                                    'data-confirm' => 'Are you sure you want to delete this capital allowance? This will recalculate all subsequent allowances and the written down value.',
                                                    'data-method' => 'post',
                                                ]
                                            );
                                        },
                                    ],
                                    'contentOptions' => ['class' => 'text-center'],
                                ],
                            ],
                            'showFooter' => true,
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
