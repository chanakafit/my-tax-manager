<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\PaysheetSuggestion */

$this->title = 'Paysheet Suggestion: ' . $model->employee->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Paysheet Suggestions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paysheet-suggestion-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->canEdit()): ?>
            <?= Html::a('<i class="glyphicon glyphicon-ok"></i> Approve', ['approve', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data-confirm' => 'Are you sure you want to approve this paysheet?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('<i class="glyphicon glyphicon-pencil"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-remove"></i> Reject', ['reject', 'id' => $model->id], [
                'class' => 'btn btn-warning',
                'data-confirm' => 'Are you sure you want to reject this suggestion?',
                'data-method' => 'post',
            ]) ?>
        <?php endif; ?>

        <?php if ($model->canDelete()): ?>
            <?= Html::a('<i class="glyphicon glyphicon-trash"></i> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'Are you sure you want to delete this item?',
                'data-method' => 'post',
            ]) ?>
        <?php endif; ?>

        <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> Back', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'employee_id',
                'format' => 'raw',
                'value' => Html::a(
                    $model->employee->fullName,
                    ['employee/view', 'id' => $model->employee_id],
                    ['target' => '_blank']
                ),
            ],
            [
                'attribute' => 'suggested_month',
                'label' => 'Pay Period',
                'value' => $model->formattedMonth,
            ],
            [
                'attribute' => 'basic_salary',
                'format' => ['decimal', 2],
                'value' => $model->basic_salary,
            ],
            [
                'attribute' => 'allowances',
                'format' => ['decimal', 2],
                'value' => $model->allowances,
            ],
            [
                'attribute' => 'deductions',
                'format' => ['decimal', 2],
                'value' => $model->deductions,
            ],
            [
                'attribute' => 'tax_amount',
                'format' => ['decimal', 2],
                'value' => $model->tax_amount,
            ],
            [
                'attribute' => 'net_salary',
                'format' => ['decimal', 2],
                'value' => $model->net_salary,
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => $model->statusLabel,
            ],
            'notes:ntext',
            'generated_at:datetime',
            'actioned_at:datetime',
            [
                'attribute' => 'actioned_by',
                'value' => $model->actionedBy ? $model->actionedBy->username : null,
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>

