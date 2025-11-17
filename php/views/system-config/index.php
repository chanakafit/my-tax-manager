<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\SystemConfigSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'System Configuration';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="system-config-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="fas fa-sliders-h"></i> Bulk Edit Configs', ['bulk-update'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fas fa-plus"></i> Create Config', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-sync"></i> Clear Cache', ['clear-cache'], [
            'class' => 'btn btn-warning',
            'data' => [
                'confirm' => 'Are you sure you want to clear the configuration cache?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'config_key',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a(Html::encode($model->config_key), ['view', 'id' => $model->id]);
                },
            ],
            [
                'attribute' => 'config_value',
                'format' => 'ntext',
                'value' => function($model) {
                    if (strlen($model->config_value) > 100) {
                        return substr($model->config_value, 0, 100) . '...';
                    }
                    return $model->config_value;
                },
            ],
            [
                'attribute' => 'config_type',
                'filter' => [
                    'string' => 'String',
                    'integer' => 'Integer',
                    'boolean' => 'Boolean',
                    'json' => 'JSON',
                    'array' => 'Array',
                ],
            ],
            [
                'attribute' => 'category',
                'filter' => [
                    'business' => 'Business',
                    'banking' => 'Banking',
                    'system' => 'System',
                    'invoice' => 'Invoice',
                ],
            ],
            'description',
            [
                'attribute' => 'is_editable',
                'format' => 'boolean',
                'filter' => [1 => 'Yes', 0 => 'No'],
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>

