<?php

namespace app\models;

use yii\db\ActiveRecord;

class TaxConfig extends BaseModel
{
    public static function tableName()
    {
        return '{{%tax_config}}';
    }

    public function rules()
    {
        return [
            [['name', 'key', 'value', 'valid_from'], 'required'],
            [['value'], 'number'],
            [['description'], 'string'],
            [['is_active'], 'boolean'],
            [['valid_from', 'valid_until'], 'date', 'format' => 'php:Y-m-d'],
            [['name', 'key'], 'string', 'max' => 255],
            [['key'], 'unique'],
        ];
    }

    public static function getConfig($key, $date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $config = self::find()->where(['key' => $key, 'is_active' => true])
            ->andWhere(['<=', 'valid_from', $date])
            ->andWhere(['or',
                ['valid_until' => null],
                ['>=', 'valid_until', $date]
            ])
            ->one();

        // If no config found with the exact key, try alternative keys for backward compatibility
        if (!$config && $key === 'profit_tax_rate') {
            $config = self::find()
                ->where(['is_active' => true])
                ->andWhere(['<=', 'valid_from', $date])
                ->andWhere(['or',
                    ['valid_until' => null],
                    ['>=', 'valid_until', $date]
                ])
                ->andWhere(['like', 'key', 'profit_tax_rate'])
                ->orderBy(['valid_from' => SORT_DESC])
                ->one();
        }

        return $config ? $config->value : null;
    }

    /**
     * Get tax rate for a specific tax period
     * @param string $startDate Start date of tax period
     * @param string $endDate End date of tax period
     * @return float Tax rate percentage
     */
    public static function getTaxRateForPeriod($startDate, $endDate)
    {
        // Use the start date of the period to determine applicable tax rate
        // This ensures consistency for the entire period
        return self::getConfig('profit_tax_rate', $startDate) ?? 0;
    }
}
