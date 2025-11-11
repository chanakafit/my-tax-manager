<?php

use yii\helpers\Html;

$this->title = 'Update Tax Record: ' . $model->tax_code;
$this->params['breadcrumbs'][] = ['label' => 'Tax Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->tax_code, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="tax-record-update">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>
