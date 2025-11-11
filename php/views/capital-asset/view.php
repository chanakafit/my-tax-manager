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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Capital Allowances</h4>
                    <?php if ($model->status === 'active' && count($allowances) < 5): ?>
                    <div>
                        <?php $nextYear = date('Y'); ?>
                        <?= Html::beginForm(['calculate-allowance', 'id' => $model->id], 'post', ['class' => 'd-flex']); ?>
                            <?= Html::dropDownList('taxYear', null,
                                array_combine(range($nextYear-2, $nextYear), range($nextYear-2, $nextYear)),
                                ['class' => 'form-control me-2', 'prompt' => 'Select Tax Year']
                            ) ?>
                            <?= Html::submitButton('Calculate Allowance', ['class' => 'btn btn-primary']) ?>
                        <?= Html::endForm(); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => new ArrayDataProvider([
                            'allModels' => $allowances,
                            'pagination' => false,
                        ]),
                        'columns' => [
                            'tax_year',
                            'year_number',
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
                        ],
                        'showFooter' => true,
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
