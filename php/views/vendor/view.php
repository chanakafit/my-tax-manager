<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Vendor $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Vendors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="vendor-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'contact',
            'email:email',
            'address:ntext',
            [
                    'attribute' => 'created_by',
                    'value' => function ($model) {
                        /** @var \app\models\Vendor $model */
                        return $model->createdBy ? $model->createdBy->username : null;
                    }
            ],
            [
                    'attribute' => 'updated_by',
                    'value' => function ($model) {
                        /** @var \app\models\Vendor $model */
                        return $model->updatedBy ? $model->updatedBy->username : null;
                    }
            ],
        ],
    ]) ?>

</div>
