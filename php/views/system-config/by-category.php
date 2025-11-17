<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\SystemConfigSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $categories */
/** @var string|null $selectedCategory */

$this->title = 'System Configuration' . ($selectedCategory ? ' - ' . ucwords(str_replace('_', ' ', $selectedCategory)) : '');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="system-config-by-category">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="mb-3">
        <div class="btn-group" role="group">
            <?= Html::a('All Categories', ['by-category'], ['class' => 'btn btn-' . (empty($selectedCategory) ? 'primary' : 'outline-primary')]) ?>
            <?php foreach ($categories as $category): ?>
                <?= Html::a(ucwords(str_replace('_', ' ', $category)), ['by-category', 'category' => $category], [
                    'class' => 'btn btn-' . ($selectedCategory === $category ? 'primary' : 'outline-primary')
                ]) ?>
            <?php endforeach; ?>
        </div>
    </div>

    <p>
        <?= Html::a('<i class="fas fa-sliders-h"></i> Bulk Edit', ['bulk-update', 'category' => $selectedCategory], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fas fa-list"></i> List View', ['index'], ['class' => 'btn btn-secondary']) ?>
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
                    if (strlen($model->config_value) > 80) {
                        return substr($model->config_value, 0, 80) . '...';
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

