<?php

use yii\helpers\Html;

$this->title = 'Update Bank Account: ' . $model->account_name;
$this->params['breadcrumbs'][] = ['label' => 'My Bank Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->account_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="owner-bank-account-update">
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

