<?php

use yii\helpers\Html;

$this->title = 'Update Liability: ' . $model->lender_name;
$this->params['breadcrumbs'][] = ['label' => 'Liabilities', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->lender_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="liability-update">
    <div class="card">
        <div class="card-header">
            <h3><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>

