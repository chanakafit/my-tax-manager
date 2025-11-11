<?php

use yii\helpers\Html;

$this->title = 'Create Bank Account';
$this->params['breadcrumbs'][] = ['label' => 'My Bank Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="owner-bank-account-create">
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

