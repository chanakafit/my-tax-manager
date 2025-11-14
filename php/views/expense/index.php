<?php

use app\models\Expense;
use app\models\Vendor;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\ActionColumn;
use app\widgets\BGridView as GridView;

/** @var yii\web\View $this */
/** @var app\models\ExpenseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Expenses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Expense', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                            'attribute' => 'expense_category_id',
                            'value' => 'expenseCategory.name',
                    ],
                    'expense_date',
                    'title',
                    'amount_lkr',
                    'receipt_number',
                    [
                            'attribute' => 'vendor_id',
                            'value' => 'vendor.name',
                            'filter' => ArrayHelper::map(Vendor::find()->orderBy('name')->all(), 'id', 'name'),
                            'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'All Vendors'],
                    ],
                    [
                            'class' => ActionColumn::class,
                            'urlCreator' => function ($action, Expense $model, $key, $index, $column) {
                                return Url::toRoute([$action, 'id' => $model->id]);
                            }
                    ],
            ],
    ]); ?>


</div>
