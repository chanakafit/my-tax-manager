<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tax_year_snapshot".
 *
 * @property int $id
 * @property string $tax_year
 * @property string $snapshot_date
 * @property string|null $notes
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property TaxYearBankBalance[] $bankBalances
 * @property TaxYearLiabilityBalance[] $liabilityBalances
 */
class TaxYearSnapshot extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tax_year_snapshot}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tax_year', 'snapshot_date'], 'required'],
            [['snapshot_date'], 'safe'],
            [['notes'], 'string'],
            [['tax_year'], 'string', 'max' => 4],
            [['tax_year'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tax_year' => 'Tax Year',
            'snapshot_date' => 'Snapshot Date',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[BankBalances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBankBalances()
    {
        return $this->hasMany(TaxYearBankBalance::class, ['tax_year_snapshot_id' => 'id']);
    }

    /**
     * Gets query for [[LiabilityBalances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLiabilityBalances()
    {
        return $this->hasMany(TaxYearLiabilityBalance::class, ['tax_year_snapshot_id' => 'id']);
    }

    /**
     * Get or create snapshot for a tax year
     * @param string $taxYear
     * @return TaxYearSnapshot|null
     */
    public static function getOrCreate($taxYear)
    {
        $snapshot = self::findOne(['tax_year' => $taxYear]);

        if (!$snapshot) {
            $snapshot = new self();
            $snapshot->tax_year = $taxYear;
            $snapshot->snapshot_date = ($taxYear + 1) . '-03-31'; // Default to end of tax year
            if (!$snapshot->save()) {
                Yii::error('Failed to create tax year snapshot: ' . json_encode($snapshot->errors));
                return null;
            }
        }

        return $snapshot;
    }
}

