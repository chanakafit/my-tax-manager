<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SystemConfig $model */

$this->title = 'Update Configuration: ' . $model->config_key;
$this->params['breadcrumbs'][] = ['label' => 'System Configuration', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->config_key, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="system-config-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

