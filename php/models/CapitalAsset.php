<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;

class CapitalAsset extends BaseModel
{
    /**
     * @var UploadedFile
     */
    public $uploadedFile;

    public static function tableName()
    {
        return '{{%capital_asset}}';
    }

    public function rules()
    {
        return [
            [['asset_name', 'purchase_date', 'purchase_cost', 'initial_tax_year', 'asset_category'], 'required'],
            [['description', 'notes'], 'string'],
            [['purchase_date', 'disposal_date'], 'safe'],
            [['purchase_cost', 'current_written_down_value', 'disposal_value'], 'number'],
            [['asset_name', 'status', 'receipt_file', 'asset_type', 'asset_category'], 'string', 'max' => 255],
            [['initial_tax_year'], 'string', 'max' => 4],
            ['status', 'default', 'value' => 'active'],
            ['status', 'in', 'range' => ['active', 'disposed']],
            ['asset_type', 'default', 'value' => 'business'],
            ['asset_type', 'in', 'range' => ['business', 'personal']],
            ['asset_category', 'default', 'value' => 'movable'],
            ['asset_category', 'in', 'range' => ['immovable', 'movable']],
            [['uploadedFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['pdf', 'png', 'jpg', 'jpeg']],
            ['current_written_down_value', 'default', 'value' => function ($model) {
                return $model->purchase_cost;
            }],
            // Disposal date required when status is disposed
            ['disposal_date', 'required', 'when' => function($model) {
                return $model->status === 'disposed';
            }, 'whenClient' => "function (attribute, value) {
                return $('#capitalasset-status').val() === 'disposed';
            }"],
            // Disposal value should be a number when provided
            ['disposal_value', 'number', 'min' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'asset_name' => 'Asset Name',
            'asset_type' => 'Asset Type',
            'asset_category' => 'Asset Category',
            'description' => 'Description',
            'purchase_date' => 'Purchase Date',
            'purchase_cost' => 'Purchase Cost',
            'initial_tax_year' => 'Initial Tax Year',
            'current_written_down_value' => 'Current Written Down Value',
            'status' => 'Status',
            'disposal_date' => 'Disposal Date',
            'disposal_value' => 'Disposal Value',
            'notes' => 'Notes',
            'receipt_file' => 'Receipt',
            'uploadedFile' => 'Receipt File',
        ];
    }

    public function getAllowances()
    {
        return $this->hasMany(CapitalAllowance::class, ['capital_asset_id' => 'id']);
    }

    /**
     * Calculate capital allowance for a given tax year with custom percentage
     *
     * @param string $taxYear The tax year (e.g., '2024')
     * @param float $percentage The percentage of original asset value (purchase cost) to claim (1-100)
     * @return CapitalAllowance|null The calculated allowance or null if not eligible
     */
    public function calculateAllowance($taxYear, $percentage = 20.0)
    {
        // Only business assets are eligible for capital allowance
        if ($this->asset_type !== 'business') {
            return null;
        }

        $allowance = new CapitalAllowance();
        $allowance->capital_asset_id = $this->id;
        $allowance->tax_year = $taxYear;
        $allowance->tax_code = $taxYear . '0'; // Final tax code for the year

        // Get existing allowances for this asset
        $existingAllowances = CapitalAllowance::find()
            ->where(['capital_asset_id' => $this->id])
            ->orderBy(['year_number' => SORT_ASC])
            ->all();

        $yearNumber = count($existingAllowances) + 1;
        if ($yearNumber > 5) {
            return null; // All allowances used
        }

        $allowance->year_number = $yearNumber;
        $allowance->percentage_claimed = $percentage;

        // Calculate allowance based on ORIGINAL ASSET VALUE (purchase cost) and custom percentage
        $allowance->allowance_amount = $this->purchase_cost * ($percentage / 100);

        // Calculate new written down value (previous WDV minus this allowance)
        $previousWrittenDownValue = $this->current_written_down_value;
        $allowance->written_down_value = $previousWrittenDownValue - $allowance->allowance_amount;


        return $allowance;
    }

    public function upload()
    {
        if ($this->uploadedFile instanceof \yii\web\UploadedFile) {
            try {
                $uploadPath = Yii::getAlias('@webroot/uploads/capital-assets');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                $fileName = 'asset_' . time() . '_' . uniqid() . '.' . $this->uploadedFile->extension;
                $filePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

                if ($this->uploadedFile->saveAs($filePath)) {
                    $this->receipt_file = 'uploads/capital-assets/' . $fileName;
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
}
