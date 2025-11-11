<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "liability".
 *
 * @property int $id
 * @property string $liability_type (business or personal)
 * @property string $liability_category (loan or leasing)
 * @property string $lender_name
 * @property string|null $description
 * @property float $original_amount
 * @property string $start_date
 * @property string|null $end_date
 * @property float|null $interest_rate
 * @property float|null $monthly_payment
 * @property string $status (active, settled)
 * @property string|null $settlement_date
 * @property string|null $notes
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class Liability extends BaseModel
{
    const TYPE_BUSINESS = 'business';
    const TYPE_PERSONAL = 'personal';

    const CATEGORY_LOAN = 'loan';
    const CATEGORY_LEASING = 'leasing';
    const CATEGORY_CREDIT_CARD = 'credit_card';

    const STATUS_ACTIVE = 'active';
    const STATUS_SETTLED = 'settled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%liability}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['liability_type', 'liability_category', 'lender_name', 'original_amount', 'start_date'], 'required'],
            [['description', 'notes'], 'string'],
            [['original_amount', 'interest_rate', 'monthly_payment'], 'number'],
            [['start_date', 'end_date', 'settlement_date'], 'safe'],
            [['liability_type', 'liability_category', 'status'], 'string', 'max' => 50],
            [['lender_name'], 'string', 'max' => 255],
            ['liability_type', 'default', 'value' => self::TYPE_BUSINESS],
            ['liability_type', 'in', 'range' => [self::TYPE_BUSINESS, self::TYPE_PERSONAL]],
            ['liability_category', 'in', 'range' => [self::CATEGORY_LOAN, self::CATEGORY_LEASING, self::CATEGORY_CREDIT_CARD]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_SETTLED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'liability_type' => 'Liability Type',
            'liability_category' => 'Category',
            'lender_name' => 'Lender Name',
            'description' => 'Description',
            'original_amount' => 'Original Amount',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'interest_rate' => 'Interest Rate (%)',
            'monthly_payment' => 'Monthly Payment',
            'status' => 'Status',
            'settlement_date' => 'Settlement Date',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Get liability type options
     * @return array
     */
    public static function getLiabilityTypeOptions()
    {
        return [
            self::TYPE_BUSINESS => 'Business',
            self::TYPE_PERSONAL => 'Personal',
        ];
    }

    /**
     * Get liability category options
     * @return array
     */
    public static function getLiabilityCategoryOptions()
    {
        return [
            self::CATEGORY_LOAN => 'Loan',
            self::CATEGORY_LEASING => 'Leasing',
            self::CATEGORY_CREDIT_CARD => 'Credit Card',
        ];
    }

    /**
     * Get status options
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SETTLED => 'Settled',
        ];
    }

    /**
     * Check if liability was started in a given tax year
     * @param string $taxYear e.g., '2023' for 2023-2024
     * @return bool
     */
    public function isStartedInTaxYear($taxYear)
    {
        $taxYearStart = $taxYear . '-04-01';
        $taxYearEnd = ($taxYear + 1) . '-03-31';

        return $this->start_date >= $taxYearStart && $this->start_date <= $taxYearEnd;
    }

    /**
     * Check if liability was started before a given tax year
     * @param string $taxYear e.g., '2023' for 2023-2024
     * @return bool
     */
    public function isStartedBeforeTaxYear($taxYear)
    {
        $taxYearStart = $taxYear . '-04-01';
        return $this->start_date < $taxYearStart;
    }
}

