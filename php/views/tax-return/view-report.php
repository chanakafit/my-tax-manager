<?php

use yii\helpers\Html;

$this->title = "Tax Return Report - Year {$year}";
$this->params['breadcrumbs'][] = ['label' => 'Tax Return', 'url' => ['index', 'year' => $year]];
$this->params['breadcrumbs'][] = 'Report';
?>

<div class="tax-return-view-report">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3><?= Html::encode($this->title) ?></h3>
                <p class="mb-0">Assessment Year: <strong><?= $year ?>-<?= $year + 1 ?></strong></p>
                <p class="mb-0">Period: April 1, <?= $year ?> - March 31, <?= $year + 1 ?></p>
            </div>
            <div>
                <?= Html::a('<i class="fas fa-edit"></i> Edit Balances', ['manage-balances', 'year' => $year], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-file-archive"></i> Download ZIP (Excel + Bank Statements)', ['export-excel', 'year' => $year], ['class' => 'btn btn-success']) ?>
            </div>
        </div>
        <div class="card-body">

            <!-- Summary Section -->
            <div class="alert alert-info mb-4">
                <h5><i class="fas fa-info-circle"></i> Tax Year Coverage</h5>
                <p><strong>This report covers:</strong> April 1, <?= $year ?> to March 31, <?= $year + 1 ?></p>
                <p class="mb-0">
                    <strong>Note:</strong> Only assets purchased before or during this period will appear in this report. 
                    Assets purchased after March 31, <?= $year + 1 ?> will appear in the <?= $year + 1 ?>-<?= $year + 2 ?> tax year report.
                </p>
            </div>

            <?php
            // Calculate totals for summary
            $totalPersonalImmovable = count($data['personalImmovableExisting']) + count($data['personalImmovablePurchased']);
            $totalPersonalMovable = count($data['personalMovableExisting']) + count($data['personalMovablePurchased']);
            $totalBusinessImmovable = count($data['businessImmovableExisting']) + count($data['businessImmovablePurchased']);
            $totalBusinessMovable = count($data['businessMovableExisting']) + count($data['businessMovablePurchased']);
            $totalDisposed = count($data['assetsDisposed']);
            $totalBankAccounts = count($data['bankBalances']);
            $totalPersonalLiabilities = count($data['personalLiabilitiesExisting']) + count($data['personalLiabilitiesStarted']);
            $totalBusinessLiabilities = count($data['businessLiabilitiesExisting']) + count($data['businessLiabilitiesStarted']);
            ?>

            <!-- Quick Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Personal Assets</h6>
                            <h3><?= $totalPersonalImmovable + $totalPersonalMovable ?></h3>
                            <small><?= $totalPersonalImmovable ?> immovable, <?= $totalPersonalMovable ?> movable</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Business Assets</h6>
                            <h3><?= $totalBusinessImmovable + $totalBusinessMovable ?></h3>
                            <small><?= $totalBusinessImmovable ?> immovable, <?= $totalBusinessMovable ?> movable</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Bank Accounts</h6>
                            <h3><?= $totalBankAccounts ?></h3>
                            <small>Balances recorded</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Liabilities</h6>
                            <h3><?= $totalPersonalLiabilities + $totalBusinessLiabilities ?></h3>
                            <small><?= $totalPersonalLiabilities ?> personal, <?= $totalBusinessLiabilities ?> business</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Immovable Properties -->
            <?php if (count($data['personalImmovableExisting']) > 0 || count($data['personalImmovablePurchased']) > 0): ?>
            <section class="mb-5">
                <h4 class="border-bottom pb-2">1. IMMOVABLE PROPERTIES (Personal)</h4>

                <?php if (count($data['personalImmovableExisting']) > 0): ?>
                <h5 class="mt-3">Existing (owned before <?= $taxYearStart ?>):</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Name</th>
                            <th>Description</th>
                            <th>Purchase Date</th>
                            <th>Purchase Cost (LKR)</th>
                            <th>Current Value (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['personalImmovableExisting'] as $asset): ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><?= Html::encode($asset->description) ?></td>
                            <td><?= $asset->purchase_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->current_written_down_value, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (count($data['personalImmovablePurchased']) > 0): ?>
                <h5 class="mt-3">Purchased during tax year:</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Name</th>
                            <th>Description</th>
                            <th>Purchase Date</th>
                            <th>Purchase Cost (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['personalImmovablePurchased'] as $asset): ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><?= Html::encode($asset->description) ?></td>
                            <td><?= $asset->purchase_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <!-- Personal Movable Properties -->
            <?php if (count($data['personalMovableExisting']) > 0 || count($data['personalMovablePurchased']) > 0): ?>
            <section class="mb-5">
                <h4 class="border-bottom pb-2">2. MOVABLE PROPERTIES (Personal)</h4>

                <?php if (count($data['personalMovableExisting']) > 0): ?>
                <h5 class="mt-3">Existing (owned before <?= $taxYearStart ?>):</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Name</th>
                            <th>Description</th>
                            <th>Purchase Date</th>
                            <th>Purchase Cost (LKR)</th>
                            <th>Current Value (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['personalMovableExisting'] as $asset): ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><?= Html::encode($asset->description) ?></td>
                            <td><?= $asset->purchase_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->current_written_down_value, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (count($data['personalMovablePurchased']) > 0): ?>
                <h5 class="mt-3">Purchased during tax year:</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Name</th>
                            <th>Description</th>
                            <th>Purchase Date</th>
                            <th>Purchase Cost (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['personalMovablePurchased'] as $asset): ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><?= Html::encode($asset->description) ?></td>
                            <td><?= $asset->purchase_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <!-- Business Assets (similar structure) -->
            <?php if (count($data['businessImmovableExisting']) > 0 || count($data['businessImmovablePurchased']) > 0): ?>
            <section class="mb-5">
                <h4 class="border-bottom pb-2">3. IMMOVABLE PROPERTIES (Business)</h4>
                <?php if (count($data['businessImmovableExisting']) > 0): ?>
                <h5 class="mt-3">Existing:</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Asset Name</th><th>Purchase Date</th><th>Purchase Cost (LKR)</th><th>Current Value (LKR)</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['businessImmovableExisting'] as $asset): ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><?= $asset->purchase_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->current_written_down_value, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
                <?php if (count($data['businessImmovablePurchased']) > 0): ?>
                <h5 class="mt-3">Purchased during tax year:</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Asset Name</th><th>Purchase Date</th><th>Purchase Cost (LKR)</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['businessImmovablePurchased'] as $asset): ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><?= $asset->purchase_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <!-- Business Movable Properties -->
            <?php if (count($data['businessMovableExisting']) > 0 || count($data['businessMovablePurchased']) > 0): ?>
            <section class="mb-5">
                <h4 class="border-bottom pb-2">4. MOVABLE PROPERTIES (Business)</h4>

                <?php if (count($data['businessMovableExisting']) > 0): ?>
                <h5 class="mt-3">Existing (owned before <?= $taxYearStart ?>):</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Name</th>
                            <th>Description</th>
                            <th>Purchase Date</th>
                            <th>Purchase Cost (LKR)</th>
                            <th>Current Value (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['businessMovableExisting'] as $asset): ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><?= Html::encode($asset->description) ?></td>
                            <td><?= $asset->purchase_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->current_written_down_value, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (count($data['businessMovablePurchased']) > 0): ?>
                <h5 class="mt-3">Purchased during tax year:</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Name</th>
                            <th>Description</th>
                            <th>Purchase Date</th>
                            <th>Purchase Cost (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['businessMovablePurchased'] as $asset): ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><?= Html::encode($asset->description) ?></td>
                            <td><?= $asset->purchase_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <!-- Disposed Assets -->
            <?php if (count($data['assetsDisposed']) > 0): ?>
            <section class="mb-5">
                <h4 class="border-bottom pb-2">5. ASSETS DISPOSED DURING TAX YEAR</h4>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Purchase Cost</th>
                            <th>Disposal Date</th>
                            <th>Disposal Value</th>
                            <th>Profit/(Loss)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['assetsDisposed'] as $asset): ?>
                        <?php $profitLoss = ($asset->disposal_value ?? 0) - $asset->purchase_cost; ?>
                        <tr>
                            <td><?= Html::encode($asset->asset_name) ?></td>
                            <td><span class="badge bg-<?= $asset->asset_type == 'business' ? 'primary' : 'info' ?>"><?= ucfirst($asset->asset_type) ?></span></td>
                            <td><?= ucfirst($asset->asset_category ?? 'N/A') ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->purchase_cost, 'LKR') ?></td>
                            <td><?= $asset->disposal_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($asset->disposal_value ?? 0, 'LKR') ?></td>
                            <td class="text-end <?= $profitLoss >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= Yii::$app->formatter->asCurrency($profitLoss, 'LKR') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
            <?php endif; ?>

            <!-- Bank Balances -->
            <section class="mb-5">
                <h4 class="border-bottom pb-2">6. BANK BALANCES (As at <?= $snapshot->snapshot_date ?>)</h4>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Bank Name</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Type</th>
                            <th>Balance (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalPersonal = 0;
                        $totalBusiness = 0;
                        foreach ($data['bankBalances'] as $balance):
                            $account = $balance->bankAccount;
                            if ($account->account_holder_type == 'personal') {
                                $totalPersonal += $balance->balance_lkr;
                            } else {
                                $totalBusiness += $balance->balance_lkr;
                            }
                        ?>
                        <tr>
                            <td><?= Html::encode($account->bank_name) ?></td>
                            <td><?= Html::encode($account->account_name) ?></td>
                            <td><?= Html::encode($account->account_number) ?></td>
                            <td><span class="badge bg-<?= $account->account_holder_type == 'business' ? 'primary' : 'info' ?>"><?= ucfirst($account->account_holder_type) ?></span></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($balance->balance_lkr, 'LKR') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['bankBalances'])): ?>
                        <tr><td colspan="5" class="text-center text-muted">No bank balances recorded</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="4" class="text-end">Total Personal:</th>
                            <th class="text-end"><?= Yii::$app->formatter->asCurrency($totalPersonal, 'LKR') ?></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Total Business:</th>
                            <th class="text-end"><?= Yii::$app->formatter->asCurrency($totalBusiness, 'LKR') ?></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Grand Total:</th>
                            <th class="text-end"><?= Yii::$app->formatter->asCurrency($totalPersonal + $totalBusiness, 'LKR') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <!-- Personal Liabilities -->
            <section class="mb-5">
                <h4 class="border-bottom pb-2">7. PERSONAL LIABILITIES</h4>

                <?php if (count($data['personalLiabilitiesExisting']) > 0): ?>
                <h5 class="mt-3">Existing (started before <?= $taxYearStart ?>):</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Lender</th><th>Type</th><th>Start Date</th><th>Original Amount</th><th>Outstanding Balance</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['personalLiabilitiesExisting'] as $liability): ?>
                        <tr>
                            <td><?= Html::encode($liability->lender_name) ?></td>
                            <td><?= ucfirst($liability->liability_category) ?></td>
                            <td><?= $liability->start_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($liability->original_amount, 'LKR') ?></td>
                            <td class="text-end">
                                <?= isset($data['liabilityBalances'][$liability->id])
                                    ? Yii::$app->formatter->asCurrency($data['liabilityBalances'][$liability->id]->outstanding_balance, 'LKR')
                                    : 'N/A' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (count($data['personalLiabilitiesStarted']) > 0): ?>
                <h5 class="mt-3">Started during tax year:</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Lender</th><th>Type</th><th>Start Date</th><th>Amount</th><th>Outstanding Balance</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['personalLiabilitiesStarted'] as $liability): ?>
                        <tr>
                            <td><?= Html::encode($liability->lender_name) ?></td>
                            <td><?= ucfirst($liability->liability_category) ?></td>
                            <td><?= $liability->start_date ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($liability->original_amount, 'LKR') ?></td>
                            <td class="text-end">
                                <?= isset($data['liabilityBalances'][$liability->id])
                                    ? Yii::$app->formatter->asCurrency($data['liabilityBalances'][$liability->id]->outstanding_balance, 'LKR')
                                    : 'N/A' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (count($data['personalLiabilitiesExisting']) == 0 && count($data['personalLiabilitiesStarted']) == 0): ?>
                <p class="text-muted">No personal liabilities recorded</p>
                <?php endif; ?>
            </section>

            <!-- Business Liabilities (similar structure as personal) -->
            <section class="mb-5">
                <h4 class="border-bottom pb-2">8. BUSINESS LIABILITIES</h4>

                <?php if (count($data['businessLiabilitiesExisting']) > 0 || count($data['businessLiabilitiesStarted']) > 0): ?>
                    <?php if (count($data['businessLiabilitiesExisting']) > 0): ?>
                    <h5 class="mt-3">Existing:</h5>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr><th>Lender</th><th>Type</th><th>Start Date</th><th>Original Amount</th><th>Outstanding Balance</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['businessLiabilitiesExisting'] as $liability): ?>
                            <tr>
                                <td><?= Html::encode($liability->lender_name) ?></td>
                                <td><?= ucfirst($liability->liability_category) ?></td>
                                <td><?= $liability->start_date ?></td>
                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($liability->original_amount, 'LKR') ?></td>
                                <td class="text-end">
                                    <?= isset($data['liabilityBalances'][$liability->id])
                                        ? Yii::$app->formatter->asCurrency($data['liabilityBalances'][$liability->id]->outstanding_balance, 'LKR')
                                        : 'N/A' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <?php if (count($data['businessLiabilitiesStarted']) > 0): ?>
                    <h5 class="mt-3">Started during tax year:</h5>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr><th>Lender</th><th>Type</th><th>Start Date</th><th>Amount</th><th>Outstanding Balance</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['businessLiabilitiesStarted'] as $liability): ?>
                            <tr>
                                <td><?= Html::encode($liability->lender_name) ?></td>
                                <td><?= ucfirst($liability->liability_category) ?></td>
                                <td><?= $liability->start_date ?></td>
                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($liability->original_amount, 'LKR') ?></td>
                                <td class="text-end">
                                    <?= isset($data['liabilityBalances'][$liability->id])
                                        ? Yii::$app->formatter->asCurrency($data['liabilityBalances'][$liability->id]->outstanding_balance, 'LKR')
                                        : 'N/A' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                <?php else: ?>
                <p class="text-muted">No business liabilities recorded</p>
                <?php endif; ?>
            </section>

        </div>
    </div>
</div>

