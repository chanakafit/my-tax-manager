<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = "Tax Return Submission - Year {$year}";
$this->params['breadcrumbs'][] = ['label' => 'Tax Years', 'url' => ['/tax-year/view', 'year' => $year]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tax-return-index">
    <div class="card">
        <div class="card-header">
            <h3><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p class="lead">
                        Tax Year: <strong><?= $year ?>-<?= $year + 1 ?></strong>
                        (April 1, <?= $year ?> - March 31, <?= $year + 1 ?>)
                    </p>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <h4>Tax Return Submission Steps</h4>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">1. Manage Assets & Liabilities</h5>
                            </div>
                            <p class="mb-1">Add and manage your personal and business assets, and liabilities.</p>
                            <div class="btn-group mt-2" role="group">
                                <?= Html::a('Manage Assets', ['/capital-asset/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= Html::a('Manage Liabilities', ['/liability/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= Html::a('Manage Bank Accounts', ['/owner-bank-account/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            </div>
                        </div>

                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">2. Enter Year-End Balances</h5>
                                <?php if ($snapshot): ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php endif; ?>
                            </div>
                            <p class="mb-1">Enter bank account balances and outstanding liability amounts as of March 31, <?= $year + 1 ?>.</p>
                            <?= Html::a('Manage Year-End Balances', ['manage-balances', 'year' => $year], ['class' => 'btn btn-sm btn-primary mt-2']) ?>
                        </div>

                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">3. View & Export Tax Return Report</h5>
                                <?php if ($snapshot): ?>
                                    <span class="badge bg-info">Available</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Available</span>
                                <?php endif; ?>
                            </div>
                            <p class="mb-1">View the compiled tax return report and export to Excel or PDF format.</p>
                            <?php if ($snapshot): ?>
                                <div class="btn-group mt-2" role="group">
                                    <?= Html::a('<i class="fas fa-eye"></i> View Report', ['view-report', 'year' => $year], ['class' => 'btn btn-sm btn-success']) ?>
                                    <?= Html::a('<i class="fas fa-file-excel"></i> Export Excel', ['export-excel', 'year' => $year], ['class' => 'btn btn-sm btn-success']) ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mt-2"><small>Complete step 2 to generate reports</small></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Important Notes:</h5>
                        <ul>
                            <li>All personal and business assets should be recorded in the system</li>
                            <li>Bank balances should be as of the end of the tax year (March 31)</li>
                            <li>Outstanding liability balances should reflect amounts owed as of March 31</li>
                            <li>Disposed assets during the year will be automatically included in the report</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

