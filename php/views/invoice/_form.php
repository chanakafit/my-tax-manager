<?php

use app\models\Invoice;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\widgets\BActiveForm as ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use app\models\PaymentTerm;
use app\models\Customer;

$this->registerCss("
    .invoice-items-section {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .invoice-item {
        background-color: #fff;
        border: 1px solid #dee2e6;
        padding: 15px;
        margin-bottom: 15px;
    }
    .remove-item {
        margin-top: 25px;
    }
    .field-invoice-payment_date {
        display: none;
    }
    .form-control[readonly] {
        background-color: #f8f9fa;
    }
    .item-total {
        padding: 10px;
        margin-top: 10px;
        text-align: right;
        font-weight: bold;
        border-top: 1px solid #dee2e6;
    }
");

/* @var $this yii\web\View */
/* @var $model app\models\Invoice */
/* @var $form yii\widgets\ActiveForm */


// Register all JavaScript at the beginning
$js = <<<JS
// Due date handling functions
function updateDueDate() {
    var termId = $('#invoice-payment_term_id').val();
    var invoiceDate = $('#invoice-invoice_date').val();
    
    if (termId && invoiceDate) {
        $.get('/payment-term/get-due-date', {
            id: termId,
            invoiceDate: invoiceDate
        }, function(response) {
            if (response.success) {
                // Convert the date string to a Date object and format it
                var dateParts = response.dueDate.split('-');
                var date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                $('#invoice-due_date-kvdate').kvDatepicker('update', date);
                $('#invoice-due_date').val(response.dueDate);
            }
        });
    }
}

function formatNumber(num) {
    return parseFloat(num).toFixed(2);
}

function parseNumber(str) {
    return parseFloat(str) || 0;
}

function calculateTotals() {
    var subtotal = 0;
    var totalTax = 0;
    var totalItemDiscount = 0;
    
    // Calculate item level totals first
    $('.invoice-item').each(function() {
        var qty = parseNumber($(this).find('.quantity').val());
        var price = parseNumber($(this).find('.unit-price').val());
        var taxRate = parseNumber($(this).find('.tax-rate').val());
        var discount = parseNumber($(this).find('.discount').val());

        // Calculate item subtotal (before tax and discount)
        var itemSubtotal = qty * price;
        
        // Calculate item tax based on subtotal before discount
        var itemTaxAmount = (itemSubtotal * taxRate) / 100;
        
        // Calculate final item total (including tax and discount)
        var itemTotal = itemSubtotal + itemTaxAmount - discount;

        // Update hidden fields
        $(this).find('.tax-amount').val(formatNumber(itemTaxAmount));
        $(this).find('.total-amount').val(formatNumber(itemTotal));
        
        // Update running totals
        subtotal += itemSubtotal;
        totalTax += itemTaxAmount; // Accumulate tax amount for invoice total
        totalItemDiscount += discount;
        
        // Update item summary displays
        $(this).find('.subtotal-display').text(formatNumber(itemSubtotal));
        $(this).find('.tax-amount-display').text(formatNumber(itemTaxAmount));
        $(this).find('.total-display').text(formatNumber(itemTotal));
    });

    // Get invoice level discount
    var discountTotal = parseNumber($('#invoice-discount').val());
    
    // Calculate grand total: subtotal + total tax - invoice discount
    var grandTotal = subtotal + totalTax - discountTotal - totalItemDiscount;

    // Update invoice summary fields with proper tax amount
    $('#invoice-subtotal').val(formatNumber(subtotal));
    $('#invoice-tax_amount').val(formatNumber(totalTax));
    $('#invoice-total_amount').val(formatNumber(grandTotal));
    
    // Update LKR amount if needed
    updateLKRAmount();
}

function togglePaymentDate() {
    var status = $('#invoice-status').val();
    var paymentDateField = $('.field-invoice-payment_date');
    
    if (status === 'paid') {
        paymentDateField.show();
        if (!$('#invoice-payment_date-kvdate').val()) {
            $('#invoice-payment_date-kvdate').kvDatepicker('setDate', new Date());
        }
    } else {
        paymentDateField.hide();
        $('#invoice-payment_date-kvdate').val('');
    }
}

function updateLKRAmount() {
    var totalAmount = parseNumber($('#invoice-total_amount').val());
    var exchangeRate = parseNumber($('#invoice-exchange_rate').val());
    var lkrAmount = totalAmount * exchangeRate;
    $('#invoice-total_amount_lkr').val(formatNumber(lkrAmount));
}

// Document ready handler
$(document).ready(function() {
    // Initialize due date if payment term and invoice date are set
    updateDueDate();
    
    // Auto-generate invoice number if empty
    if (!$('#invoice-invoice_number').val()) {
        $.get('/invoice/generate-number', function(number) {
            $('#invoice-invoice_number').val(number);
        });
    }
    
    // Bind event handlers for all input changes that affect totals
    $(document).on('change keyup', '.quantity, .unit-price, .tax-rate, .discount', function() {
        calculateTotals();
    });
    
    // Add currency code change handler
    $('#invoice-currency_code').on('change', function() {
        calculateTotals();
    });
    
    // Add exchange rate change handler
    $('#invoice-exchange_rate').on('change keyup', function() {
        updateLKRAmount();
    });
    
    // Add discount change handler for the main invoice discount
    $('#invoice-discount').on('change keyup', function() {
        calculateTotals();
    });
    
    // Initial calculations
    setTimeout(function() {
        calculateTotals();
    }, 100);
    
    // Handle dynamic form events
    $(".dynamicform_wrapper").on("afterInsert", function(e, item) {
        $(item).find('input').val('');
        setTimeout(function() {
            calculateTotals();
        }, 100);
    });

    $(".dynamicform_wrapper").on("afterDelete", function(e) {
        setTimeout(function() {
            calculateTotals();
        }, 100);
    });
    
    // Initialize payment date visibility
    togglePaymentDate();
    
    // Bind status change event
    $('#invoice-status').on('change', function() {
        togglePaymentDate();
    });
    
    // Initialize LKR amount
    updateLKRAmount();
    
    // Add customer change handler
    $('#invoice-customer_id').on('change', function() {
        var customerId = $(this).val();
        if (customerId) {
            $.get('/invoice/get-customer-details', { id: customerId }, function(response) {
                if (response.success) {
                    $('#invoice-currency_code').val(response.currency).trigger('change');
                }
            });
        }
    });
});
JS;

$this->registerJs($js);
?>

<div class="invoice-form">
    <?php $form = \app\widgets\BActiveForm::begin(['id' => 'invoice-form']); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'customer_id')->widget(Select2::class, [
                'data' => ArrayHelper::map(Customer::find()->all(), 'id', 'fullName'),
                'options' => ['placeholder' => 'Select Customer...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>

            <?= $form->field($model, 'invoice_number')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'currency_code')->dropDownList(
                \app\helpers\Params::get('currencies'),
                [
                    'prompt' => 'Select Currency...',
                    'options' => [
                        'LKR' => ['selected' => empty($model->currency_code)],
                    ]
                ]
            ) ?>

            <?= $form->field($model, 'invoice_date')->widget(DatePicker::class, [
                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ],
                'options' => [
                    'autocomplete' => 'off'
                ]
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'payment_term_id')->widget(Select2::class, [
                'data' => ArrayHelper::map(PaymentTerm::find()->all(), 'id', 'name'),
                'options' => ['placeholder' => 'Select Payment Term...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>

            <?= $form->field($model, 'due_date')->widget(DatePicker::class, [
                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                'removeButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                    'startDate' => date('Y-m-d'),
                ],
                'options' => [
                    'autocomplete' => 'off',
                    'class' => 'form-control'
                ]
            ]) ?>

            <?= $form->field($model, 'exchange_rate')->textInput([
                    'type' => 'number',
                    'step' => '0.0001',
                    'min' => '0'
            ]) ?>

            <?= $form->field($model, 'status')->dropDownList($model::getStatusList()) ?>

            <?= $form->field($model, 'payment_date')->widget(DatePicker::class, [
                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ],
                'options' => [
                    'autocomplete' => 'off',
                    'class' => 'form-control'
                ]
            ]) ?>
        </div>
    </div>

    <div class="invoice-items-section">
        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'dynamicform_wrapper',
            'widgetBody' => '.container-items',
            'widgetItem' => '.invoice-item',
            'limit' => 10,
            'min' => 1,
            'insertButton' => '.add-item',
            'deleteButton' => '.remove-item',
            'model' => $invoiceItems[0],
            'formId' => 'invoice-form',
            'formFields' => [
                'item_name',
                'description',
                'quantity',
                'unit_price',
                'tax_rate',
                'tax_amount',
                'discount',
                'total_amount',
            ],
        ]); ?>

        <div class="container-items">
            <?php foreach ($invoiceItems as $index => $item): ?>
                <div class="invoice-item">
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($item, "[$index]item_name")->textInput() ?>
                        </div>
                        <div class="col-md-2">
                            <?= $form->field($item, "[$index]quantity")->textInput([
                                'type' => 'number',
                                'step' => '1',
                                'min' => '0',
                                'class' => 'form-control quantity'
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $form->field($item, "[$index]unit_price")->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'class' => 'form-control unit-price'
                            ]) ?>
                        </div>
                        <div class="col-md-1">
                            <?= $form->field($item, "[$index]tax_rate")->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'class' => 'form-control tax-rate'
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $form->field($item, "[$index]discount")->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'class' => 'form-control discount'
                            ]) ?>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="remove-item btn btn-danger">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <?= $form->field($item, "[$index]description")->textarea(['rows' => 2]) ?>

                    <div class="item-totals" style="text-align: right;">
                        <div class="row">
                            <div class="col-md-12">
                                Subtotal: <span class="subtotal-display">0.00</span> |
                                Tax: <span class="tax-amount-display">0.00</span> |
                                Total: <span class="total-display">0.00</span>
                            </div>
                        </div>
                    </div>

                    <?= $form->field($item, "[$index]tax_amount")->hiddenInput(['class' => 'tax-amount'])->label(false) ?>
                    <?= $form->field($item, "[$index]total_amount")->hiddenInput(['class' => 'total-amount'])->label(false) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="add-item btn btn-success">
            <i class="fa fa-plus"></i> Add Item
        </button>

        <?php DynamicFormWidget::end(); ?>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'notes')->textarea(['rows' => 6]) ?>
        </div>
        <div class="col-md-6">
            <div class="invoice-totals">
                <?= $form->field($model, 'subtotal')->textInput([
                    'readonly' => true,
                    'class' => 'form-control'
                ]) ?>

                <?= $form->field($model, 'tax_amount')->textInput([
                    'readonly' => true,
                    'class' => 'form-control'
                ]) ?>

                <?= $form->field($model, 'discount')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0',
                    'class' => 'form-control'
                ]) ?>

                <?= $form->field($model, 'total_amount')->textInput([
                    'readonly' => true,
                    'class' => 'form-control',
                    'style' => 'font-weight: bold;'
                ]) ?>

                <?= $form->field($model, 'total_amount_lkr')->textInput([
                    'readonly' => true,
                    'class' => 'form-control'
                ]) ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php \app\widgets\BActiveForm::end(); ?>
</div>
