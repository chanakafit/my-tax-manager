<?php

namespace app\models;

use app\helpers\Params;
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
     * Get the maximum tax rate for a specific tax period
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

    /**
     * Year of assessment a date belongs to (Sri Lankan tax year is April - March,
     * so Jan-Mar belongs to the previous calendar year)
     * @param string $date Y-m-d
     * @return int
     */
    public static function getTaxYearForDate($date)
    {
        $timestamp = strtotime($date);
        $year = (int)date('Y', $timestamp);

        return ((int)date('n', $timestamp) >= 4) ? $year : ($year - 1);
    }

    /**
     * Get the params.taxConfigs entry for a year of assessment.
     *
     * Years that are not configured inherit the most recent configured year, so a
     * new year of assessment does not silently lose the relief and rate table.
     * @param int|string $year
     * @return array
     */
    public static function getYearConfig($year)
    {
        $configs = Params::get('taxConfigs');
        if (!is_array($configs) || empty($configs)) {
            return [];
        }

        $year = (int)$year;
        if (isset($configs[$year])) {
            return $configs[$year];
        }

        $years = array_map('intval', array_keys($configs));
        sort($years);

        $applicable = null;
        foreach ($years as $configuredYear) {
            if ($configuredYear <= $year) {
                $applicable = $configuredYear;
            }
        }

        return $applicable === null ? [] : $configs[$applicable];
    }

    /**
     * Yearly personal relief (Fifth Schedule) for a year of assessment
     * @param int|string $year
     * @return float
     */
    public static function getYearlyRelief($year)
    {
        $config = self::getYearConfig($year);

        return isset($config['yearlyTaxRelief']) ? (float)$config['yearlyTaxRelief'] : 0.0;
    }

    /**
     * Progressive rate table (First Schedule) for a year of assessment
     * @param int|string $year
     * @return array
     */
    public static function getTaxBrackets($year)
    {
        $config = self::getYearConfig($year);

        return (isset($config['taxBrackets']) && is_array($config['taxBrackets'])) ? $config['taxBrackets'] : [];
    }

    /**
     * Income tax on a taxable amount for a tax period.
     *
     * Applies the progressive rate table for the period's year of assessment and
     * then caps the result at the maximum rate from tax_config (15% for foreign
     * currency service exports / foreign source income from 01.04.2025, 0% before,
     * when such income was exempt).
     *
     * @param float $taxableAmount Taxable income for the period
     * @param string $periodStartDate Start of the tax period (Y-m-d)
     * @param int $periodsPerYear 1 for the annual return, 4 for a quarterly instalment
     *                            (the bands are scaled down so four quarters add up to
     *                             roughly the annual liability)
     * @return float
     */
    public static function calculateIncomeTax($taxableAmount, $periodStartDate, $periodsPerYear = 1)
    {
        return self::applyRateTable(
            $taxableAmount,
            self::getTaxBrackets(self::getTaxYearForDate($periodStartDate)),
            self::getTaxRateForPeriod($periodStartDate, $periodStartDate),
            $periodsPerYear
        );
    }

    /**
     * Apply a progressive rate table capped at a maximum rate. Pure calculation -
     * no config or database access, so it can be unit tested directly.
     *
     * @param float $taxableAmount
     * @param array $brackets List of ['width' => float|null, 'rate' => float] in ascending order
     * @param float $maxRatePercent Maximum effective rate, as a percentage
     * @param int $periodsPerYear Divides the band widths (4 = quarterly)
     * @return float
     */
    public static function applyRateTable($taxableAmount, array $brackets, $maxRatePercent, $periodsPerYear = 1)
    {
        $taxableAmount = max(0, (float)$taxableAmount);
        $maxRate = (float)$maxRatePercent / 100;

        // A zero maximum rate means the income is not taxable for this period
        if ($taxableAmount <= 0 || $maxRate <= 0) {
            return 0.0;
        }

        // No rate table configured: fall back to the maximum rate as a flat rate
        if (empty($brackets)) {
            return $taxableAmount * $maxRate;
        }

        $periodsPerYear = max(1, (int)$periodsPerYear);

        $tax = 0.0;
        $remaining = $taxableAmount;
        $lastRate = 0.0;
        foreach ($brackets as $bracket) {
            $lastRate = (float)$bracket['rate'];
            if ($remaining <= 0) {
                break;
            }

            $width = (isset($bracket['width']) && $bracket['width'] !== null)
                ? ((float)$bracket['width'] / $periodsPerYear)
                : $remaining;

            $band = min($remaining, $width);
            $tax += $band * ($lastRate / 100);
            $remaining -= $band;
        }

        // Table did not end with a "balance" band - tax what is left at the top rate
        if ($remaining > 0) {
            $tax += $remaining * ($lastRate / 100);
        }

        // The concessionary rate is a maximum, not a flat rate: pay the lower of the
        // progressive computation and the capped rate
        return min($tax, $taxableAmount * $maxRate);
    }
}
