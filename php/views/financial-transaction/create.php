<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\FinancialTransaction $model */

$this->title = 'Create Financial Transaction';
$this->params['breadcrumbs'][] = ['label' => 'Financial Transactions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="financial-transaction-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
