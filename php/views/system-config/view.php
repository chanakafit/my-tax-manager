<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\SystemConfig $model */

$this->title = $model->config_key;
$this->params['breadcrumbs'][] = ['label' => 'System Configuration', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="system-config-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="fas fa-edit"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php if ($model->is_editable): ?>
            <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        <?= Html::a('<i class="fas fa-list"></i> Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
        <?= Html::a('<i class="fas fa-sliders-h"></i> Bulk Edit', ['bulk-update'], ['class' => 'btn btn-info']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'config_key',
            [
                'attribute' => 'config_value',
                'format' => 'ntext',
            ],
            'config_type',
            'category',
            'description',
            [
                'attribute' => 'is_editable',
                'format' => 'boolean',
            ],
            'created_at:datetime',
            'updated_at:datetime',
            'created_by',
            'updated_by',
        ],
    ]) ?>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Parsed Value</h5>
        </div>
        <div class="card-body">
            <?php
            $parsedValue = \app\models\SystemConfig::get($model->config_key);
            if (is_array($parsedValue)) {
                echo '<pre>' . Html::encode(print_r($parsedValue, true)) . '</pre>';
            } elseif (is_bool($parsedValue)) {
                echo '<span class="badge badge-' . ($parsedValue ? 'success' : 'danger') . '">' .
                     ($parsedValue ? 'True' : 'False') . '</span>';
            } else {
                echo '<code>' . Html::encode($parsedValue) . '</code>';
            }
            ?>
        </div>
    </div>

</div>

