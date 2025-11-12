<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var int $pendingCount */
/** @var array $suggestions */
/** @var bool $showDetails */

$this->registerCss("
.paysheet-health-check-widget {
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
    border-left: 3px solid #f0ad4e;
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

<div class="paysheet-health-check-widget">
    <div class="card">
        <div class="card-header bg-warning">
            <div class="health-check-header">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-check"></i> Paysheet Health Check
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge badge-danger"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </h4>
                <div>
                    <?= Html::a('View All', ['paysheet-suggestion/index'], ['class' => 'btn btn-sm btn-light']) ?>
                    <?= Html::a('<i class="fas fa-history"></i> History', ['paysheet-suggestion/history'], ['class' => 'btn btn-sm btn-secondary']) ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if ($pendingCount === 0): ?>
                <div class="no-suggestions">
                    <i class="fas fa-check-circle"></i>
                    <strong>All Clear!</strong> All employee paysheets are up to date.
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-3">
                    <strong><?= $pendingCount ?></strong> employee paysheet<?= $pendingCount > 1 ? 's' : '' ?> need<?= $pendingCount === 1 ? 's' : '' ?> review and approval.
                </div>

                <?php if ($showDetails && !empty($suggestions)): ?>
                    <?php foreach ($suggestions as $suggestion): ?>
                        <div class="suggestion-item">
                            <strong>
                                <?= Html::encode($suggestion->employee->fullName) ?>
                            </strong>
                            <span class="text-muted"> - <?= Html::encode($suggestion->employee->position) ?></span>
                            <div class="suggestion-meta">
                                <span><i class="fas fa-calendar"></i> <?= $suggestion->formattedMonth ?></span>
                                &nbsp;|&nbsp;
                                <span><i class="fas fa-money-bill"></i> Basic: <?= Yii::$app->formatter->asCurrency($suggestion->basic_salary, 'LKR') ?></span>
                                &nbsp;|&nbsp;
                                <span title="Net salary after deductions and tax">
                                    <i class="fas fa-wallet"></i>
                                    Net: <?= Yii::$app->formatter->asCurrency($suggestion->net_salary, 'LKR') ?>
                                </span>
                                <?php if ($suggestion->allowances > 0): ?>
                                    &nbsp;|&nbsp;
                                    <span class="text-success" title="Allowances">
                                        <i class="fas fa-plus-circle"></i>
                                        +<?= Yii::$app->formatter->asCurrency($suggestion->allowances, 'LKR') ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($suggestion->tax_amount > 0): ?>
                                    &nbsp;|&nbsp;
                                    <span class="text-danger" title="Tax amount">
                                        <i class="fas fa-percentage"></i>
                                        Tax: <?= Yii::$app->formatter->asCurrency($suggestion->tax_amount, 'LKR') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="btn-health-actions">
                                <?= Html::a('<i class="fas fa-check"></i> Approve',
                                    ['paysheet-suggestion/approve', 'id' => $suggestion->id],
                                    [
                                        'class' => 'btn btn-success btn-sm',
                                        'title' => 'Approve and set payment date',
                                    ]) ?>
                                <?= Html::a('<i class="fas fa-edit"></i> Edit',
                                    ['paysheet-suggestion/update', 'id' => $suggestion->id],
                                    ['class' => 'btn btn-primary btn-sm']) ?>
                                <?= Html::button('<i class="fas fa-times"></i> Reject', [
                                    'class' => 'btn btn-warning btn-sm btn-reject-suggestion',
                                    'data-id' => $suggestion->id,
                                    'data-employee' => $suggestion->employee->fullName ?? '',
                                    'data-month' => $suggestion->formattedMonth,
                                ]) ?>
                                <?= Html::a('<i class="fas fa-eye"></i> Details',
                                    ['paysheet-suggestion/view', 'id' => $suggestion->id],
                                    ['class' => 'btn btn-info btn-sm']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($pendingCount > count($suggestions)): ?>
                        <div class="text-center mt-3">
                            <?= Html::a('View All ' . $pendingCount . ' Paysheets',
                                ['paysheet-suggestion/index'],
                                ['class' => 'btn btn-warning']) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Register the reject button handler JavaScript
$this->registerJs("
$(document).on('click', '.btn-reject-suggestion', function(e) {
    e.preventDefault();
    var suggestionId = $(this).data('id');
    var employeeName = $(this).data('employee');
    var month = $(this).data('month');
    
    // Show modal for rejection reason
    var modalHtml = `
        <div class='modal fade' id='rejectModal' tabindex='-1'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Reject Paysheet Suggestion</h5>
                        <button type='button' class='close' data-dismiss='modal'>&times;</button>
                    </div>
                    <div class='modal-body'>
                        <p><strong>Employee:</strong> ` + employeeName + `</p>
                        <p><strong>Month:</strong> ` + month + `</p>
                        <hr>
                        <div class='form-group'>
                            <label>Reason (optional)</label>
                            <textarea class='form-control' id='rejectReason' rows='3' placeholder='Why are you rejecting this paysheet? (e.g., employee on leave, already paid manually, etc.)'></textarea>
                        </div>
                        <div class='alert alert-info'>
                            <i class='fas fa-info-circle'></i> Rejected paysheets can be deleted later from the history page.
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                        <button type='button' class='btn btn-warning' id='confirmReject'>Reject Paysheet</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    $('#rejectModal').modal('show');
    
    // Handle confirm reject
    $('#confirmReject').on('click', function() {
        var reason = $('#rejectReason').val();
        
        $.ajax({
            url: '" . Url::to(['paysheet-suggestion/reject']) . "',
            type: 'POST',
            data: {
                id: suggestionId,
                reason: reason,
                " . Yii::$app->request->csrfParam . ": '" . Yii::$app->request->csrfToken . "'
            },
            success: function(response) {
                $('#rejectModal').modal('hide');
                $('#rejectModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                    location.reload();
                });
            },
            error: function(xhr) {
                alert('An error occurred while rejecting the suggestion.');
            }
        });
    });
    
    // Clean up modal after hide
    $('#rejectModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
});
", \yii\web\View::POS_READY);
?>

