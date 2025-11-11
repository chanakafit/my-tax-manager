<?php

use app\helpers\Params;
use yii\helpers\Html;
use kartik\date\DatePicker;
use kartik\money\MaskMoney;
use kartik\file\FileInput;
use app\models\Expense;

$currency = Params::get('currencySymbol');

$js = <<<JS
function formatNumber(num) {
    return parseFloat(num).toFixed(2);
}

function updateTax() {
    var amount = $('#expense-amount').val() || 0;
    var taxRate = parseFloat($('#expense-tax_rate').val()) || 0;
    var taxAmount = (amount * taxRate) / 100;
    $('#expense-tax_amount').val(formatNumber(taxAmount));
}

function updateLKRAmount() {
    var amount = parseFloat($('#expense-amount').val()) || 0;
    var exchangeRate = parseFloat($('#expense-exchange_rate').val()) || 0;
    var lkrAmount = amount * exchangeRate;
    $('#expense-amount_lkr').val(formatNumber(lkrAmount));
    $('#lkr-amount-display').text(formatNumber(lkrAmount));
}

// Update tax amount when amount changes
$('#expense-amount').on('change keyup', function() {
    updateTax();
    updateLKRAmount();
});
$('#expense-tax_rate').on('change keyup', updateTax);
$('#expense-exchange_rate').on('change keyup', updateLKRAmount);

// Update exchange rate field visibility based on currency
$('#expense-currency_code').on('change', function() {
    var currency = $(this).val();
    var exchangeRateGroup = $('.field-expense-exchange_rate');
    var lkrAmountGroup = $('.field-expense-amount_lkr');
    
    if (currency === 'LKR') {
        exchangeRateGroup.hide();
        lkrAmountGroup.hide();
        $('#expense-exchange_rate').val(1);
    } else {
        exchangeRateGroup.show();
        lkrAmountGroup.show();
        updateLKRAmount();
    }
});

// Check budget limit when category is selected
$('#expense-expense_category_id').on('change', function() {
    var categoryId = $(this).val();
    if (categoryId) {
        $.get('/expense-category/get-budget', {id: categoryId}, function(data) {
            if (data.remaining < 0) {
                alert('Warning: This category has exceeded its budget limit!');
            } else if (data.remaining < data.limit * 0.1) {
                alert('Warning: This category is close to its budget limit!');
            }
            $('#budget-info').html(
                'Budget Limit: <?= $currency ?>' + formatNumber(data.limit) + '<br>' +
                'Used: <?= $currency ?>' + formatNumber(data.used) + '<br>' +
                'Remaining: <?= $currency ?>' + formatNumber(data.remaining)
            );
        });
    }
});

// Document ready handler with additional vendor selection handling
$(document).ready(function() {
    // Existing initialization
    $('#expense-currency_code').trigger('change');
    updateTax();
    updateLKRAmount();
    
    // Add vendor select handler
    $('#expense-vendor_id').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.currency_code) {
            $('#expense-currency_code').val(data.currency_code).trigger('change');
        }
    });
});
JS;

$this->registerJs($js);
?>

<div class="expense-form">
    <?php $form = \app\widgets\BActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'expense_category_id')->dropDownList(
                    $categories,
                    [
                            'prompt' => 'Select Category',
                            'class' => 'form-control'
                    ]
            ) ?>
            <div id="budget-info" class="alert alert-info">
                Select a category to see budget information
            </div>

            <?= $form->field($model, 'expense_date')->widget(DatePicker::class, [
                    'options' => ['placeholder' => 'Select date'],
                    'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
            ]) ?>

            <?= $form->field($model, 'currency_code')->dropDownList(
                Params::get('currencies'),
                [
                    'prompt' => 'Select Currency',
                    'class' => 'form-control'
                ]
            ) ?>

            <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>

            <?= $form->field($model, 'exchange_rate')->textInput([
                'type' => 'number',
                'step' => '0.0001',
                'min' => '0',
                'class' => 'form-control'
            ]) ?>

            <?= $form->field($model, 'amount_lkr')->textInput([
                'readonly' => true,
                'class' => 'form-control'
            ]) ?>

            <?= $form->field($model, 'tax_rate')->textInput(['type' => 'number', 'step' => '0.01']) ?>

            <?= $form->field($model, 'tax_amount')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'readonly' => true,
            ]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'payment_method')->dropDownList(Yii::$app->params['paymentMethods']) ?>

            <?= $form->field($model, 'vendor_id')->widget(\kartik\select2\Select2::class, [
                'options' => ['placeholder' => 'Type to search vendors...'],
                'initValueText' => $vendorName ?? null,
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 2,
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['/vendor/list']),
                        'dataType' => 'json',
                        'delay' => 250,
                        'data' => new \yii\web\JsExpression('function(params) {
                            return {
                                q: params.term
                            };
                        }'),
                        'processResults' => new \yii\web\JsExpression('function(data) {
                            return data;
                        }'),
                        'cache' => true
                    ],
                    'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
                ],
                'pluginEvents' => [
                    "select2:select" => "function(e) { 
                        var data = e.params.data;
                        if (data.currency_code) {
                            $('#expense-currency_code').val(data.currency_code).trigger('change');
                        }
                    }"
                ]
            ]) ?>
            <?= $form->field($model, 'receipt_number')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'receipt_file')->widget(FileInput::class, [
                    'options' => ['accept' => 'image/*,.pdf'],
                    'pluginOptions' => [
                            'showPreview' => true,
                            'showCaption' => true,
                            'showRemove' => true,
                            'showUpload' => false
                    ]
            ]) ?>

            <?= $form->field($model, 'is_recurring')->checkbox() ?>

            <div class="recurring-fields" style="display: <?= $model->is_recurring ? 'block' : 'none' ?>">
                <?= $form->field($model, 'recurring_interval')->dropDownList([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly'
                ]) ?>

                <?= $form->field($model, 'next_recurring_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'Select next date'],
                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
                ]) ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php \app\widgets\BActiveForm::end(); ?>
</div>
