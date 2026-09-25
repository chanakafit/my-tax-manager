<?php

namespace app\models;

use Yii;

class CapitalAllowance extends BaseModel
{
    public static function tableName()
    {
        return '{{%capital_allowance}}';
    }

    public function rules()
    {
        return [
            [['capital_asset_id', 'tax_year', 'tax_code', 'allowance_amount', 'written_down_value', 'year_number'], 'required'],
            [['capital_asset_id', 'year_number'], 'integer'],
            [['allowance_amount', 'written_down_value', 'percentage_claimed'], 'number'],
            [['tax_year'], 'string', 'max' => 4],
            [['tax_code'], 'string', 'max' => 255],
            [['capital_asset_id'], 'exist', 'skipOnError' => true, 'targetClass' => CapitalAsset::class, 'targetAttribute' => ['capital_asset_id' => 'id']],
            ['year_number', 'in', 'range' => range(1, 5)],
            ['percentage_claimed', 'default', 'value' => 20.0],
            // Prevent duplicate allowances for the same asset and tax year
            [['tax_year'], 'unique', 'targetAttribute' => ['capital_asset_id', 'tax_year'], 'message' => 'A capital allowance for this tax year already exists for this asset.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'capital_asset_id' => 'Capital Asset',
            'tax_year' => 'Tax Year',
            'tax_code' => 'Tax Code',
            'allowance_amount' => 'Allowance Amount',
            'written_down_value' => 'Written Down Value',
            'year_number' => 'Year Number',
            'percentage_claimed' => 'Percentage Claimed (%)',
        ];
    }

    public function getCapitalAsset()
    {
        return $this->hasOne(CapitalAsset::class, ['id' => 'capital_asset_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // The allowance is claimed for the whole year of assessment, so the annual
        // return and all four quarterly instalments have to be recalculated
        TaxRecord::recalculateForTaxYear($this->tax_year);

        return true;
    }

    public function afterDelete()
    {
        parent::afterDelete();

        // Recalculate the year of assessment without this allowance
        TaxRecord::recalculateForTaxYear($this->tax_year);
    }
}
