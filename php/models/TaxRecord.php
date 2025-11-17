<?php

namespace app\models;

use app\helpers\Params;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "tax_record".
 *
 * @property int $id
 * @property string $tax_period_start
 * @property string $tax_period_end
 * @property string $tax_type
 * @property string|null $tax_code
 * @property string|null $ird_ref
 * @property float $tax_rate
 * @property float $taxable_amount
 * @property float $tax_amount
 * @property string $payment_status
 * @property string|null $payment_date
 * @property string|null $reference_number
 * @property string|null $notes
 * @property string|null $related_invoice_ids
 * @property string|null $related_expense_ids
 * @property string|null $related_paysheet_ids
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property float|null $total_income
 * @property float|null $total_expenses
 */
class TaxRecord extends BaseModel
{
    const TYPE_VAT = 'VAT';
    const TYPE_INCOME = 'Income Tax';
    const TYPE_PAYROLL = 'Payroll Tax';
    const EVENT_AFTER_TAX_PAYMENT = 'afterTaxPayment';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tax_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tax_period_start', 'tax_period_end', 'tax_type', 'tax_rate', 'taxable_amount', 'tax_amount'], 'required'],
            [['tax_period_start', 'tax_period_end', 'payment_date'], 'safe'],
            [['tax_rate', 'taxable_amount', 'tax_amount', 'total_income', 'total_expenses'], 'number'],
            [['notes', 'related_invoice_ids', 'related_expense_ids', 'related_paysheet_ids'], 'string'],
            [['tax_type', 'payment_status', 'reference_number', 'tax_code', 'ird_ref'], 'string', 'max' => 255],
            [['tax_type'], 'in', 'range' => array_keys(self::getTaxTypesList())],
            [['payment_status'], 'default', 'value' => 'pending'],
            [['payment_status'], 'in', 'range' => ['pending', 'paid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tax_period_start' => 'Tax Period Start',
            'tax_period_end' => 'Tax Period End',
            'tax_type' => 'Tax Type',
            'tax_code' => 'Tax Code',
            'ird_ref' => 'IRD Ref',
            'tax_rate' => 'Tax Rate (%)',
            'taxable_amount' => 'Taxable Amount',
            'tax_amount' => 'Tax Amount',
            'total_income' => 'Total Income',
            'total_expenses' => 'Total Expenses',
            'payment_status' => 'Payment Status',
            'payment_date' => 'Payment Date',
            'reference_number' => 'Reference Number',
            'notes' => 'Notes',
            'related_invoice_ids' => 'Related Invoice Ids',
            'related_expense_ids' => 'Related Expense Ids',
            'related_paysheet_ids' => 'Related Paysheet Ids',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Get related invoices
     */
    public function getRelatedInvoices()
    {
        $ids = $this->related_invoice_ids ? Json::decode($this->related_invoice_ids) : [];
        return Invoice::find()->where(['id' => $ids])->all();
    }

    /**
     * Get related expenses
     */
    public function getRelatedExpenses()
    {
        $ids = $this->related_expense_ids ? Json::decode($this->related_expense_ids) : [];
        return Expense::find()->where(['id' => $ids])->all();
    }

    /**
     * Calculate profit tax for a period
     */
    public function calculateTax()
    {
        // Extract year and quarter from tax code
        $year = substr($this->tax_code, 0, 4);
        $quarter = substr($this->tax_code, -1);

        // Determine tax period dates based on tax code
        if ($quarter === '0') { // Final tax
            $startDate = $year . '-04-01';
            $endDate = (intval($year) + 1) . '-03-31';
        } else {
            switch ($quarter) {
                case '1': // Q1: Apr-Jun
                    $startDate = $year . '-04-01';
                    $endDate = $year . '-06-30';
                    break;
                case '2': // Q2: Jul-Sep
                    $startDate = $year . '-07-01';
                    $endDate = $year . '-09-30';
                    break;
                case '3': // Q3: Oct-Dec
                    $startDate = $year . '-10-01';
                    $endDate = $year . '-12-31';
                    break;
                case '4': // Q4: Jan-Mar
                    $startDate = (intval($year) + 1) . '-01-01';
                    $endDate = (intval($year) + 1) . '-03-31';
                    break;
                default:
                    $this->addError('tax_code', 'Invalid quarter');
                    return false;
            }
        }

        // Get total income
        $incomeRecords = FinancialTransaction::find()
            ->where(['category' => FinancialTransaction::CATEGORY_INCOME])
            ->andWhere(['between', 'transaction_date', $startDate, $endDate])
            ->all();
        $totalIncome = 0;
        $invoiceIds = [];
        foreach ($incomeRecords as $record) {
            $totalIncome += $record->amount_lkr;
            if ($record->related_invoice_id) {
                $invoiceIds[] = $record->related_invoice_id;
            }
        }

        // Get total expenses
        $expenseRecords = FinancialTransaction::find()
            ->where(['category' => FinancialTransaction::CATEGORY_EXPENSE])
            ->andWhere(['between', 'transaction_date', $startDate, $endDate])
            ->all();
        $totalExpenses = 0;
        $expenseIds = [];
        foreach ($expenseRecords as $record) {
            $totalExpenses += $record->amount_lkr;
            if ($record->related_expense_id) {
                $expenseIds[] = $record->related_expense_id;
            }
        }

        $paysheetIds = [];
        $totalPayroll = 0;
        $payrollRecords = FinancialTransaction::find()
            ->where([
                'category' => FinancialTransaction::CATEGORY_PAYROLL
            ])
            ->andWhere(['between', 'transaction_date', $startDate, $endDate])
            ->all();
        foreach ($payrollRecords as $record) {
            $totalPayroll += $record->amount_lkr;
            if ($record->related_paysheet_id) {
                $paysheetIds[] = $record->related_paysheet_id;
            }
        }

        // Calculate profit before capital allowances
        $profit = $totalIncome - $totalExpenses - $totalPayroll;

        // Get capital allowances for this tax period
        $capitalAllowances = CapitalAllowance::find()
            ->where(['tax_code' => $this->tax_code])
            ->all();

        $totalCapitalAllowances = 0;
        foreach ($capitalAllowances as $allowance) {
            $totalCapitalAllowances += $allowance->allowance_amount;
        }

        // Calculate taxable profit after capital allowances and relief
        $yearlyRelief = Params::get('taxConfigs.' . $year . '.yearlyTaxRelief') ?? 0;
        if ($quarter === '0') { // Final tax
            $relief = $yearlyRelief;
        } else {
            $relief = $yearlyRelief / 4; // Quarterly relief
        }

        // Deduct capital allowances and relief from profit
        $taxableProfit = max(0, $profit - $totalCapitalAllowances - $relief);

        // Calculate tax at configured rate based on the tax period start date
        // This ensures 0% tax for periods before April 1, 2025
        $taxRatePercent = TaxConfig::getTaxRateForPeriod($startDate, $endDate);
        $taxRate = $taxRatePercent / 100;
        $taxAmount = $taxableProfit * $taxRate;

        $this->tax_period_start = $startDate;
        $this->tax_period_end = $endDate;
        $this->tax_type = self::TYPE_INCOME;
        $this->tax_rate = $taxRate;
        $this->taxable_amount = $taxableProfit;
        $this->tax_amount = $taxAmount;
        $this->total_income = $totalIncome ?: 0;
        $this->total_expenses = $totalExpenses + $totalCapitalAllowances + $totalPayroll;  // Include capital allowances in total expenses
        $this->related_invoice_ids = Json::encode($invoiceIds);
        $this->related_expense_ids = Json::encode($expenseIds);
        $this->related_paysheet_ids = Json::encode($paysheetIds);
        $this->payment_status = 'pending';

        return $this->save();

    }

    /**
     * Record tax payment
     */
    public function recordPayment($amount, $paymentMethod, $referenceNumber)
    {
        $transaction = new FinancialTransaction();
        $transaction->bank_account_id = Params::get('defaultBankAccountId');
        $transaction->transaction_date = date('Y-m-d');
        $transaction->transaction_type = 'withdrawal';
        $transaction->amount = $amount;
        $transaction->reference_number = $referenceNumber;
        $transaction->description = "Tax payment for {$this->tax_type} ({$this->tax_period_start} to {$this->tax_period_end})";
        $transaction->category = FinancialTransaction::CATEGORY_TAX;
        $transaction->payment_method = $paymentMethod;
        $transaction->status = 'completed';

        if ($transaction->save()) {
            $this->payment_status = 'paid';
            $this->payment_date = date('Y-m-d');
            $this->reference_number = $referenceNumber;
            return $this->save();
        }

        return false;
    }

    /**
     * Get tax types list
     */
    public static function getTaxTypesList()
    {
        return [
            self::TYPE_VAT => 'VAT',
            self::TYPE_INCOME => 'Income Tax',
            self::TYPE_PAYROLL => 'Payroll Tax'
        ];
    }

    /**
     * Generate tax code based on year and quarter
     * Format: YYYYQ (e.g., 20251 for 2025 Q1)
     */
    public function generateTaxCode()
    {
        $startDate = new \DateTime($this->tax_period_start);
        $year = $startDate->format('Y');

        // Determine quarter based on month
        $month = (int)$startDate->format('m');
        $quarter = $this->getQuarterFromMonth($month);

        return $year . $quarter;
    }

    /**
     * Get quarter number (1-4) from month
     */
    private function getQuarterFromMonth($month)
    {
        if ($month >= 4 && $month <= 6) return 1;  // Q1: Apr-Jun
        if ($month >= 7 && $month <= 9) return 2;  // Q2: Jul-Sep
        if ($month >= 10 && $month <= 12) return 3; // Q3: Oct-Dec
        return 4; // Q4: Jan-Mar
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Generate tax code if not set
        if (empty($this->tax_code)) {
            $this->tax_code = $this->generateTaxCode();
        }

        return true;
    }

    public static function getYearSummary($year)
    {
        $taxCodes = [$year . '0', $year . '1', $year . '2', $year . '3', $year . '4'];

        /** @var self $finalTaxRecord */
        $finalTaxRecord = self::find()
            ->where(['tax_code' => $year . '0'])
            ->one();

        // Get all tax records for the year
        $taxRecords = self::find()
            ->where(['tax_code' => $taxCodes])
            ->all();

        $totalIncome = 0;
        $totalExpenses = 0;
        $totalTaxAmount = 0;

        $invoiceIds = [];
        $expenseIds = [];
        $paysheetIds = [];

        if (!$finalTaxRecord) {
            foreach ($taxRecords as $taxRecord) {
                $totalIncome += $taxRecord->total_income;
                $totalExpenses += $taxRecord->total_expenses;
                $totalTaxAmount += $taxRecord->tax_amount;
                $invoiceIds = array_merge($invoiceIds, Json::decode($taxRecord->related_invoice_ids ?? '[]'));
                $expenseIds = array_merge($expenseIds, Json::decode($taxRecord->related_expense_ids ?? '[]'));
                $paysheetIds = array_merge($paysheetIds, Json::decode($taxRecord->related_paysheet_ids ?? '[]'));
            }
        } else {
            $totalIncome = $finalTaxRecord->total_income;
            $totalExpenses = $finalTaxRecord->total_expenses;
            $totalTaxAmount = $finalTaxRecord->tax_amount;
            $invoiceIds = Json::decode($finalTaxRecord->related_invoice_ids ?? '[]');
            $expenseIds = Json::decode($finalTaxRecord->related_expense_ids ?? '[]');
            $paysheetIds = Json::decode($finalTaxRecord->related_paysheet_ids ?? '[]');
        }

        // Get all tax payments for the year
        $taxPayments = TaxPayment::find()
            ->where(['tax_year' => $year])
            ->all();

        $totalPaidAmount = 0;
        foreach ($taxPayments as $payment) {
            $totalPaidAmount += $payment->amount;
        }

        // Get capital allowances for this year
        $capitalAllowances = CapitalAllowance::find()
            ->joinWith('capitalAsset')
            ->where(['tax_code' => $year . '0'])
            ->all();

        $totalCapitalAllowances = 0;
        foreach ($capitalAllowances as $allowance) {
            $totalCapitalAllowances += $allowance->allowance_amount;
        }

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'total_tax_amount' => $totalTaxAmount,
            'total_paid_amount' => $totalPaidAmount,
            'balance_due' => $totalTaxAmount - $totalPaidAmount,
            'tax_records' => $taxRecords,
            'tax_payments' => $taxPayments,
            'capital_allowances' => $capitalAllowances,
            'total_capital_allowances' => $totalCapitalAllowances,
            'invoices' => Invoice::find()->where(['id' => $invoiceIds])->orderBy(['invoice_date' => SORT_ASC])->all(),
            'expenses' => Expense::find()->where(['id' => $expenseIds])->orderBy(['expense_date' => SORT_ASC])->all(),
            'paysheets' => Paysheet::find()->where(['id' => $paysheetIds])->orderBy(['payment_date' => SORT_ASC])->all(),
        ];
    }
}
