<?php

namespace app\models;

use app\helpers\Params;
use Yii;

/**
 * This is the model class for table "financial_transaction".
 *
 * @property int $id
 * @property int|null $bank_account_id
 * @property string $transaction_date
 * @property string $transaction_type
 * @property float $amount
 * @property float|null $exchange_rate
 * @property float|null $amount_lkr
 * @property string|null $reference_type
 * @property string|null $reference_number
 * @property int|null $related_invoice_id
 * @property int|null $related_expense_id
 * @property int|null $related_paysheet_id
 * @property string|null $description
 * @property string|null $category
 * @property string|null $payment_method
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property BankAccount $bankAccount
 * @property Expense $relatedExpense
 * @property Invoice $relatedInvoice
 * @property Paysheet $relatedPaysheet
 */
class FinancialTransaction extends BaseModel
{

    public const TRANSACTION_TYPE_DEPOSIT = 'deposit';
    public const TRANSACTION_TYPE_REMITTANCE = 'remittance';
    public const TRANSACTION_TYPE_WITHDRAWAL = 'withdrawal';
    public const TRANSACTION_TYPE_TRANSFER = 'transfer';
    public const TRANSACTION_TYPE_PAYMENT = 'payment';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const REFERENCE_TYPE_INVOICE = 'invoice';
    public const REFERENCE_TYPE_EXPENSE = 'expense';
    public const REFERENCE_TYPE_PAYSHEET = 'paysheet';

    public const CATEGORY_INCOME = 'income';
    public const CATEGORY_EXPENSE = 'expense';
    public const CATEGORY_TRANSFER = 'transfer';
    public const CATEGORY_PAYROLL = 'payroll';
    public const CATEGORY_TAX = 'tax';

    public const PAYMENT_METHOD_CASH = 'cash';
    public const PAYMENT_METHOD_CHECK = 'check';
    public const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';

    public const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_CASH => 'Cash',
        self::PAYMENT_METHOD_CHECK => 'Check',
        self::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
        self::PAYMENT_METHOD_CREDIT_CARD => 'Credit Card',
    ];
    
    public const CATEGORIES = [
        self::CATEGORY_INCOME => 'Income',
        self::CATEGORY_EXPENSE => 'Expense',
        self::CATEGORY_TRANSFER => 'Transfer',
        self::CATEGORY_PAYROLL => 'Payroll',
        self::CATEGORY_TAX => 'Tax',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%financial_transaction}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bank_account_id', 'exchange_rate', 'amount_lkr', 'reference_type', 'reference_number', 'related_invoice_id', 'related_expense_id', 'related_paysheet_id', 'description', 'category', 'payment_method'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'pending'],
            [['bank_account_id', 'related_invoice_id', 'related_expense_id', 'related_paysheet_id'], 'integer'],
            [['transaction_date', 'transaction_type', 'amount'], 'required'],
            [['transaction_date'], 'safe'],
            [['amount', 'exchange_rate', 'amount_lkr'], 'number'],
            [['description'], 'string'],
            [['transaction_type', 'reference_type', 'reference_number', 'category', 'payment_method', 'status'], 'string', 'max' => 255],
            [['bank_account_id'], 'exist', 'skipOnError' => true, 'targetClass' => BankAccount::class, 'targetAttribute' => ['bank_account_id' => 'id']],
            [['related_expense_id'], 'exist', 'skipOnError' => true, 'targetClass' => Expense::class, 'targetAttribute' => ['related_expense_id' => 'id']],
            [['related_invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['related_invoice_id' => 'id']],
            [['related_paysheet_id'], 'exist', 'skipOnError' => true, 'targetClass' => Paysheet::class, 'targetAttribute' => ['related_paysheet_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_account_id' => 'Bank Account ID',
            'transaction_date' => 'Transaction Date',
            'transaction_type' => 'Transaction Type',
            'amount' => 'Amount',
            'exchange_rate' => 'Exchange Rate',
            'amount_lkr' => 'Amount Lkr',
            'reference_type' => 'Reference Type',
            'reference_number' => 'Reference Number',
            'related_invoice_id' => 'Related Invoice ID',
            'related_expense_id' => 'Related Expense ID',
            'related_paysheet_id' => 'Related Paysheet ID',
            'description' => 'Description',
            'category' => 'Category',
            'payment_method' => 'Payment Method',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Create transaction from invoice payment
     * @param Invoice $invoice
     * @param string $paymentMethod
     * @param string|null $referenceNumber
     * @return bool
     */
    public static function createFromInvoice($invoice, $paymentMethod, $referenceNumber = null)
    {
        $transaction = new self();
        $transaction->bank_account_id = Params::get('defaultBankAccountId');
        $transaction->transaction_date = $invoice->payment_date ?: date('Y-m-d');
        $transaction->transaction_type = 'deposit';
        $transaction->amount = $invoice->total_amount;
        $transaction->exchange_rate = $invoice->exchange_rate;
        $transaction->amount_lkr = $invoice->total_amount_lkr;
        $transaction->reference_number = $referenceNumber;
        $transaction->related_invoice_id = $invoice->id;
        $transaction->description = "Payment received for invoice {$invoice->invoice_number} ({$invoice->currency_code})";
        $transaction->category = 'income';
        $transaction->payment_method = $paymentMethod;
        $transaction->status = 'completed';

        if ($transaction->save()) {
            $invoice->status = Invoice::STATUS_PAID;
            return $invoice->save();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // If there's no exchange rate, use 1 for LKR transactions
        if (empty($this->exchange_rate)) {
            $this->exchange_rate = 1;
        }

        // Calculate LKR amount if not set
        if (empty($this->amount_lkr) && !empty($this->amount)) {
            $this->amount_lkr = $this->amount * $this->exchange_rate;
        }

        return true;
    }

    /**
     * Gets query for [[BankAccount]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBankAccount()
    {
        return $this->hasOne(BankAccount::class, ['id' => 'bank_account_id']);
    }

    /**
     * Gets query for [[RelatedExpense]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedExpense()
    {
        return $this->hasOne(Expense::class, ['id' => 'related_expense_id']);
    }

    /**
     * Gets query for [[RelatedInvoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'related_invoice_id']);
    }

    /**
     * Gets query for [[RelatedPaysheet]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedPaysheet()
    {
        return $this->hasOne(Paysheet::class, ['id' => 'related_paysheet_id']);
    }

}
