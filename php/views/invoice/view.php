<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Invoice;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */

$this->title = "Invoice #{$model->invoice_number}";
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .invoice-box {
        max-width: 1200px;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 14px;
        line-height: 24px;
        background: #fff;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 12px;
    }
    .status-paid { background: #d4edda; color: #155724; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-overdue { background: #f8d7da; color: #721c24; }
    .invoice-items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .invoice-items-table th,
    .invoice-items-table td {
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
    }
    .invoice-items-table th {
        background: #f8f9fa;
        text-align: left;
    }
    .text-right { text-align: right; }
    .total-box {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    .total-table {
        width: 100%;
        max-width: 400px;
        margin-left: auto;
    }
    .total-table td {
        padding: 5px 0;
    }
    .total-table .total-line {
        border-top: 2px solid #dee2e6;
        font-weight: bold;
        font-size: 1.1em;
    }
    .header-section {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .details-section {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .detail-box {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    .action-buttons {
        border-top: 1px solid #e2e8f0;
        margin-top: 30px;
        padding-top: 20px;
    }
    .btn-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
");
?>

<div class="invoice-box">
    <div class="row mb-4">
        <div class="col">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <div class="details-section">
        <div class="detail-box">
            <h4>Customer Details</h4>
            <div>
                <strong><?= Html::encode($model->customer->company_name) ?></strong><br>
                <?php if ($model->customer->contact_person): ?>
                    <?= Html::encode($model->customer->contact_person) ?><br>
                <?php endif; ?>
                <?= Html::encode($model->customer->address) ?><br>
                <?= Html::encode($model->customer->city) ?>
                <?= $model->customer->state ? ', ' . Html::encode($model->customer->state) : '' ?>
                <?= Html::encode($model->customer->postal_code) ?><br>
                <?= Html::encode($model->customer->country) ?><br>
                <br>
                <?php if ($model->customer->phone): ?>
                    Phone: <?= Html::encode($model->customer->phone) ?><br>
                <?php endif; ?>
                Email: <?= Html::encode($model->customer->email) ?>
            </div>
        </div>

        <div class="detail-box">
            <h4>Invoice Details</h4>
            <table class="table table-borderless">
                <tr>
                    <th>Invoice Date:</th>
                    <td><?= Yii::$app->formatter->asDate($model->invoice_date) ?></td>
                </tr>
                <tr>
                    <th>Due Date:</th>
                    <td><?= Yii::$app->formatter->asDate($model->due_date) ?></td>
                </tr>
                <?php if ($model->payment_date): ?>
                    <tr>
                        <th>Payment Date:</th>
                        <td><?= Yii::$app->formatter->asDate($model->payment_date) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>Currency:</th>
                    <td><?= Html::encode($model->currency_code) ?></td>
                </tr>
                <?php if ($model->currency_code !== 'LKR'): ?>
                    <tr>
                        <th>Exchange Rate:</th>
                        <td><?= Yii::$app->formatter->asDecimal($model->exchange_rate) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>Payment Terms:</th>
                    <td><?= $model->paymentTerm ? Html::encode($model->paymentTerm->name) : '' ?></td>
                </tr>
            </table>
        </div>
    </div>

    <h4>Invoice Items</h4>
    <div class="invoice-items">
        <table class="invoice-items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="tex-right">Discount</th>
                    <th class="text-right">Total (Without Tax)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($model->invoiceItems as $item): ?>
                <tr>
                    <td><?= Html::encode($item->item_name) ?></td>
                    <td><?= Html::encode($item->description) ?></td>
                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($item->quantity) ?></td>
                    <td class="text-right">
                        <?= Yii::$app->formatter->asCurrency($item->unit_price, $model->currency_code) ?>
                    </td>
                    <td class="text-right">
                        <?= Yii::$app->formatter->asCurrency($item->discount, $model->currency_code) ?>
                    </td>
                    <td class="text-right">
                        <?= Yii::$app->formatter->asCurrency($item->total_amount, $model->currency_code) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="invoice-totals">
        <table class="total-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">
                    <?= Yii::$app->formatter->asCurrency($model->subtotal, $model->currency_code) ?>
                </td>
            </tr>
            <tr>
                <td>Tax Amount:</td>
                <td class="text-right">
                    <?= Yii::$app->formatter->asCurrency($model->tax_amount, $model->currency_code) ?>
                </td>
            </tr>
            <?php if ($model->discount > 0): ?>
            <tr>
                <td>Discount:</td>
                <td class="text-right">
                    <?= Yii::$app->formatter->asCurrency($model->discount, $model->currency_code) ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr class="total-line">
                <td>Total Amount:</td>
                <td class="text-right">
                    <?= Yii::$app->formatter->asCurrency($model->total_amount, $model->currency_code) ?>
                </td>
            </tr>
            <?php if ($model->currency_code !== 'LKR'): ?>
            <tr>
                <td>Total (LKR):</td>
                <td class="text-right">
                    <?= Yii::$app->formatter->asCurrency($model->total_amount_lkr, 'LKR') ?>
                </td>
            </tr>
            <tr>
                <td>Exchange Rate:</td>
                <td class="text-right"><?= Yii::$app->formatter->asDecimal($model->exchange_rate) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <?php if ($model->notes): ?>
        <div style="margin-top: 20px;">
            <h4>Notes</h4>
            <p><?= Html::encode($model->notes) ?></p>
        </div>
    <?php endif; ?>

    <div style="margin-top: 20px; font-size: 12px; color: #666;">
        <p>
            Created: <?= Yii::$app->formatter->asDatetime($model->created_at) ?> by <?= $model->createdBy ? Html::encode($model->createdBy->username) : 'System' ?><br>
            Last Updated: <?= Yii::$app->formatter->asDatetime($model->updated_at) ?> by <?= $model->updatedBy ? Html::encode($model->updatedBy->username) : 'System' ?>
        </p>
    </div>

    <div class="action-buttons">
        <div class="btn-toolbar justify-content-end" role="toolbar">
            <div class="btn-group">
                <?= Html::a('<i class="fa fa-envelope"></i> Send Email', ['send-email', 'id' => $model->id], [
                    'class' => 'btn btn-info',
                ]) ?>
            </div>
            <div class="btn-group position-relative">
                <?= Html::a('<i class="fa fa-link"></i> Share Public Link', ['#'], [
                    'class' => 'btn btn-info',
                    'id' => 'generate-public-link',
                    'data-invoice-id' => $model->id,
                    'data-url' => \yii\helpers\Url::to(['generate-public-link', 'id' => $model->id])
                ]) ?>
                <div id="public-link-container" class="position-absolute end-0 bottom-100 mb-2 d-none" style="z-index: 1000; width: 400px;">
                    <div class="card">
                        <div class="card-body">
                            <div class="input-group">
                                <input type="text" id="public-link-input" class="form-control" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="copy-link-btn">
                                    <i class="fa fa-copy"></i> Copy
                                </button>
                            </div>
                            <small class="text-muted mt-2 d-block">Link expires in 30 days</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn-group">
                <?= Html::a('<i class="fa fa-download"></i> Download PDF', ['download-pdf', 'id' => $model->id], [
                    'class' => 'btn btn-primary',
                    'target' => '_blank'
                ]) ?>
            </div>
            <div class="btn-group">
                <?= Html::a('<i class="fa fa-edit"></i> Update', ['update', 'id' => $model->id], [
                    'class' => 'btn btn-primary'
                ]) ?>
            </div>
            <?php if ($model->status !== 'paid'): ?>
                <div class="btn-group">
                    <?= Html::a('<i class="fa fa-check"></i> Mark as Paid', ['mark-as-paid', 'id' => $model->id], [
                        'class' => 'btn btn-success'
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$script = <<<JS
    $('#generate-public-link').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var container = $('#public-link-container');
        var input = $('#public-link-input');
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: btn.data('url'),
            data: {
                id: btn.data('invoice-id'),
                _csrf: yii.getCsrfToken()
            },
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    input.val(response.url);
                    container.removeClass('d-none');
                    input.select();
                } else {
                    krajeeDialog.alert('Failed to generate public link: ' + response.message);
                }
            },
            error: function() {
                krajeeDialog.alert('Failed to generate public link. Please try again.');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    $('#copy-link-btn').on('click', function() {
        var input = $('#public-link-input');
        input.select();
        document.execCommand('copy');
        
        // Show feedback
        var btn = $(this);
        var originalText = btn.html();
        btn.html('<i class="fa fa-check"></i> Copied!');
        setTimeout(function() {
            btn.html(originalText);
        }, 2000);
    });

    // Close link container when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#generate-public-link, #public-link-container').length) {
            $('#public-link-container').addClass('d-none');
        }
    });

    // Keep link container open when clicking inside it
    $('#public-link-container').on('click', function(e) {
        e.stopPropagation();
    });
JS;
$this->registerJs($script);

// Add required styles
$this->registerCss("
    #public-link-container {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    #public-link-container .card {
        margin-bottom: 0;
    }
    .position-relative {
        position: relative;
    }
    .position-absolute {
        position: absolute;
    }
    .end-0 {
        right: 0;
    }
");
?>
