<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Paysheet */

$this->title = 'Confirm Payment: ' . $model->employee->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Paysheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Confirm Payment';
?>
<div class="paysheet-confirm-payment">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Payment Details</h3>
                </div>
                <div class="box-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'payment_date')->widget(DatePicker::class, [
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ]
                    ]) ?>

                    <?= $form->field($model, 'payment_method')->dropDownList([
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'check' => 'Check',
                    ], ['prompt' => 'Select payment method']) ?>

                    <?= $form->field($model, 'payment_reference')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Confirm Payment', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Payment Summary</h3>
                </div>
                <div class="box-body">
                    <table class="table">
                        <tr>
                            <th>Employee:</th>
                            <td><?= Html::encode($model->employee->fullName) ?></td>
                        </tr>
                        <tr>
                            <th>Pay Period:</th>
                            <td><?= Html::encode(date('Y-m-d', strtotime($model->pay_period_start))) ?> to <?= Html::encode(date('Y-m-d', strtotime($model->pay_period_end))) ?></td>
                        </tr>
                        <tr>
                            <th>Basic Salary:</th>
                            <td><?= Yii::$app->formatter->asCurrency($model->basic_salary) ?></td>
                        </tr>
                        <tr>
                            <th>Allowances:</th>
                            <td><?= Yii::$app->formatter->asCurrency($model->allowances) ?></td>
                        </tr>
                        <tr>
                            <th>Deductions:</th>
                            <td><?= Yii::$app->formatter->asCurrency($model->deductions) ?></td>
                        </tr>
                        <tr>
                            <th>Tax Amount:</th>
                            <td><?= Yii::$app->formatter->asCurrency($model->tax_amount) ?></td>
                        </tr>
                        <tr>
                            <th>Net Salary:</th>
                            <td><strong><?= Yii::$app->formatter->asCurrency($model->net_salary) ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
