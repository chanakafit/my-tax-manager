<?php

use app\models\TaxRecord;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\widgets\BGridView as GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\TaxRecordSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Tax Records';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tax-record-index">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
            <div>
                <?= Html::a('<i class="fas fa-calculator"></i> Calculate Quartely Tax', ['#'], [
                    'class' => 'btn btn-success',
                    'id' => 'calculate-tax-btn',
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#calculateTaxModal'
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'attribute' => 'tax_code',
                        'headerOptions' => ['style' => 'width:100px'],
                    ],
                    [
                        'attribute' => 'tax_period_start',
                        'format' => 'date',
                        'filter' => \yii\jui\DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'tax_period_start',
                            'dateFormat' => 'yyyy-MM-dd',
                            'options' => ['class' => 'form-control']
                        ]),
                    ],
                    [
                        'attribute' => 'tax_type',
                        'filter' => TaxRecord::getTaxTypesList(),
                    ],
                    [
                        'attribute' => 'total_income',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                        'headerOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'total_expenses',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                        'headerOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'taxable_amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                        'headerOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'tax_amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                        'headerOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'payment_status',
                        'format' => 'raw',
                        'filter' => [
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                        ],
                        'value' => function ($model) {
                            return Html::tag('span',
                                $model->payment_status == 'paid' ? 'Paid' : 'Pending',
                                ['class' => 'badge bg-' . ($model->payment_status == 'paid' ? 'success' : 'warning')]
                            );
                        },
                    ],
                    [
                        'class' => ActionColumn::class,
                        'template' => '{view} {update}',
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>

<!-- Calculate Tax Modal -->
<div class="modal fade" id="calculateTaxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Calculate Tax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="tax-year" class="form-label">Tax Year</label>
                    <select class="form-control" id="tax-year">
                        <?php
                        $currentYear = date('Y');
                        for ($i = $currentYear - 2; $i <= $currentYear; $i++) {
                            $yearLabel = $i . '/' . ($i + 1);
                            echo "<option value='{$i}'>{$yearLabel}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is-quarterly" checked>
                        <label class="form-check-label" for="is-quarterly">
                            Quarterly Tax
                        </label>
                    </div>
                    <div id="quarter-section">
                        <label for="tax-quarter" class="form-label">Quarter</label>
                        <select class="form-control" id="tax-quarter">
                            <option value="1">Q1 (Apr-Jun)</option>
                            <option value="2">Q2 (Jul-Sep)</option>
                            <option value="3">Q3 (Oct-Dec)</option>
                            <option value="4">Q4 (Jan-Mar)</option>
                        </select>
                    </div>
                </div>
                <div id="calculation-result" class="d-none">
                    <table class="table">
                        <tr><td>Period:</td><td class="text-end" id="period-display"></td></tr>
                        <tr><td>Total Income:</td><td class="text-end" id="total-income"></td></tr>
                        <tr><td>Total Expenses:</td><td class="text-end" id="total-expenses"></td></tr>
                        <tr><td>Profit:</td><td class="text-end" id="profit"></td></tr>
                        <tr><td>Taxable Amount:</td><td class="text-end" id="taxable-amount"></td></tr>
                        <tr><td>Tax Amount:</td><td class="text-end" id="tax-amount"></td></tr>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="calculate-btn">Calculate</button>
            </div>
        </div>
    </div>
</div>

<?php
$calculateUrl = Url::to(['calculate']);
$js = <<<JS
    let calculationSuccessful = false;

    function generateTaxCode(year, isQuarterly, quarter) {
        if (!isQuarterly || !quarter) {
            return year + '0'; // Final tax code
        }
        return year + quarter; // Quarterly tax code
    }

    function getQuarterDates(year, isQuarterly, quarter) {
        if (!isQuarterly || !quarter) {
            // Final tax year period
            return {
                startDate: year + '-04-01',
                endDate: (parseInt(year) + 1) + '-03-31'
            };
        }
        
        // Quarterly periods
        let startDate, endDate;
        switch(parseInt(quarter)) {
            case 1: // Q1: Apr-Jun
                startDate = year + '-04-01';
                endDate = year + '-06-30';
                break;
            case 2: // Q2: Jul-Sep
                startDate = year + '-07-01';
                endDate = year + '-09-30';
                break;
            case 3: // Q3: Oct-Dec
                startDate = year + '-10-01';
                endDate = year + '-12-31';
                break;
            case 4: // Q4: Jan-Mar
                startDate = (parseInt(year) + 1) + '-01-01';
                endDate = (parseInt(year) + 1) + '-03-31';
                break;
        }
        return { startDate, endDate };
    }

    // Toggle quarter section visibility
    $('#is-quarterly').change(function() {
        $('#quarter-section').toggle($(this).is(':checked'));
    });

    $('#calculate-btn').click(function() {
        var year = $('#tax-year').val();
        var isQuarterly = $('#is-quarterly').is(':checked');
        var quarter = isQuarterly ? $('#tax-quarter').val() : null;
        var taxCode = generateTaxCode(year, isQuarterly, quarter);
        
        $.post('$calculateUrl', {
            taxCode: taxCode
        }, function(response) {
            if (response.success) {
                var dates = getQuarterDates(year, isQuarterly, quarter);
                $('#period-display').text(dates.startDate + ' to ' + dates.endDate);
                $('#total-income').text(response.data.income.toFixed(2));
                $('#total-expenses').text(response.data.expenses.toFixed(2));
                $('#profit').text(response.data.profit.toFixed(2));
                $('#taxable-amount').text(response.data.taxableAmount.toFixed(2));
                $('#tax-amount').text(response.data.taxAmount.toFixed(2));
                $('#calculation-result').removeClass('d-none');
                calculationSuccessful = true;
            } else {
                alert(response.message || 'Failed to calculate tax. Please try again.');
            }
        }).fail(function() {
            alert('An error occurred while calculating tax. Please try again.');
        });
    });
    
    // Handle modal close event
    $('#calculateTaxModal').on('hidden.bs.modal', function () {
        if (calculationSuccessful) {
            $.pjax.reload({container: '#p0'});
            calculationSuccessful = false;
            $('#calculation-result').addClass('d-none');
        }
    });

    // Set default quarter based on current date
    $(document).ready(function() {
        var currentDate = new Date();
        var currentMonth = currentDate.getMonth() + 1; // JavaScript months are 0-based
        var quarter;
        
        if (currentMonth >= 4 && currentMonth <= 6) quarter = 1;
        else if (currentMonth >= 7 && currentMonth <= 9) quarter = 2;
        else if (currentMonth >= 10 && currentMonth <= 12) quarter = 3;
        else quarter = 4;
        
        $('#tax-quarter').val(quarter);
        
        // Set tax year based on current date
        var year = currentDate.getFullYear();
        if (currentMonth < 4) year--; // If we're in Jan-Mar, we're in the previous tax year
        $('#tax-year').val(year);
    });
JS;
$this->registerJs($js);
?>
