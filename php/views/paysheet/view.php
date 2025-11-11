<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Paysheet */

$this->title = 'View Paysheet';
$this->params['breadcrumbs'][] = ['label' => 'Paysheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paysheet-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="fas fa-pencil-alt"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php if ($model->status === 'pending'): ?>
            <?= Html::a('<i class="fas fa-money-bill"></i> Mark as Paid', ['mark-as-paid', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'toggle' => 'tooltip',
                    'title' => 'Mark this paysheet as paid'
                ]
            ]) ?>
        <?php endif; ?>
    </p>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Employee Details</h5>
            <p class="card-text">
                <strong>Name:</strong> <?= Html::encode($model->employee->fullName) ?><br>
                <strong>Position:</strong> <?= Html::encode($model->employee->position) ?><br>
                <strong>Department:</strong> <?= Html::encode($model->employee->department) ?>
            </p>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Paysheet Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Pay Period:</strong><br>
                        From: <?= Yii::$app->formatter->asDate($model->pay_period_start) ?><br>
                        To: <?= Yii::$app->formatter->asDate($model->pay_period_end) ?></p>
                    <p><strong>Payment Date:</strong> <?= Yii::$app->formatter->asDate($model->payment_date) ?></p>
                </div>
                <div class="col-md-6">
                    <p>
                        <strong>Status:</strong>
                        <span class="badge bg-<?= $model->status === 'paid' ? 'success' : 'warning' ?>">
                            <?= Html::encode(ucfirst($model->status)) ?>
                        </span>
                    </p>
                    <p><strong>Payment Method:</strong> <?= Html::encode($model->payment_method) ?></p>
                    <?php if ($model->payment_reference): ?>
                        <p><strong>Reference:</strong> <?= Html::encode($model->payment_reference) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Salary Breakdown</h5>
            <table class="table">
                <tr>
                    <td>Basic Salary</td>
                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($model->basic_salary) ?></td>
                </tr>
                <?php if ($model->allowances): ?>
                <tr>
                    <td>Allowances</td>
                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($model->allowances) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($model->deductions): ?>
                <tr>
                    <td>Deductions</td>
                    <td class="text-end">-<?= Yii::$app->formatter->asCurrency($model->deductions) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($model->tax_amount): ?>
                <tr>
                    <td>Tax</td>
                    <td class="text-end">-<?= Yii::$app->formatter->asCurrency($model->tax_amount) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="table-active fw-bold">
                    <td>Net Salary</td>
                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($model->net_salary) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <?php if ($model->notes): ?>
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Notes</h5>
            <p class="card-text"><?= Html::encode($model->notes) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-3">
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this paysheet?',
                'method' => 'post',
            ],
        ]) ?>
    </div>
</div>
