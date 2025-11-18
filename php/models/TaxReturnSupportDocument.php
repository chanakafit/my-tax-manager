<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "tax_return_support_document".
 *
 * @property int $id
 * @property int $tax_year_snapshot_id
 * @property string $document_title
 * @property string|null $document_description
 * @property string $file_path
 * @property string $file_name
 * @property int $file_size
 * @property string|null $mime_type
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property TaxYearSnapshot $taxYearSnapshot
 * @property User $createdBy
 * @property User $updatedBy
 */
class TaxReturnSupportDocument extends BaseModel
{
    /**
     * @var UploadedFile
     */
    public $uploadedFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tax_return_support_document}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tax_year_snapshot_id', 'document_title'], 'required'],
            [['tax_year_snapshot_id', 'file_size', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['document_description'], 'string'],
            [['document_title', 'file_name'], 'string', 'max' => 255],
            [['file_path'], 'string', 'max' => 500],
            [['mime_type'], 'string', 'max' => 100],
            [['uploadedFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx', 'xls', 'xlsx'], 'maxSize' => 1024 * 1024 * 10], // 10MB max
            [['tax_year_snapshot_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxYearSnapshot::class, 'targetAttribute' => ['tax_year_snapshot_id' => 'id']],
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
            'document_title' => 'Document Title',
            'document_description' => 'Description',
            'file_path' => 'File Path',
            'file_name' => 'File Name',
            'file_size' => 'File Size',
            'mime_type' => 'MIME Type',
            'uploadedFile' => 'Upload Document',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
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
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Upload file and save the record
     *
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            if ($this->uploadedFile) {
                $uploadDir = Yii::getAlias('@webroot/uploads/tax-return-documents');

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $filename = uniqid() . '_' . time() . '.' . $this->uploadedFile->extension;
                $filePath = $uploadDir . '/' . $filename;

                if ($this->uploadedFile->saveAs($filePath)) {
                    $this->file_path = 'uploads/tax-return-documents/' . $filename;
                    $this->file_name = $this->uploadedFile->baseName . '.' . $this->uploadedFile->extension;
                    $this->file_size = $this->uploadedFile->size;
                    $this->mime_type = $this->uploadedFile->type;

                    return $this->save(false);
                }
            }
        }
        return false;
    }

    /**
     * Delete file when model is deleted
     *
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Delete the physical file
            if ($this->file_path) {
                $filePath = Yii::getAlias('@webroot/' . $this->file_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Get formatted file size
     *
     * @return string
     */
    public function getFormattedFileSize()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getFileExtension()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }
}

