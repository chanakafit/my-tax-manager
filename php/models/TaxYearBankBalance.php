<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tax_year_bank_balance".
 *
 * @property int $id
 * @property int $tax_year_snapshot_id
 * @property int $bank_account_id
 * @property float $balance
 * @property float $balance_lkr
 * @property string|null $supporting_document
 * @property int $created_at
 * @property int $updated_at
 *
 * @property TaxYearSnapshot $taxYearSnapshot
 * @property BankAccount $bankAccount
 */
class TaxYearBankBalance extends BaseModel
{
    /**
     * @var \yii\web\UploadedFile
     */
    public $uploadedFile;
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            \yii\behaviors\TimestampBehavior::class,
            // BlameableBehavior excluded - table doesn't have created_by/updated_by columns
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tax_year_bank_balance}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tax_year_snapshot_id', 'bank_account_id', 'balance', 'balance_lkr'], 'required'],
            [['tax_year_snapshot_id', 'bank_account_id'], 'integer'],
            [['balance', 'balance_lkr'], 'number'],
            [['supporting_document'], 'string', 'max' => 255],
            [['uploadedFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['pdf', 'png', 'jpg', 'jpeg'], 'maxSize' => 1024 * 1024 * 10, 'checkExtensionByMimeType' => false],
            [['tax_year_snapshot_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxYearSnapshot::class, 'targetAttribute' => ['tax_year_snapshot_id' => 'id']],
            [['bank_account_id'], 'exist', 'skipOnError' => true, 'targetClass' => OwnerBankAccount::class, 'targetAttribute' => ['bank_account_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tax_year_snapshot_id' => 'Tax Year Snapshot ID',
            'bank_account_id' => 'Bank Account',
            'balance' => 'Balance',
            'balance_lkr' => 'Balance (LKR)',
            'supporting_document' => 'Bank Statement',
            'uploadedFile' => 'Bank Statement',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[TaxYearSnapshot]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaxYearSnapshot()
    {
        return $this->hasOne(TaxYearSnapshot::class, ['id' => 'tax_year_snapshot_id']);
    }

    /**
     * Gets query for [[BankAccount]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBankAccount()
    {
        return $this->hasOne(OwnerBankAccount::class, ['id' => 'bank_account_id']);
    }

    /**
     * Upload supporting document
     * @return bool
     */
    public function upload()
    {
        if ($this->uploadedFile instanceof \yii\web\UploadedFile) {
            try {
                $uploadPath = Yii::getAlias('@webroot/uploads/bank-statements');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                $fileName = 'bank_stmt_' . $this->bank_account_id . '_' . time() . '_' . uniqid() . '.' . $this->uploadedFile->extension;
                $filePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

                if ($this->uploadedFile->saveAs($filePath)) {
                    // Delete old file if exists
                    if ($this->supporting_document && file_exists(Yii::getAlias('@webroot/' . $this->supporting_document))) {
                        unlink(Yii::getAlias('@webroot/' . $this->supporting_document));
                    }
                    $this->supporting_document = 'uploads/bank-statements/' . $fileName;
                    return true;
                } else {
                    Yii::error('Failed to save uploaded file: ' . $filePath);
                    $this->addError('uploadedFile', 'Failed to save the uploaded file.');
                    return false;
                }
            } catch (\Exception $e) {
                Yii::error('Upload error: ' . $e->getMessage());
                $this->addError('uploadedFile', 'An error occurred during file upload: ' . $e->getMessage());
                return false;
            }
        }
        return true; // No file to upload, that's okay
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Only process upload if a file was actually uploaded
        if ($this->uploadedFile instanceof \yii\web\UploadedFile) {
            if (!$this->upload()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the full path to the supporting document
     * @return string|null
     */
    public function getSupportingDocumentPath()
    {
        if ($this->supporting_document) {
            return Yii::getAlias('@webroot/' . $this->supporting_document);
        }
        return null;
    }
}

