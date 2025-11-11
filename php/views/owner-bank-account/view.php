<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->account_name;
$this->params['breadcrumbs'][] = ['label' => 'My Bank Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="owner-bank-account-view">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3><?= Html::encode($this->title) ?></h3>
            <div>
                <?= Html::a('<i class="fas fa-edit"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this bank account?',
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'bank_name',
                    'account_name',
                    'account_number',
                    'branch_name',
                    'swift_code',
                    'account_type',
                    [
                        'attribute' => 'account_holder_type',
                        'value' => '<span class="badge bg-' . ($model->account_holder_type == 'business' ? 'primary' : 'info') . '">'
                            . ucfirst($model->account_holder_type) . '</span>',
                        'format' => 'raw',
                    ],
                    'currency',
                    [
                        'attribute' => 'is_active',
                        'value' => '<span class="badge bg-' . ($model->is_active ? 'success' : 'secondary') . '">'
                            . ($model->is_active ? 'Active' : 'Inactive') . '</span>',
                        'format' => 'raw',
                    ],
                    'notes:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>
</div>

