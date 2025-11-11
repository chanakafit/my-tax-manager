<?php

use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;
use kartik\date\DatePicker;
use app\models\FinancialTransaction;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */

$this->title = 'Mark Invoice #' . $model->invoice_number . ' as Paid';
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoice_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Mark as Paid';

// Register JavaScript for LKR amount calculation
$js = <<<JS
function formatNumber(num) {
    return parseFloat(num).toFixed(2);
}

function updateLKRAmount() {
    var totalAmount = parseFloat($('#original-amount').val());
    var exchangeRate = parseFloat($('#invoice-exchange_rate').val()) || 0;
    var lkrAmount = totalAmount * exchangeRate;
    $('#lkr-amount-display').text(formatNumber(lkrAmount));
    $('#invoice-total_amount_lkr').val(formatNumber(lkrAmount));
}

$(document).ready(function() {
    // Initial calculation
    updateLKRAmount();
    
    // Update on exchange rate change
    $('#invoice-exchange_rate').on('change keyup', function() {
        updateLKRAmount();
    });
});
JS;
$this->registerJs($js);

// Define payment methods using constants
$paymentMethods = [
    FinancialTransaction::PAYMENT_METHOD_CASH => 'Cash',
    FinancialTransaction::PAYMENT_METHOD_CHECK => 'Cheque',
    FinancialTransaction::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
    FinancialTransaction::PAYMENT_METHOD_CREDIT_CARD => 'Credit Card',
];
?>
<div class="invoice-mark-paid">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="invoice-details">
        <p><strong>Customer:</strong> <?= Html::encode($model->customer->fullName) ?></p>
        <p><strong>Amount:</strong> <?= Yii::$app->formatter->asCurrency($model->total_amount, $model->currency_code) ?></p>
        <?php if ($model->currency_code !== 'LKR'): ?>
            <p>
                <strong>Amount (LKR):</strong>
                <span id="lkr-amount-display">0.00</span> LKR
                <?= Html::hiddenInput('original-amount', $model->total_amount, ['id' => 'original-amount']) ?>
                <?= Html::hiddenInput('invoice-total_amount_lkr', $model->total_amount_lkr, ['id' => 'invoice-total_amount_lkr']) ?>
            </p>
        <?php endif; ?>
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'payment_date')->widget(DatePicker::class, [
        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
            'todayHighlight' => true,
            'endDate' => date('Y-m-d')
        ],
        'options' => ['autocomplete' => 'off']
    ]) ?>

    <?= $form->field($model, 'payment_method')->dropDownList($paymentMethods, [
        'prompt' => 'Select Payment Method...'
    ]) ?>

    <?= $form->field($model, 'reference_number')->textInput([
        'maxlength' => true,
        'placeholder' => 'Optional - Enter cheque number, transaction ID, etc.'
    ]) ?>

    <?php if ($model->currency_code !== 'LKR'): ?>
        <?= $form->field($model, 'exchange_rate')->textInput([
            'type' => 'number',
            'step' => '0.0001',
            'min' => '0',
            'class' => 'form-control'
        ]) ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Confirm Payment', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancel', ['view', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
