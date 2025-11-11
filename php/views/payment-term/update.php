<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PaymentTerm $model */

$this->title = 'Update Payment Term: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Payment Terms', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="payment-term-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
