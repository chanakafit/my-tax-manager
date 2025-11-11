<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'My Bank Accounts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="owner-bank-account-index">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3><?= Html::encode($this->title) ?></h3>
            <?= Html::a('<i class="fas fa-plus"></i> Add Bank Account', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'bank_name',
                    'account_name',
                    'account_number',
                    [
                        'attribute' => 'account_type',
                        'filter' => \app\models\OwnerBankAccount::getAccountTypeOptions(),
                    ],
                    [
                        'attribute' => 'account_holder_type',
                        'value' => function ($model) {
                            return '<span class="badge bg-' . ($model->account_holder_type == 'business' ? 'primary' : 'info') . '">'
                                . ucfirst($model->account_holder_type) . '</span>';
                        },
                        'format' => 'raw',
                        'filter' => ['business' => 'Business', 'personal' => 'Personal'],
                    ],
                    'currency',
                    [
                        'attribute' => 'is_active',
                        'value' => function ($model) {
                            return '<span class="badge bg-' . ($model->is_active ? 'success' : 'secondary') . '">'
                                . ($model->is_active ? 'Active' : 'Inactive') . '</span>';
                        },
                        'format' => 'raw',
                        'filter' => [1 => 'Active', 0 => 'Inactive'],
                    ],
                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>
        </div>
    </div>
</div>

