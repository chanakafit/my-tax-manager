<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tax_year_liability_balance".
 *
 * @property int $id
 * @property int $tax_year_snapshot_id
 * @property int $liability_id
 * @property float $outstanding_balance
 * @property int $created_at
 * @property int $updated_at
 *
 * @property TaxYearSnapshot $taxYearSnapshot
 * @property Liability $liability
 */
class TaxYearLiabilityBalance extends BaseModel
{
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
        return '{{%tax_year_liability_balance}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tax_year_snapshot_id', 'liability_id', 'outstanding_balance'], 'required'],
            [['tax_year_snapshot_id', 'liability_id'], 'integer'],
            [['outstanding_balance'], 'number'],
            [['tax_year_snapshot_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxYearSnapshot::class, 'targetAttribute' => ['tax_year_snapshot_id' => 'id']],
            [['liability_id'], 'exist', 'skipOnError' => true, 'targetClass' => Liability::class, 'targetAttribute' => ['liability_id' => 'id']],
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
            'liability_id' => 'Liability',
            'outstanding_balance' => 'Outstanding Balance',
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
     * Gets query for [[Liability]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLiability()
    {
        return $this->hasOne(Liability::class, ['id' => 'liability_id']);
    }
}

