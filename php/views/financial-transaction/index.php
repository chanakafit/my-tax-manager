<?php

use app\models\FinancialTransaction;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\widgets\BGridView as GridView;

/** @var yii\web\View $this */
/** @var app\models\FinancialTransactionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Financial Transactions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="financial-transaction-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Financial Transaction', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'bank_account_id',
            'transaction_date',
            'transaction_type',
            'amount',
            //'reference_number',
            //'related_invoice_id',
            //'related_expense_id',
            //'related_paysheet_id',
            //'description:ntext',
            //'category',
            //'payment_method',
            //'status',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, FinancialTransaction $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
