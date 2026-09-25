<?php

namespace tests\unit\models;

use app\models\TaxConfig;
use Codeception\Test\Unit;

/**
 * Test TaxConfig rate table logic (Y/A 2025/2026 onwards, IRD notice PN/IT/2025-01)
 */
class TaxConfigTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Progressive rates for individuals from 01.04.2025
     */
    private function brackets()
    {
        return [
            ['width' => 1000000, 'rate' => 6],
            ['width' => 500000, 'rate' => 18],
            ['width' => 500000, 'rate' => 24],
            ['width' => 500000, 'rate' => 30],
            ['width' => null, 'rate' => 36],
        ];
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(TaxConfig::tableName())->equals('{{%tax_config}}');
    }

    /**
     * Below the crossover the progressive rates are lower than the 15% maximum rate
     */
    public function testProgressiveRatesApplyBelowTheCap()
    {
        // 1,000,000 @ 6%
        verify(TaxConfig::applyRateTable(1000000, $this->brackets(), 15))->equals(60000.0);

        // 1,000,000 @ 6% + 200,000 @ 18% = 96,000 (15% would be 180,000)
        verify(TaxConfig::applyRateTable(1200000, $this->brackets(), 15))->equals(96000.0);

        // 60,000 + 90,000 + 120,000 = 270,000 (15% would be 300,000)
        verify(TaxConfig::applyRateTable(2000000, $this->brackets(), 15))->equals(270000.0);
    }

    /**
     * The progressive computation and the 15% cap meet at a taxable income of 2,200,000
     */
    public function testCrossoverPoint()
    {
        verify(TaxConfig::applyRateTable(2200000, $this->brackets(), 15))->equals(330000.0);
    }

    /**
     * Above the crossover the 15% maximum rate caps the liability
     */
    public function testMaximumRateCapsTheLiability()
    {
        // Progressive would be 420,000 + 36% of 1,700,000 = 1,032,000
        verify(TaxConfig::applyRateTable(4200000, $this->brackets(), 15))->equals(630000.0);

        // 15% of 10,000,000
        verify(TaxConfig::applyRateTable(10000000, $this->brackets(), 15))->equals(1500000.0);
    }

    /**
     * A quarterly instalment uses quarter-width bands so four quarters of equal
     * profit add up to the annual liability
     */
    public function testQuarterlyBandsAreScaled()
    {
        // 300,000 per quarter = 1,200,000 for the year
        $quarterly = TaxConfig::applyRateTable(300000, $this->brackets(), 15, 4);
        verify($quarterly)->equals(24000.0);
        verify($quarterly * 4)->equals(TaxConfig::applyRateTable(1200000, $this->brackets(), 15));
    }

    /**
     * Zero maximum rate means exempt - service exports before 01.04.2025
     */
    public function testZeroRateMeansNoTax()
    {
        verify(TaxConfig::applyRateTable(5000000, $this->brackets(), 0))->equals(0.0);
    }

    /**
     * Nothing to tax
     */
    public function testNoTaxableIncome()
    {
        verify(TaxConfig::applyRateTable(0, $this->brackets(), 15))->equals(0.0);
        verify(TaxConfig::applyRateTable(-500000, $this->brackets(), 15))->equals(0.0);
    }

    /**
     * Without a configured rate table the maximum rate is applied as a flat rate
     */
    public function testFlatRateFallbackWithoutBrackets()
    {
        verify(TaxConfig::applyRateTable(1000000, [], 15))->equals(150000.0);
    }

    /**
     * Sri Lankan tax year runs April - March
     */
    public function testTaxYearForDate()
    {
        verify(TaxConfig::getTaxYearForDate('2025-04-01'))->equals(2025);
        verify(TaxConfig::getTaxYearForDate('2025-12-31'))->equals(2025);
        verify(TaxConfig::getTaxYearForDate('2026-03-31'))->equals(2025);
        verify(TaxConfig::getTaxYearForDate('2026-04-01'))->equals(2026);
        verify(TaxConfig::getTaxYearForDate('2025-03-31'))->equals(2024);
    }

    /**
     * Personal relief is 1,800,000 from Y/A 2025/2026 and carries forward to
     * years that are not explicitly configured
     */
    public function testYearlyReliefCarriesForward()
    {
        verify(TaxConfig::getYearlyRelief(2025))->equals(1800000.0);
        verify(TaxConfig::getYearlyRelief(2026))->equals(1800000.0);
        verify(TaxConfig::getYearlyRelief(2030))->equals(1800000.0);
        verify(TaxConfig::getYearlyRelief(2024))->equals(0.0);
    }

    /**
     * The rate table carries forward the same way
     */
    public function testTaxBracketsCarryForward()
    {
        verify(TaxConfig::getTaxBrackets(2025))->equals($this->brackets());
        verify(TaxConfig::getTaxBrackets(2026))->equals($this->brackets());
        verify(TaxConfig::getTaxBrackets(2024))->equals([]);
    }
}
