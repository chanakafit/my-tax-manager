<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\EmployeeSalaryAdvance $model */

$this->title = 'Create Salary Advance';
$this->params['breadcrumbs'][] = ['label' => 'Salary Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employee-salary-advance-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

