<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\ExpenseCategory;
use app\models\Vendor;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Expense $model */
/** @var app\models\ExpenseSuggestion $suggestion */

$this->title = 'Create Expense from Suggestion';
$this->params['breadcrumbs'][] = ['label' => 'Expense Suggestions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="expense-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <strong>Creating expense from pattern suggestion:</strong><br>
        <strong>Category:</strong> <?= Html::encode($suggestion->expenseCategory->name) ?><br>
        <strong>Vendor:</strong> <?= Html::encode($suggestion->vendor->name) ?><br>
        <strong>Month:</strong> <?= date('F Y', strtotime($suggestion->suggested_month)) ?><br>
        <strong>Average Amount:</strong> <?= Yii::$app->formatter->asCurrency($suggestion->avg_amount_lkr, 'LKR') ?><br>
        <strong>Pattern Found In:</strong>
        <?php
        $months = $suggestion->getPatternMonthsArray();
        if (!empty($months)) {
            echo implode(', ', array_map(function($m) {
                return date('M Y', strtotime($m));
            }, $months));
        }
        ?>
    </div>

    <div class="expense-form">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'expense_category_id')->widget(Select2::class, [
                    'data' => ArrayHelper::map(ExpenseCategory::find()->all(), 'id', 'name'),
                    'options' => ['placeholder' => 'Select category...'],
                    'pluginOptions' => [
                        'allowClear' => false,
                    ],
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'vendor_id')->widget(Select2::class, [
                    'data' => ArrayHelper::map(Vendor::find()->all(), 'id', 'name'),
                    'options' => ['placeholder' => 'Select vendor...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'expense_date')->widget(DatePicker::class, [
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'currency_code')->dropDownList([
                    'LKR' => 'LKR',
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                    'GBP' => 'GBP',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'exchange_rate')->textInput(['type' => 'number', 'step' => '0.0001']) ?>
            </div>
        </div>

        <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'payment_method')->dropDownList([
                    'cash' => 'Cash',
                    'bank_transfer' => 'Bank Transfer',
                    'credit_card' => 'Credit Card',
                    'debit_card' => 'Debit Card',
                    'cheque' => 'Cheque',
                    'online' => 'Online Payment',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'status')->dropDownList([
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'receipt_number')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'receipt_file')->fileInput() ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Create Expense', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

