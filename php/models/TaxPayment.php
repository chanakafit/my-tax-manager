<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tax_payment".
 *
 * @property int $id
 * @property string $tax_year
 * @property string $payment_date
 * @property float $amount
 * @property string $payment_type
 * @property int|null $quarter
 * @property string|null $reference_number
 * @property string|null $notes
 * @property string|null $receipt_file
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class TaxPayment extends BaseModel
{
    public $uploadedFile;

    const TYPE_QUARTERLY = 'quarterly';
    const TYPE_FINAL = 'final';

    /** Client side counterpart of isQuarterly(), for conditional validation */
    const JS_IS_QUARTERLY = "function (attribute, value) {
        return jQuery('#taxpayment-payment_type').val() === 'quarterly';
    }";

    public static function tableName()
    {
        return '{{%tax_payment}}';
    }

    public function rules()
    {
        return [
            [['tax_year', 'payment_date', 'amount', 'payment_type'], 'required'],
            [['payment_date'], 'safe'],
            [['amount'], 'number'],
            [['notes'], 'string'],
            [['quarter'], 'integer'],
            [['payment_type'], 'in', 'range' => [self::TYPE_QUARTERLY, self::TYPE_FINAL]],
            [['tax_year'], 'string', 'max' => 4],
            [['reference_number', 'receipt_file'], 'string', 'max' => 255],
            // A quarter only applies to a quarterly instalment. `when` alone is
            // server side only, so `whenClient` has to mirror it or the browser
            // asks for a quarter on a final (whole year) payment.
            [['quarter'], 'required', 'when' => function ($model) {
                return $model->isQuarterly();
            }, 'whenClient' => self::JS_IS_QUARTERLY],
            [['quarter'], 'in', 'range' => [1, 2, 3, 4], 'when' => function ($model) {
                return $model->isQuarterly();
            }, 'whenClient' => self::JS_IS_QUARTERLY],
            [['quarter'], 'default', 'value' => null],
            [['uploadedFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['pdf', 'png', 'jpg', 'jpeg'], 'maxSize' => 2 * 1024 * 1024], // 2MB limit
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tax_year' => 'Tax Year',
            'payment_date' => 'Payment Date',
            'amount' => 'Amount',
            'payment_type' => 'Payment Type',
            'quarter' => 'Quarter',
            'reference_number' => 'Reference Number',
            'notes' => 'Notes',
            'uploadedFile' => 'Receipt',
            'receipt_file' => 'Receipt File',
        ];
    }

    /**
     * Whether this payment is a quarterly instalment rather than the final
     * (whole year) payment
     * @return bool
     */
    public function isQuarterly()
    {
        return $this->payment_type === self::TYPE_QUARTERLY;
    }

    public function upload()
    {
        if ($this->uploadedFile) {
            $uploadPath = Yii::getAlias('@webroot/uploads/receipts');

            // Create directory if it doesn't exist
            if (!file_exists($uploadPath)) {
                if (!mkdir($uploadPath, 0777, true)) {
                    Yii::error("Failed to create directory: $uploadPath");
                    return false;
                }
                chmod($uploadPath, 0777);
            }

            // Generate a unique filename
            $fileName = 'tax_payment_' . $this->tax_year . '_' .
                       ($this->quarter ? 'Q' . $this->quarter : 'final') . '_' .
                       uniqid() . '.' . $this->uploadedFile->extension;

            $filePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

            try {
                if ($this->uploadedFile->saveAs($filePath)) {
                    $this->receipt_file = 'uploads/receipts/' . $fileName;
                    return true;
                }
            } catch (\Exception $e) {
                Yii::error("File upload failed: " . $e->getMessage());
                return false;
            }
        }
        return true; // Return true if no file to upload
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->uploadedFile) {
            if (!$this->upload()) {
                return false;
            }
        }

        if (!$this->isQuarterly()) {
            // Final payment covers the whole year of assessment
            $this->quarter = null;
        }

        return true;
    }
}
