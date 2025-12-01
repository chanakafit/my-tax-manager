<?php
/**
 * @var yii\web\View $this
 * @var app\models\Customer $model
 */
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\BGridView as GridView;

$this->title = $model->company_name;
$this->params['breadcrumbs'][] = ['label' => 'Customers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= !$model->invoices ? Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this customer?',
                'method' => 'post',
            ],
        ]) : '' ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'company_name',
            'contact_person',
            'email:email',
            'phone',
            'website',
            'address:ntext',
            'city',
            'state',
            'postal_code',
            'country',
            'registry_code',
            'tax_number',
            'notes:ntext',
            [
                'attribute' => 'status',
                'value' => $model->statusText,
            ],
            [
                'attribute' => 'created_at',
                'value' => Yii::$app->formatter->asDatetime($model->created_at),
            ],
            [
                'attribute' => 'updated_at',
                'value' => Yii::$app->formatter->asDatetime($model->updated_at),
            ],
            [
                'attribute' => 'created_by',
                'value' => $model->createdBy->username ?? null,
            ],
            [
                'attribute' => 'updated_by',
                'value' => $model->updatedBy->username ?? null,
            ],
        ],
    ]) ?>

    <?php if ($model->invoices): ?>
    <h2>Customer Invoices</h2>
    <?= GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $model->invoices,
            'sort' => [
                'attributes' => ['invoice_number', 'invoice_date', 'due_date', 'total_amount', 'status'],
            ],
        ]),
        'columns' => [
            'invoice_number',
            'invoice_date:date',
            'due_date:date',
            [
                'attribute' => 'total_amount',
                'value' => function($model) {
                    return Yii::$app->formatter->asCurrency($model->total_amount);
                }
            ],
            [
                'attribute' => 'total_amount_lkr',
                'value' => function($model) {
                    return Yii::$app->formatter->asCurrency($model->total_amount_lkr);
                }
            ],
            'status',
            [
                'class' => 'yii\grid\ActionColumn',
                'controller' => 'invoice',
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('View', ['invoice/view', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']);
                    },
                ]
            ],
        ],
    ]); ?>
    <?php endif; ?>
</div>
