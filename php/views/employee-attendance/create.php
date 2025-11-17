<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\EmployeeAttendance $model */

$this->title = 'Add Attendance';
$this->params['breadcrumbs'][] = ['label' => 'Attendance', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employee-attendance-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

