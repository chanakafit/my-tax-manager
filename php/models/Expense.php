<?php

namespace app\models;

use app\helpers\Params;
use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "expense".
 *
 * @property int $id
 * @property int $expense_category_id
 * @property string $expense_date
 * @property string $title
 * @property string|null $description
 * @property float $amount
 * @property string|null $currency_code
 * @property float|null $exchange_rate
 * @property float|null $amount_lkr
 * @property float|null $tax_amount
 * @property string|null $receipt_number
 * @property string|null $receipt_path
 * @property string $payment_method
 * @property string $status
 * @property int|null $vendor_id
 * @property int|null $is_recurring
 * @property string|null $recurring_interval
 * @property string|null $next_recurring_date
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property string|null $receipt_file
 * @property string|null $receipt_date
 * @property string|null $payment_date
 * @property string|null $payment_reference
 *
 * @property ExpenseCategory $expenseCategory
 * @property FinancialTransaction[] $financialTransactions
 * @property Vendor $vendor
 */
class Expense extends BaseModel
{
    /**
     * @var UploadedFile
     */
    public $receipt_file;

    public $tax_rate;

    /**
     * Store expense date before deletion for dependent operations
     * @var string
     */
    private $_expenseDateBeforeDelete;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Attach event handlers for tax recalculation
        $this->on(self::EVENT_AFTER_INSERT, function ($event) {
            try {
                \app\models\TaxRecord::recalculateForDate($this->expense_date);
            } catch (\Exception $e) {
                Yii::error("Failed to trigger tax recalculation after expense insert: " . $e->getMessage(), __METHOD__);
            }
        });

        $this->on(self::EVENT_AFTER_UPDATE, function ($event) {
            try {
                \app\models\TaxRecord::recalculateForDate($this->expense_date);
            } catch (\Exception $e) {
                Yii::error("Failed to trigger tax recalculation after expense update: " . $e->getMessage(), __METHOD__);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%expense}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'amount_lkr', 'receipt_number', 'receipt_path', 'vendor_id', 'recurring_interval', 'next_recurring_date', 'receipt_date', 'payment_date', 'payment_reference'], 'default', 'value' => null],
            [['currency_code'], 'default', 'value' => 'LKR'],
            [['exchange_rate'], 'default', 'value' => 1.0000],
            [['tax_amount'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 'paid'],
            [['is_recurring'], 'default', 'value' => 0],
            [['expense_category_id', 'expense_date', 'title', 'amount', 'payment_method'], 'required'],
            [['expense_category_id', 'vendor_id', 'is_recurring'], 'integer'],
            [['expense_date', 'next_recurring_date', 'receipt_date', 'payment_date'], 'safe'],
            [['description'], 'string'],
            [['amount', 'exchange_rate', 'amount_lkr', 'tax_amount'], 'number'],
            [['title', 'receipt_number', 'payment_method', 'status', 'recurring_interval', 'payment_reference'], 'string', 'max' => 255],
            [['currency_code'], 'string', 'max' => 3],
            [['receipt_path'], 'string'],
            [['receipt_file'], 'file', 'skipOnEmpty' => true, 'extensions' => ['png', 'jpg', 'jpeg', 'pdf']],
            [['expense_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExpenseCategory::class, 'targetAttribute' => ['expense_category_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vendor::class, 'targetAttribute' => ['vendor_id' => 'id']],
            [['currency_code'], 'in', 'range' => array_keys(Params::get('currencies'))],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'expense_category_id' => 'Expense Category',
            'expense_date' => 'Expense Date',
            'title' => 'Title',
            'description' => 'Description',
            'amount' => 'Amount',
            'currency_code' => 'Currency Code',
            'exchange_rate' => 'Exchange Rate',
            'amount_lkr' => 'Amount (LKR)',
            'tax_amount' => 'Tax Amount',
            'receipt_number' => 'Receipt Number',
            'receipt_path' => 'Receipt Path',
            'payment_method' => 'Payment Method',
            'status' => 'Status',
            'vendor_id' => 'Vendor',
            'is_recurring' => 'Is Recurring',
            'recurring_interval' => 'Recurring Interval',
            'next_recurring_date' => 'Next Recurring Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'receipt_file' => 'Receipt File',
            'receipt_date' => 'Receipt Date',
            'payment_date' => 'Payment Date',
            'payment_reference' => 'Payment Reference',
        ];
    }

    /**
     * Gets query for [[ExpenseCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpenseCategory()
    {
        return $this->hasOne(ExpenseCategory::class, ['id' => 'expense_category_id']);
    }

    /**
     * Gets query for [[FinancialTransactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFinancialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, ['related_expense_id' => 'id']);
    }

    /**
     * Gets query for [[Vendor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    public function uploadReceipt()
    {
        if ($this->receipt_file instanceof UploadedFile) {
            $uploadPath = Yii::getAlias('@webroot/uploads/receipts');

            // Create directory if it doesn't exist
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = uniqid('receipt_') . '.' . $this->receipt_file->extension;
            $filePath = $uploadPath . '/' . $fileName;

            if ($this->receipt_file->saveAs($filePath)) {
                $this->receipt_path = 'uploads/receipts/' . $fileName;
                return $this->save(false); // false to skip validation as we've already validated
            }
            return false;
        }
        return true; // if no file to upload, return true
    }

    /**
     * Calculate LKR amount based on exchange rate
     */
    public function calculateLKRAmount()
    {
        if ($this->amount && $this->exchange_rate) {
            $this->amount_lkr = $this->amount * $this->exchange_rate;
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


    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // Store expense date for afterDelete
        $this->_expenseDateBeforeDelete = $this->expense_date;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // Trigger tax recalculation in TaxRecord
        if ($this->_expenseDateBeforeDelete) {
            try {
                \app\models\TaxRecord::recalculateForDate($this->_expenseDateBeforeDelete);
            } catch (\Exception $e) {
                Yii::error("Failed to trigger tax recalculation from Expense afterDelete: " . $e->getMessage(), __METHOD__);
            }
        }
    }
}
