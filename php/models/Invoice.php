<?php

namespace app\models;

use app\helpers\Params;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "invoice".
 *
 * @property int $id
 * @property int|null $customer_id
 * @property string $invoice_number
 * @property string $invoice_date
 * @property string $due_date
 * @property string|null $payment_date
 * @property int|null $payment_term_id
 * @property string $currency_code
 * @property float $exchange_rate
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $discount
 * @property float $total_amount
 * @property float $total_amount_lkr
 * @property string $status
 * @property string|null $notes
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class Invoice extends BaseModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_OVERDUE = 'overdue';
    public $payment_method;
    public $reference_number; // For payment reference

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%invoice}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'payment_term_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['invoice_number', 'invoice_date', 'due_date', 'currency_code'], 'required'],
            [['invoice_date', 'due_date', 'payment_date'], 'safe'],
            [['subtotal', 'tax_amount', 'discount', 'total_amount', 'exchange_rate', 'total_amount_lkr'], 'number'],
            [['notes'], 'string'],
            [['invoice_number'], 'string', 'max' => 255],
            [['currency_code'], 'string', 'max' => 3],
            [['status'], 'string', 'max' => 20],
            [['invoice_number'], 'unique'],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['exchange_rate'], 'default', 'value' => 1],
            [['discount'], 'default', 'value' => 0],
            [['currency_code'], 'in', 'range' => array_keys(Params::get('currencies'))],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_CANCELLED, self::STATUS_OVERDUE]],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['payment_term_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentTerm::class, 'targetAttribute' => ['payment_term_id' => 'id']],
            [['payment_method', 'reference_number'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer',
            'invoice_number' => 'Invoice Number',
            'invoice_date' => 'Invoice Date',
            'due_date' => 'Due Date',
            'payment_date' => 'Payment Date',
            'payment_term_id' => 'Payment Term',
            'currency_code' => 'Currency',
            'exchange_rate' => 'Exchange Rate',
            'subtotal' => 'Subtotal',
            'tax_amount' => 'Tax Amount',
            'discount' => 'Discount',
            'total_amount' => 'Total Amount',
            'total_amount_lkr' => 'Total Amount (LKR)',
            'status' => 'Status',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'payment_method' => 'Payment Method',
            'reference_number' => 'Reference Number',
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[InvoiceItems]].
     */
    public function getInvoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, ['invoice_id' => 'id']);
    }

    /**
     * Gets query for [[PaymentTerm]].
     */
    public function getPaymentTerm()
    {
        return $this->hasOne(PaymentTerm::class, ['id' => 'payment_term_id']);
    }

    /**
     * Get list of statuses
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PAID => 'Paid',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_OVERDUE => 'Overdue',
        ];
    }

    /**
     * Generate next invoice number
     */
    public static function generateInvoiceNumber()
    {
        $startNumber = Params::get('invoiceNumberStart');
        $format = Params::get('invoiceNumberFormat');

        $lastInvoice = self::find()
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastInvoice) {
            // Extract the numeric part and increment
            preg_match('/\d+/', $lastInvoice->invoice_number, $matches);
            $nextNumber = intval($matches[0]) + 1;
        } else {
            $nextNumber = $startNumber;
        }

        return sprintf($format, $nextNumber);
    }

    /**
     * Calculate totals based on invoice items
     */
    public function calculateTotals()
    {
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($this->invoiceItems as $item) {
            $subtotal += $item->total_amount;
            $taxAmount += $item->tax_amount;
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $subtotal + $taxAmount - ($this->discount ?? 0);
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment($amount, $paymentMethod, $referenceNumber = null)
    {
        $transaction = new FinancialTransaction();
        $transaction->bank_account_id = Params::get('defaultBankAccountId');
        $transaction->transaction_date = date('Y-m-d');
        $transaction->transaction_type = 'deposit';
        $transaction->amount = $amount;
        $transaction->reference_number = $referenceNumber;
        $transaction->related_invoice_id = $this->id;
        $transaction->description = "Payment received for invoice {$this->invoice_number}";
        $transaction->category = 'income';
        $transaction->payment_method = $paymentMethod;
        $transaction->status = 'completed';

        if ($transaction->save()) {
            $this->status = self::STATUS_PAID;
            return $this->save();
        }

        return false;
    }

    /**
     * Check for overdue invoices and update status
     */
    public static function updateOverdueStatus()
    {
        return self::updateAll(
            ['status' => self::STATUS_OVERDUE],
            ['and',
                ['status' => self::STATUS_PENDING],
                ['<', 'due_date', date('Y-m-d')]
            ]
        );
    }

    /**
     * Get list of available currencies
     */
    public static function getCurrencyList()
    {
        return Params::get('currencies');
    }

    /**
     * Calculate LKR amount based on exchange rate
     */
    public function calculateLKRAmount()
    {
        if ($this->total_amount && $this->exchange_rate) {
            $this->total_amount_lkr = $this->total_amount * $this->exchange_rate;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Calculate LKR amount before saving
        $this->calculateLKRAmount();

        return true;
    }

    public function getCreatedBy(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }
}
