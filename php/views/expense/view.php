<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Expense $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="expense-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                ],
        ]) ?>
        <?= Html::a('Create Expense', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                    'id',
                    [
                            'attribute' => 'expense_category_id',
                            'value' => function ($model) {
                                /** @var \app\models\Expense $model */
                                return $model->expenseCategory ? $model->expenseCategory->name : null;
                            },
                    ],
                    'expense_date',
                    'title',
                    'description:ntext',
                    'amount_lkr',
                    'tax_amount',
                    'receipt_number',
                    'payment_method',
                    'status',
                    'is_recurring',
                    'recurring_interval',
                    'next_recurring_date',
                    [
                            'attribute' => 'receipt',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->receipt_path) {
                                    $url = '/' . $model->receipt_path;
                                    $ext = strtolower(pathinfo($model->receipt_path, PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        return Html::img([$url], ['style' => 'max-width:1000px;']);
                                    } elseif ($ext == 'pdf') {
                                        return '<embed src="' . $url . '" type="application/pdf" width="1000" height="400px" />';
                                    } else {
                                        return Html::a('Download Receipt', [$url], ['target' => '_blank']);
                                    }
                                }
                                return null;
                            },
                    ],
                    [
                            'attribute' => 'created_by',
                            'value' => function ($model) {
                                /** @var \app\models\Expense $model */
                                return $model->createdBy ? $model->createdBy->username : null;
                            },
                    ],
                    [
                            'attribute' => 'updated_by',
                            'value' => function ($model) {
                                /** @var \app\models\Expense $model */
                                return $model->updatedBy ? $model->updatedBy->username : null;
                            },
                    ],
            ],
    ]) ?>

</div>
