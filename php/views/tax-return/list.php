<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tax Returns';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tax-return-list">
    <div class="card">
        <div class="card-header">
            <h3><?= Html::encode($this->title) ?></h3>
            <p class="text-muted">View and manage tax return submissions by year</p>
        </div>
        <div class="card-body">
            <?php if (empty($snapshots)): ?>
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> No Tax Returns Yet</h5>
                    <p>You haven't created any tax return submissions yet. Tax returns are created when you manage year-end balances for a specific tax year.</p>
                    <p class="mb-0">
                        <?= Html::a('Go to Tax Years', ['/tax-year/index'], ['class' => 'btn btn-primary']) ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tax Year</th>
                                <th>Period</th>
                                <th>Snapshot Date</th>
                                <th>Bank Accounts</th>
                                <th>Liabilities</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($snapshots as $snapshot): ?>
                                <?php
                                $year = $snapshot->tax_year;
                                $taxYearStart = $year . '-04-01';
                                $taxYearEnd = ($year + 1) . '-03-31';
                                $bankBalanceCount = count($snapshot->bankBalances);
                                $liabilityBalanceCount = count($snapshot->liabilityBalances);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= $year ?>-<?= $year + 1 ?></strong>
                                        <br>
                                        <small class="text-muted">Year <?= $year ?></small>
                                    </td>
                                    <td>
                                        <small>
                                            <?= Yii::$app->formatter->asDate($taxYearStart, 'php:M d, Y') ?>
                                            <br>to<br>
                                            <?= Yii::$app->formatter->asDate($taxYearEnd, 'php:M d, Y') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= Yii::$app->formatter->asDate($snapshot->snapshot_date, 'php:M d, Y') ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($bankBalanceCount > 0): ?>
                                            <span class="badge bg-primary"><?= $bankBalanceCount ?> accounts</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($liabilityBalanceCount > 0): ?>
                                            <span class="badge bg-warning text-dark"><?= $liabilityBalanceCount ?> liabilities</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= Yii::$app->formatter->asDatetime($snapshot->created_at) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?= Html::a('<i class="fas fa-eye"></i> View',
                                                ['/tax-return/view-report', 'year' => $year],
                                                ['class' => 'btn btn-outline-primary', 'title' => 'View Report']) ?>
                                            <?= Html::a('<i class="fas fa-edit"></i>',
                                                ['/tax-return/manage-balances', 'year' => $year],
                                                ['class' => 'btn btn-outline-secondary', 'title' => 'Edit Balances']) ?>
                                            <?= Html::a('<i class="fas fa-file-archive"></i>',
                                                ['/tax-return/export-excel', 'year' => $year],
                                                ['class' => 'btn btn-outline-success', 'title' => 'Download ZIP']) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-info-circle"></i> Need to create a new tax return?</h6>
                    <p class="mb-0">
                        Go to <?= Html::a('Tax Years', ['/tax-year/index'], ['class' => 'alert-link']) ?>,
                        select a year, and click "Tax Return Submission" to create or update a tax return.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Statistics -->
    <?php if (!empty($snapshots)): ?>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Tax Returns</h5>
                        <h2 class="text-primary"><?= count($snapshots) ?></h2>
                        <small class="text-muted">Submissions created</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Latest Submission</h5>
                        <h2 class="text-success"><?= $snapshots[0]->tax_year ?>-<?= $snapshots[0]->tax_year + 1 ?></h2>
                        <small class="text-muted">Most recent year</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <?= Html::a('View Tax Years', ['/tax-year/index'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                            <?= Html::a('Manage Assets', ['/capital-asset/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <?= Html::a('Manage Liabilities', ['/liability/index'], ['class' => 'btn btn-outline-warning btn-sm']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

