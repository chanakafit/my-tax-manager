$(document).ready(function() {
    // Invoice management
    $('.calculate-invoice-total').on('change', '.quantity, .unit-price', function() {
        let row = $(this).closest('tr');
        let quantity = parseFloat(row.find('.quantity').val()) || 0;
        let unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
        let total = quantity * unitPrice;
        row.find('.line-total').val(total.toFixed(2));
        calculateInvoiceTotal();
    });

    // Expense management
    $('.add-expense-item').click(function() {
        let template = $('#expense-item-template').html();
        $('.expense-items').append(template);
    });

    // Payroll processing
    $('.process-payroll').click(function(e) {
        e.preventDefault();
        let employeeId = $(this).data('employee-id');
        $.ajax({
            url: '/paysheet/calculate',
            type: 'POST',
            data: { employee_id: employeeId },
            success: function(response) {
                $('#payroll-details').html(response);
            }
        });
    });

    // Tax calculation
    $('.calculate-tax').click(function(e) {
        e.preventDefault();
        let period = $('#tax-period').val();
        $.ajax({
            url: '/tax-record/calculate',
            type: 'POST',
            data: { period: period },
            success: function(response) {
                $('#tax-calculation-result').html(response);
            }
        });
    });

    // Dynamic form calculations
    function calculateInvoiceTotal() {
        let total = 0;
        $('.line-total').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $('#invoice-total').val(total.toFixed(2));
        calculateTax(total);
    }

    function calculateTax(amount) {
        let taxRate = parseFloat($('#tax-rate').val()) || 0;
        let taxAmount = amount * (taxRate / 100);
        $('#tax-amount').val(taxAmount.toFixed(2));
        $('#grand-total').val((amount + taxAmount).toFixed(2));
    }
});
