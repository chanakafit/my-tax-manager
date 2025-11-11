<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PaymentTerm $model */

$this->title = 'Create Payment Term';
$this->params['breadcrumbs'][] = ['label' => 'Payment Terms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-term-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
