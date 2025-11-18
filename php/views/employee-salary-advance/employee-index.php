<?php

use app\models\EmployeeSalaryAdvance;
use app\widgets\BHtml;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\EmployeeSalaryAdvanceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Employee $employee */

$this->title = 'Salary Advances: ' . $employee->getFullName();
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['/employee/index']];
$this->params['breadcrumbs'][] = ['label' => $employee->getFullName(), 'url' => ['/employee/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = 'Salary Advances';
?>
<div class="employee-salary-advance-index">

    <h1><?= BHtml::encode($this->title) ?></h1>

    <?php
    $currentYear = Yii::$app->request->get('year', date('Y'));
    $availableYears = EmployeeSalaryAdvance::getAvailableYears($employee->id);
    if (empty($availableYears)) {
        $availableYears = [date('Y')];
    }
    $monthlyOverview = EmployeeSalaryAdvance::getMonthlyOverview($employee->id, $currentYear);
    $yearTotal = EmployeeSalaryAdvance::getYearToDateTotal($employee->id, $currentYear);
    $allTimeTotal = EmployeeSalaryAdvance::getTotalAdvanceAmount($employee->id);
    ?>

    <!-- Year Selector -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <?php foreach ($availableYears as $year): ?>
                    <?= BHtml::a($year, ['employee-index', 'employeeId' => $employee->id, 'year' => $year], [
                        'class' => 'btn ' . ($year == $currentYear ? 'btn-primary' : 'btn-outline-primary')
                    ]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Year <?= $currentYear ?> Total</h6>
                    <h3 class="text-primary"><?= Yii::$app->formatter->asCurrency($yearTotal) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">All Time Total</h6>
                    <h3 class="text-info"><?= Yii::$app->formatter->asCurrency($allTimeTotal) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Average per Month (<?= $currentYear ?>)</h6>
                    <h3 class="text-success"><?= Yii::$app->formatter->asCurrency($yearTotal / 12) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Overview -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Monthly Overview - <?= $currentYear ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Amount</th>
                            <th class="text-center">Count</th>
                            <th class="text-right">Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthlyOverview as $data): ?>
                            <tr class="<?= $data['total'] > 0 ? 'table-info' : '' ?>">
                                <td><strong><?= $data['month'] ?></strong></td>
                                <td class="text-right"><?= Yii::$app->formatter->asCurrency($data['total']) ?></td>
                                <td class="text-center">
                                    <?php if ($data['count'] > 0): ?>
                                        <span class="badge badge-primary"><?= $data['count'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <?php if ($data['count'] > 0): ?>
                                        <?= Yii::$app->formatter->asCurrency($data['total'] / $data['count']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th>Total</th>
                            <th class="text-right"><?= Yii::$app->formatter->asCurrency($yearTotal) ?></th>
                            <th class="text-center">
                                <span class="badge badge-light"><?= array_sum(array_column($monthlyOverview, 'count')) ?></span>
                            </th>
                            <th class="text-right">-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <p>
        <?= BHtml::a('<i class="fas fa-plus"></i> Add Salary Advance', ['create', 'employeeId' => $employee->id], ['class' => 'btn btn-success']) ?>
        <?= BHtml::a('<i class="fas fa-user"></i> Back to Employee', ['/employee/view', 'id' => $employee->id], ['class' => 'btn btn-secondary']) ?>
    </p>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list"></i> Advance Details</h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'advance_date:date',
                    'amount:currency',
                    'reason',
                    'notes:ntext',
                    [
                        'class' => ActionColumn::class,
                        'template' => '{view} {update} {delete}',
                        'urlCreator' => function ($action, EmployeeSalaryAdvance $model, $key, $index, $column) {
                            return Url::toRoute([$action, 'id' => $model->id]);
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>


</div>

