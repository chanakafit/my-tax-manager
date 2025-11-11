<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->lender_name;
$this->params['breadcrumbs'][] = ['label' => 'Liabilities', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="liability-view">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3><?= Html::encode($this->title) ?></h3>
            <div>
                <?= Html::a('<i class="fas fa-edit"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this liability?',
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    [
                        'attribute' => 'liability_type',
                        'value' => '<span class="badge bg-' . ($model->liability_type == 'business' ? 'primary' : 'info') . '">'
                            . ucfirst($model->liability_type) . '</span>',
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'liability_category',
                        'value' => function($model) {
                            $category = str_replace('_', ' ', $model->liability_category);
                            return ucwords($category);
                        },
                    ],
                    'lender_name',
                    'description:ntext',
                    [
                        'attribute' => 'original_amount',
                        'value' => Yii::$app->formatter->asCurrency($model->original_amount, 'LKR'),
                    ],
                    'start_date:date',
                    'end_date:date',
                    'interest_rate',
                    [
                        'attribute' => 'monthly_payment',
                        'value' => $model->monthly_payment ? Yii::$app->formatter->asCurrency($model->monthly_payment, 'LKR') : 'N/A',
                    ],
                    [
                        'attribute' => 'status',
                        'value' => '<span class="badge bg-' . ($model->status == 'active' ? 'success' : 'secondary') . '">'
                            . ucfirst($model->status) . '</span>',
                        'format' => 'raw',
                    ],
                    'settlement_date:date',
                    'notes:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>
</div>

