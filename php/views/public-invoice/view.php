<?php

use app\helpers\Params;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Invoice */
/* @var $token string */

$this->title = 'Invoice #' . $model->invoice_number;
?>

<div class="invoice-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Download PDF', ['download', 'token' => $token], [
                'class' => 'btn btn-primary',
                'target' => '_blank'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Invoice Details</h5>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td><strong>Invoice Date:</strong></td>
                            <td><?= Yii::$app->formatter->asDate($model->invoice_date) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Due Date:</strong></td>
                            <td><?= Yii::$app->formatter->asDate($model->due_date) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?= $model->status === 'paid' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($model->status) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Bill To</h5>
                    <div>
                        <strong><?= Html::encode($model->customer->company_name) ?></strong><br>
                        <?= nl2br(Html::encode($model->customer->address)) ?><br>
                        <?= Html::encode($model->customer->city) ?>
                        <?= $model->customer->state ? ', ' . Html::encode($model->customer->state) : '' ?>
                        <?= Html::encode($model->customer->postal_code) ?><br>
                        <?= Html::encode($model->customer->country) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Items</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Description</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($model->invoiceItems as $item): ?>
                            <tr>
                                <td><?= Html::encode($item->item_name) ?></td>
                                <td><?= Html::encode($item->description) ?></td>
                                <td class="text-end"><?= Yii::$app->formatter->asDecimal($item->quantity) ?></td>
                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($item->unit_price, $model->currency_code) ?></td>
                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($item->total_amount, $model->currency_code) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($model->subtotal, $model->currency_code) ?></td>
                        </tr>
                        <?php if ($model->tax_amount > 0): ?>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($model->tax_amount, $model->currency_code) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($model->discount > 0): ?>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($model->discount, $model->currency_code) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong><?= Yii::$app->formatter->asCurrency($model->total_amount, $model->currency_code) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Payment Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Bank Name:</strong></td>
                            <td><?= Html::encode(Params::get('bankingDetailsBankName')) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Branch Name:</strong></td>
                            <td><?= Html::encode(Params::get('bankingDetailsBranchName')) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Account Name:</strong></td>
                            <td><?= Html::encode(Params::get('bankingDetailsAccountName')) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Account Number:</strong></td>
                            <td><?= Html::encode(Params::get('bankingDetailsAccountNumber')) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>SWIFT Code:</strong></td>
                            <td><?= Html::encode(Params::get('bankingDetailsSwiftCode')) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Bank Code:</strong></td>
                            <td><?= Html::encode(Params::get('bankingDetailsBankCode')) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Branch Code:</strong></td>
                            <td><?= Html::encode(Params::get('bankingDetailsBranchCode')) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <?php if (Params::get('bankingDetailsBankAddress')): ?>
                <div class="mt-2">
                    <strong>Bank Address:</strong><br>
                    <?= Html::encode(Params::get('bankingDetailsBankAddress')) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($model->notes): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Notes</h5>
                <p class="mb-0"><?= nl2br(Html::encode($model->notes)) ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
