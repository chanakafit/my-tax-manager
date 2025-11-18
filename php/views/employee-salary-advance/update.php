<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\EmployeeSalaryAdvance $model */

$this->title = 'Update Salary Advance: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Salary Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="employee-salary-advance-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

