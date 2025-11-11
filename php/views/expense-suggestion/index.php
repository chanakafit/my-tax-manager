<?php

use app\models\ExpenseSuggestion;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Expense Health Check - Suggestions';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
.suggestion-actions {
    display: flex;
    gap: 5px;
}
");

$this->registerJs("
// Handle ignore button click
$(document).on('click', '.btn-ignore-suggestion', function(e) {
    e.preventDefault();
    var suggestionId = $(this).data('id');
    var categoryName = $(this).data('category');
    var vendorName = $(this).data('vendor');
    var month = $(this).data('month');
    
    // Show modal to choose ignore type
    var modalHtml = `
        <div class='modal fade' id='ignoreModal' tabindex='-1'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Ignore Expense Suggestion</h5>
                        <button type='button' class='close' data-dismiss='modal'>&times;</button>
                    </div>
                    <div class='modal-body'>
                        <p><strong>Category:</strong> ` + categoryName + `</p>
                        <p><strong>Vendor:</strong> ` + vendorName + `</p>
                        <p><strong>Month:</strong> ` + month + `</p>
                        <hr>
                        <div class='form-group'>
                            <label>Ignore Type</label>
                            <select class='form-control' id='ignoreType'>
                                <option value='temporary'>Temporary (will reappear after 2 months)</option>
                                <option value='permanent'>Permanent (ignore this pattern)</option>
                            </select>
                        </div>
                        <div class='form-group'>
                            <label>Reason (optional)</label>
                            <textarea class='form-control' id='ignoreReason' rows='3' placeholder='Why are you ignoring this suggestion?'></textarea>
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                        <button type='button' class='btn btn-warning' id='confirmIgnore'>Ignore Suggestion</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    $('#ignoreModal').modal('show');
    
    // Handle confirm ignore
    $('#confirmIgnore').on('click', function() {
        var ignoreType = $('#ignoreType').val();
        var reason = $('#ignoreReason').val();
        
        $.ajax({
            url: '" . Url::to(['expense-suggestion/ignore']) . "',
            type: 'POST',
            data: {
                id: suggestionId,
                ignore_type: ignoreType,
                reason: reason,
                " . Yii::$app->request->csrfParam . ": '" . Yii::$app->request->csrfToken . "'
            },
            success: function(response) {
                $('#ignoreModal').modal('hide');
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while ignoring the suggestion.');
            }
        });
    });
    
    // Clean up modal on close
    $('#ignoreModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
});
", \yii\web\View::POS_READY);
?>

<div class="expense-suggestion-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-eye-slash"></i> View Ignored', ['ignored'], ['class' => 'btn btn-warning']) ?>
            <?= Html::a('Back to Dashboard', ['site/dashboard'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>Active Suggestions:</strong> This page shows pending and added suggestions only.
        Ignored suggestions are shown in a <?= Html::a('separate list', ['ignored'], ['class' => 'alert-link']) ?> to avoid confusion.
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'panel' => [
            'heading' => '<h3 class="panel-title"><i class="fas fa-heartbeat"></i> Expense Suggestions</h3>',
            'type' => 'primary',
        ],
        'columns' => [
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getStatusLabel();
                },
                'filter' => [
                    ExpenseSuggestion::STATUS_PENDING => 'Pending',
                    ExpenseSuggestion::STATUS_ADDED => 'Added',
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
                'attribute' => 'generated_at',
                'format' => ['datetime', 'php:M d, Y H:i'],
                'label' => 'Generated',
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{custom}',
                'buttons' => [
                    'custom' => function ($url, $model, $key) {
                        if ($model->status === ExpenseSuggestion::STATUS_PENDING) {
                            return '<div class="suggestion-actions">' .
                                Html::a('<i class="fas fa-plus"></i> Add',
                                    ['create-expense', 'id' => $model->id],
                                    ['class' => 'btn btn-success btn-sm', 'title' => 'Create Expense']) .
                                Html::button('<i class="fas fa-times"></i> Ignore', [
                                    'class' => 'btn btn-warning btn-sm btn-ignore-suggestion',
                                    'data-id' => $model->id,
                                    'data-category' => $model->expenseCategory->name ?? '',
                                    'data-vendor' => $model->vendor->name ?? '',
                                    'data-month' => date('M Y', strtotime($model->suggested_month)),
                                    'title' => 'Ignore Suggestion'
                                ]) .
                                '</div>';
                        } else {
                            return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                                'class' => 'btn btn-info btn-sm',
                                'title' => 'View'
                            ]);
                        }
                    },
                ],
            ],
        ],
    ]); ?>
</div>

