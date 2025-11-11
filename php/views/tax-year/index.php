<?php

use yii\helpers\Html;
use app\widgets\BGridView as GridView;

$this->title = 'Tax Years';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="tax-year-index">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tax Year</th>
                            <th>Period</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($years as $year): ?>
                        <tr>
                            <td><?= $year ?>/<?= $year + 1 ?></td>
                            <td>1 Apr <?= $year ?> - 31 Mar <?= $year + 1 ?></td>
                            <td>
                                <?= Html::a('<i class="fas fa-eye"></i> View Summary', ['view', 'year' => $year], [
                                    'class' => 'btn btn-primary btn-sm'
                                ]) ?>
                                <?= Html::a('<i class="fas fa-plus"></i> Record Payment', ['make-payment', 'year' => $year], [
                                    'class' => 'btn btn-success btn-sm'
                                ]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
