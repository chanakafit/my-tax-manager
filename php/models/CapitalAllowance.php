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
            [['allowance_amount', 'written_down_value'], 'number'],
            [['tax_year'], 'string', 'max' => 4],
            [['tax_code'], 'string', 'max' => 255],
            [['capital_asset_id'], 'exist', 'skipOnError' => true, 'targetClass' => CapitalAsset::class, 'targetAttribute' => ['capital_asset_id' => 'id']],
            ['year_number', 'in', 'range' => range(1, 5)],
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
        ];
    }

    public function getCapitalAsset()
    {
        return $this->hasOne(CapitalAsset::class, ['id' => 'capital_asset_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Find and update the tax record to include this allowance
        $taxRecord = TaxRecord::findOne(['tax_code' => $this->tax_code]);
        if ($taxRecord) {
            $taxRecord->trigger(TaxRecord::EVENT_AFTER_TAX_PAYMENT); // This will recalculate the tax including allowances
        }

        return true;
    }
}
