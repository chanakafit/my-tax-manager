<?php

use yii\helpers\Html;

$this->title = 'Add Capital Asset';
$this->params['breadcrumbs'][] = ['label' => 'Capital Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="capital-asset-create">
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
