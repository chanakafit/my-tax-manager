<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\PaysheetSuggestion */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Approve Paysheet: ' . $model->employee->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Paysheet Suggestions', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Approve';
?>
<div class="paysheet-suggestion-approve">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="glyphicon glyphicon-info-sign"></i> Paysheet Details</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="dl-horizontal">
                        <dt>Employee:</dt>
                        <dd><strong><?= Html::encode($model->employee->fullName) ?></strong></dd>

                        <dt>Position:</dt>
                        <dd><?= Html::encode($model->employee->position) ?></dd>

                        <dt>Pay Period:</dt>
                        <dd><strong><?= $model->formattedMonth ?></strong></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="dl-horizontal">
                        <dt>Basic Salary:</dt>
                        <dd>LKR <?= Yii::$app->formatter->asDecimal($model->basic_salary, 2) ?></dd>

                        <?php if ($model->allowances > 0): ?>
                        <dt>Allowances:</dt>
                        <dd class="text-success">+ LKR <?= Yii::$app->formatter->asDecimal($model->allowances, 2) ?></dd>
                        <?php endif; ?>

                        <?php if ($model->deductions > 0): ?>
                        <dt>Deductions:</dt>
                        <dd class="text-danger">- LKR <?= Yii::$app->formatter->asDecimal($model->deductions, 2) ?></dd>
                        <?php endif; ?>

                        <?php if ($model->tax_amount > 0): ?>
                        <dt>Tax Amount:</dt>
                        <dd class="text-muted">- LKR <?= Yii::$app->formatter->asDecimal($model->tax_amount, 2) ?></dd>
                        <?php endif; ?>

                        <dt>Net Salary:</dt>
                        <dd><strong class="text-primary" style="font-size: 18px;">LKR <?= Yii::$app->formatter->asDecimal($model->net_salary, 2) ?></strong></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="glyphicon glyphicon-ok"></i> Approve Paysheet</h3>
        </div>
        <div class="panel-body">

            <?php $form = ActiveForm::begin([
                'id' => 'approve-form',
                'action' => ['approve', 'id' => $model->id],
                'method' => 'post',
            ]); ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Payment Date <span class="text-danger">*</span></label>
                        <?= DatePicker::widget([
                            'name' => 'payment_date',
                            'value' => date('Y-m-d'),
                            'options' => ['placeholder' => 'Select payment date'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                                'todayBtn' => true,
                            ],
                        ]); ?>
                        <p class="help-block">The date when the salary will be or was paid</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Payment Method <span class="text-danger">*</span></label>
                        <?= Html::dropDownList('payment_method', 'Bank Transfer', [
                            'Bank Transfer' => 'Bank Transfer',
                            'Cash' => 'Cash',
                            'Cheque' => 'Cheque',
                            'Online Payment' => 'Online Payment',
                        ], ['class' => 'form-control']) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">Payment Reference (Optional)</label>
                        <?= Html::textInput('payment_reference', '', [
                            'class' => 'form-control',
                            'placeholder' => 'e.g., Transaction ID, Cheque number, etc.',
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">Notes (Optional)</label>
                        <?= Html::textarea('notes', 'Generated from paysheet suggestion', [
                            'class' => 'form-control',
                            'rows' => 3,
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="glyphicon glyphicon-warning-sign"></i>
                <strong>Please confirm:</strong> This will create an actual paysheet record with status "pending".
                You can mark it as "paid" later from the paysheet management page.
            </div>

            <div class="form-group">
                <?= Html::submitButton('<i class="glyphicon glyphicon-ok"></i> Approve & Create Paysheet', [
                    'class' => 'btn btn-success btn-lg',
                    'data-confirm' => 'Are you sure you want to approve this paysheet?',
                ]) ?>
                <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> Cancel', ['index'], [
                    'class' => 'btn btn-default btn-lg',
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

</div>

