<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var int $pendingCount */
/** @var array $suggestions */
/** @var bool $showDetails */

$this->registerCss("
.expense-health-check-widget {
    margin-bottom: 20px;
}
.health-check-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.suggestion-item {
    padding: 10px;
    border-left: 3px solid #ffc107;
    margin-bottom: 10px;
    background: #fff9e6;
    border-radius: 3px;
}
.suggestion-item:hover {
    background: #fff3cd;
}
.suggestion-meta {
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
}
.no-suggestions {
    color: #28a745;
    padding: 10px;
    background: #d4edda;
    border-radius: 3px;
    text-align: center;
}
.btn-health-actions {
    display: flex;
    gap: 5px;
    margin-top: 5px;
}
");
?>

<div class="expense-health-check-widget">
    <div class="card">
        <div class="card-header bg-warning">
            <div class="health-check-header">
                <h4 class="mb-0">
                    <i class="fas fa-heartbeat"></i> Expense Health Check
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge badge-danger"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </h4>
                <div>
                    <?= Html::a('View All', ['expense-suggestion/index'], ['class' => 'btn btn-sm btn-light']) ?>
                    <?= Html::a('<i class="fas fa-eye-slash"></i> Ignored', ['expense-suggestion/ignored'], ['class' => 'btn btn-sm btn-secondary']) ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if ($pendingCount === 0): ?>
                <div class="no-suggestions">
                    <i class="fas fa-check-circle"></i>
                    <strong>All Clear!</strong> No missing expenses detected.
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-3">
                    <strong><?= $pendingCount ?></strong> possible missing expense(s) detected based on historical patterns.
                </div>

                <?php if ($showDetails && !empty($suggestions)): ?>
                    <?php foreach ($suggestions as $suggestion): ?>
                        <div class="suggestion-item">
                            <strong>
                                <?= Html::encode($suggestion->expenseCategory->name ?? 'Unknown Category') ?>
                                -
                                <?= Html::encode($suggestion->vendor->name ?? 'Unknown Vendor') ?>
                            </strong>
                            <div class="suggestion-meta">
                                <span><i class="fas fa-calendar"></i> <?= date('F Y', strtotime($suggestion->suggested_month)) ?></span>
                                &nbsp;|&nbsp;
                                <span><i class="fas fa-money-bill"></i> Avg: <?= Yii::$app->formatter->asCurrency($suggestion->avg_amount_lkr, 'LKR') ?></span>
                                &nbsp;|&nbsp;
                                <span title="Pattern found in these months">
                                    <i class="fas fa-chart-line"></i>
                                    Pattern: <?= count($suggestion->getPatternMonthsArray()) ?> months
                                </span>
                            </div>
                            <div class="btn-health-actions">
                                <?= Html::a('<i class="fas fa-plus"></i> Add Expense',
                                    ['expense-suggestion/create-expense', 'id' => $suggestion->id],
                                    ['class' => 'btn btn-success btn-sm']) ?>
                                <?= Html::button('<i class="fas fa-times"></i> Ignore', [
                                    'class' => 'btn btn-secondary btn-sm btn-ignore-suggestion',
                                    'data-id' => $suggestion->id,
                                    'data-category' => $suggestion->expenseCategory->name ?? '',
                                    'data-vendor' => $suggestion->vendor->name ?? '',
                                    'data-month' => date('M Y', strtotime($suggestion->suggested_month)),
                                ]) ?>
                                <?= Html::a('<i class="fas fa-eye"></i> Details',
                                    ['expense-suggestion/view', 'id' => $suggestion->id],
                                    ['class' => 'btn btn-info btn-sm']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($pendingCount > count($suggestions)): ?>
                        <div class="text-center mt-3">
                            <?= Html::a('View All ' . $pendingCount . ' Suggestions',
                                ['expense-suggestion/index'],
                                ['class' => 'btn btn-warning']) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Register the ignore button handler JavaScript
$this->registerJs("
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
                                <option value='permanent'>Permanent (ignore this pattern forever)</option>
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

