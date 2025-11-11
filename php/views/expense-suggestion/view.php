<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\ExpenseSuggestion $model */

$this->title = 'Expense Suggestion #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Expense Suggestions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="expense-suggestion-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->status === \app\models\ExpenseSuggestion::STATUS_PENDING): ?>
            <?= Html::a('Create Expense', ['create-expense', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
        <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => $model->getStatusLabel(),
            ],
            [
                'attribute' => 'expense_category_id',
                'value' => $model->expenseCategory->name ?? 'N/A',
            ],
            [
                'attribute' => 'vendor_id',
                'value' => $model->vendor->name ?? 'N/A',
            ],
            [
                'attribute' => 'suggested_month',
                'format' => ['date', 'php:F Y'],
            ],
            [
                'label' => 'Pattern Months',
                'format' => 'raw',
                'value' => function ($model) {
                    $months = $model->getPatternMonthsArray();
                    if (empty($months)) {
                        return 'N/A';
                    }
                    $formatted = array_map(function($m) {
                        return date('F Y', strtotime($m));
                    }, $months);
                    return '<ul><li>' . implode('</li><li>', $formatted) . '</li></ul>';
                },
            ],
            [
                'attribute' => 'avg_amount_lkr',
                'format' => ['currency', 'LKR'],
            ],
            [
                'attribute' => 'last_expense_id',
                'format' => 'raw',
                'value' => $model->last_expense_id
                    ? Html::a("Expense #{$model->last_expense_id}", ['expense/view', 'id' => $model->last_expense_id])
                    : 'N/A',
            ],
            [
                'attribute' => 'ignored_reason',
                'visible' => !empty($model->ignored_reason),
            ],
            [
                'attribute' => 'generated_at',
                'format' => ['datetime', 'php:M d, Y H:i'],
            ],
            [
                'attribute' => 'actioned_at',
                'format' => ['datetime', 'php:M d, Y H:i'],
                'visible' => !empty($model->actioned_at),
            ],
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:M d, Y H:i'],
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['datetime', 'php:M d, Y H:i'],
            ],
        ],
    ]) ?>

    <?php if ($model->lastExpense): ?>
        <h3>Last Recorded Expense Details</h3>
        <?= DetailView::widget([
            'model' => $model->lastExpense,
            'attributes' => [
                'id',
                'title',
                'expense_date:date',
                'amount_lkr:currency',
                'description:ntext',
                'payment_method',
            ],
        ]) ?>
    <?php endif; ?>
</div>

