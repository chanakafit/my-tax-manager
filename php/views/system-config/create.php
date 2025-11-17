<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SystemConfig $model */

$this->title = 'Create System Configuration';
$this->params['breadcrumbs'][] = ['label' => 'System Configuration', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="system-config-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

