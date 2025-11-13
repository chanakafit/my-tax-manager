<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = "Manage Year-End Balances - {$year}";
$this->params['breadcrumbs'][] = ['label' => 'Tax Return', 'url' => ['index', 'year' => $year]];
$this->params['breadcrumbs'][] = 'Manage Balances';
?>

<div class="tax-return-manage-balances">
    <div class="card">
        <div class="card-header">
            <h3><?= Html::encode($this->title) ?></h3>
            <p>Tax Year: <strong><?= $year ?>-<?= $year + 1 ?></strong> (April 1, <?= $year ?> - March 31, <?= $year + 1 ?>)</p>
            <div class="alert alert-info">
                <strong>Note:</strong> This page shows balances as of <strong>March 31, <?= $year + 1 ?></strong>:
                <ul class="mb-0 mt-2">
                    <li>Liabilities that were active on that date (even if settled later)</li>
                    <li>Assets acquired before that date and not yet disposed</li>
                    <li>Bank accounts that are currently active</li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

            <!-- Snapshot Notes -->
            <div class="form-group">
                <?= $form->field($snapshot, 'notes')->textarea(['rows' => 3])->label('Notes / Remarks') ?>
                <?= $form->field($snapshot, 'snapshot_date')->textInput(['type' => 'date'])->label('Snapshot Date (usually March 31)') ?>
            </div>

            <hr>

            <!-- Bank Balances Section -->
            <h4 class="mt-4">Bank Account Balances (as of <?= $snapshot->snapshot_date ?>)</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Bank Name</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Type</th>
                            <th>Currency</th>
                            <th>Balance</th>
                            <th>Balance (LKR)</th>
                            <th>Bank Statement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bankAccounts as $account): ?>
                            <?php
                            $existingBalance = isset($existingBankBalances[$account->id]) ? $existingBankBalances[$account->id] : null;
                            ?>
                            <tr>
                                <td><?= Html::encode($account->bank_name) ?></td>
                                <td><?= Html::encode($account->account_name) ?></td>
                                <td><?= Html::encode($account->account_number) ?></td>
                                <td><span class="badge bg-<?= $account->account_holder_type == 'business' ? 'primary' : 'info' ?>"><?= ucfirst($account->account_holder_type ?? '') ?></span></td>
                                <td><?= Html::encode($account->currency) ?></td>
                                <td>
                                    <input type="number" step="0.01" class="form-control"
                                           name="BankBalance[<?= $account->id ?>][balance]"
                                           value="<?= $existingBalance ? $existingBalance->balance : '' ?>"
                                           placeholder="0.00">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control"
                                           name="BankBalance[<?= $account->id ?>][balance_lkr]"
                                           value="<?= $existingBalance ? $existingBalance->balance_lkr : '' ?>"
                                           placeholder="0.00">
                                </td>
                                <td>
                                    <?php if ($existingBalance && $existingBalance->supporting_document): ?>
                                        <div class="mb-2">
                                            <a href="/<?= $existingBalance->supporting_document ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-file-pdf"></i> View
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control form-control-sm"
                                           name="BankBalance[<?= $account->id ?>][document]"
                                           accept=".pdf,.png,.jpg,.jpeg">
                                    <small class="text-muted">PDF, JPG, PNG (max 10MB)</small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bankAccounts)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No bank accounts found. <?= Html::a('Add Bank Account', ['/owner-bank-account/create']) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <!-- Liabilities Section -->
            <h4 class="mt-4">Outstanding Liability Balances (as of <?= $taxYearEnd ?>)</h4>
            <div class="alert alert-secondary">
                <small><i class="fas fa-info-circle"></i> Showing liabilities that started before <?= $taxYearEnd ?> and were not settled by that date.</small>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Lender</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Start Date</th>
                            <th>Original Amount</th>
                            <th>Outstanding Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($liabilities as $liability): ?>
                            <?php
                            $existingBalance = isset($existingLiabilityBalances[$liability->id]) ? $existingLiabilityBalances[$liability->id] : null;
                            ?>
                            <tr>
                                <td><?= Html::encode($liability->lender_name) ?></td>
                                <td><span class="badge bg-<?= $liability->liability_type == 'business' ? 'primary' : 'info' ?>"><?= ucfirst($liability->liability_type ?? '') ?></span></td>
                                <td><?= ucfirst($liability->liability_category ?? '') ?></td>
                                <td><?= $liability->start_date ?></td>
                                <td><?= Yii::$app->formatter->asCurrency($liability->original_amount, 'LKR') ?></td>
                                <td>
                                    <input type="number" step="0.01" class="form-control"
                                           name="LiabilityBalance[<?= $liability->id ?>][outstanding_balance]"
                                           value="<?= $existingBalance ? $existingBalance->outstanding_balance : '' ?>"
                                           placeholder="0.00">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($liabilities)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No liabilities found. <?= Html::a('Add Liability', ['/liability/create']) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <!-- Capital Assets Section -->
            <h4 class="mt-4">Capital Assets (acquired before <?= $taxYearEnd ?>)</h4>
            <div class="alert alert-secondary">
                <small><i class="fas fa-info-circle"></i> These are assets acquired before the end of the tax year and not yet disposed as of that date.</small>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Asset Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Purchase Date</th>
                            <th>Purchase Cost</th>
                            <th>Current WDV</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($capitalAssets as $asset): ?>
                            <tr>
                                <td><?= Html::encode($asset->asset_name) ?></td>
                                <td>
                                    <span class="badge bg-<?= $asset->asset_type == 'business' ? 'primary' : 'info' ?>">
                                        <?= ucfirst($asset->asset_type ?? '') ?>
                                    </span>
                                </td>
                                <td><?= ucfirst($asset->asset_category ?? '') ?></td>
                                <td><?= Yii::$app->formatter->asDate($asset->purchase_date) ?></td>
                                <td><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                                <td><?= Yii::$app->formatter->asCurrency($asset->current_written_down_value, 'LKR') ?></td>
                                <td>
                                    <span class="badge bg-<?= $asset->status == 'active' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($asset->status ?? '') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($capitalAssets)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No capital assets found for this tax year.
                                    <?= Html::a('Add Capital Asset', ['/capital-asset/create'], ['class' => 'btn btn-sm btn-primary']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-group mt-4">
                <?= Html::submitButton('<i class="fas fa-save"></i> Save Balances', ['class' => 'btn btn-success btn-lg']) ?>
                <?= Html::a('Cancel', ['index', 'year' => $year], ['class' => 'btn btn-secondary btn-lg']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

