<?php

use yii\helpers\Html;

$this->title = 'Create Liability';
$this->params['breadcrumbs'][] = ['label' => 'Liabilities', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="liability-create">
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

