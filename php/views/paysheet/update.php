<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Paysheet */

$this->title = 'Update Paysheet: ' . $model->employee->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Paysheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->employee->fullName, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="paysheet-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
