<?php
use yii\helpers\Html;
use app\widgets\BGridView as GridView;
use yii\data\ArrayDataProvider;

$this->title = 'Capital Assets';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="capital-asset-index">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
            <div>
                <?= Html::a('<i class="fas fa-plus"></i> Add Capital Asset', ['create'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $assets,
                    'pagination' => false,
                ]),
                'columns' => [
                    'asset_name',
                    [
                        'attribute' => 'purchase_date',
                        'format' => 'date',
                    ],
                    [
                        'attribute' => 'purchase_cost',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'current_written_down_value',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::tag('span',
                                ucfirst($model->status),
                                ['class' => 'badge bg-' . ($model->status === 'active' ? 'success' : 'secondary')]
                            );
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {dispose}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a(
                                    '<i class="fas fa-eye"></i>',
                                    ['view', 'id' => $model->id],
                                    ['class' => 'btn btn-sm btn-info']
                                );
                            },
                            'dispose' => function ($url, $model) {
                                if ($model->status === 'active') {
                                    return Html::a(
                                        '<i class="fas fa-trash"></i>',
                                        ['dispose', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-sm btn-danger ms-1',
                                            'title' => 'Dispose Asset',
                                        ]
                                    );
                                }
                                return '';
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
