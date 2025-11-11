<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Liabilities';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="liability-index">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3><?= Html::encode($this->title) ?></h3>
            <?= Html::a('<i class="fas fa-plus"></i> Create Liability', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'lender_name',
                    [
                        'attribute' => 'liability_type',
                        'value' => function ($model) {
                            return '<span class="badge bg-' . ($model->liability_type == 'business' ? 'primary' : 'info') . '">'
                                . ucfirst($model->liability_type) . '</span>';
                        },
                        'format' => 'raw',
                        'filter' => ['business' => 'Business', 'personal' => 'Personal'],
                    ],
                    [
                        'attribute' => 'liability_category',
                        'value' => function ($model) {
                            return ucfirst($model->liability_category);
                        },
                        'filter' => ['loan' => 'Loan', 'leasing' => 'Leasing'],
                    ],
                    [
                        'attribute' => 'original_amount',
                        'value' => function ($model) {
                            return Yii::$app->formatter->asCurrency($model->original_amount, 'LKR');
                        },
                    ],
                    'start_date:date',
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $class = $model->status == 'active' ? 'success' : 'secondary';
                            return '<span class="badge bg-' . $class . '">' . ucfirst($model->status) . '</span>';
                        },
                        'format' => 'raw',
                        'filter' => ['active' => 'Active', 'settled' => 'Settled'],
                    ],
                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>
        </div>
    </div>
</div>

