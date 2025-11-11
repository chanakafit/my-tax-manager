<?php

use app\models\ExpenseSuggestion;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ignored Expense Suggestions';
$this->params['breadcrumbs'][] = ['label' => 'Expense Suggestions', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Ignored';

$this->registerCss("
.suggestion-actions {
    display: flex;
    gap: 5px;
}
");
?>

<div class="expense-suggestion-ignored">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-list"></i> Active Suggestions', ['index'], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Back to Dashboard', ['site/dashboard'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>Ignored Suggestions:</strong> These suggestions have been ignored (temporarily or permanently).
        <ul class="mb-0">
            <li><strong>Temporary:</strong> Will reappear after 2 months</li>
            <li><strong>Permanent:</strong> Hidden forever (unless you add same category+vendor expense again)</li>
        </ul>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'panel' => [
            'heading' => '<h3 class="panel-title"><i class="fas fa-eye-slash"></i> Ignored Suggestions</h3>',
            'type' => 'warning',
        ],
        'columns' => [
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getStatusLabel();
                },
                'filter' => [
                    ExpenseSuggestion::STATUS_IGNORED_TEMPORARY => 'Temporary',
                    ExpenseSuggestion::STATUS_IGNORED_PERMANENT => 'Permanent',
                ],
            ],
            [
                'attribute' => 'suggested_month',
                'format' => ['date', 'php:M Y'],
                'label' => 'Month',
            ],
            [
                'attribute' => 'expense_category_id',
                'value' => 'expenseCategory.name',
                'label' => 'Category',
            ],
            [
                'attribute' => 'vendor_id',
                'value' => 'vendor.name',
                'label' => 'Vendor',
            ],
            [
                'attribute' => 'avg_amount_lkr',
                'format' => ['currency', 'LKR'],
                'label' => 'Avg Amount',
            ],
            [
                'label' => 'Pattern',
                'format' => 'raw',
                'value' => function ($model) {
                    $months = $model->getPatternMonthsArray();
                    if (empty($months)) {
                        return '-';
                    }
                    $formatted = array_map(function($m) {
                        return date('M Y', strtotime($m));
                    }, $months);
                    return implode(', ', $formatted);
                },
            ],
            [
                'attribute' => 'ignored_reason',
                'label' => 'Reason',
                'format' => 'raw',
                'value' => function($model) {
                    return $model->ignored_reason
                        ? Html::tag('span', Html::encode($model->ignored_reason), ['class' => 'text-muted', 'style' => 'font-size: 0.9em;'])
                        : Html::tag('span', 'No reason provided', ['class' => 'text-muted fst-italic']);
                },
            ],
            [
                'attribute' => 'actioned_at',
                'format' => ['datetime', 'php:M d, Y H:i'],
                'label' => 'Ignored At',
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{view} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-info btn-sm',
                            'title' => 'View Details'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'title' => 'Delete',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this ignored suggestion?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <div class="alert alert-info mt-3">
        <strong>Note:</strong>
        <ul class="mb-0">
            <li>Temporary ignores will automatically return to active suggestions after 2 months</li>
            <li>Permanent ignores will be automatically reset if you add an expense for the same category + vendor</li>
            <li>You can manually delete any ignored suggestion using the delete button</li>
        </ul>
    </div>
</div>

