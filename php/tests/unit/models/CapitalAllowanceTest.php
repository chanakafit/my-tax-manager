<?php

namespace tests\unit\models;

use app\models\CapitalAllowance;
use Codeception\Test\Unit;

/**
 * Test CapitalAllowance model - straight line 20% of cost per year, 5 years
 */
class CapitalAllowanceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(CapitalAllowance::tableName())->equals('{{%capital_allowance}}');
    }

    /**
     * Test the write-off constants
     */
    public function testWriteOffConstants()
    {
        verify(CapitalAllowance::ANNUAL_PERCENTAGE)->equals(20.0);
        verify(CapitalAllowance::MAX_YEARS)->equals(5);
    }

    /**
     * Percentage defaults to 20 when not set
     */
    public function testPercentageDefaultsTo20()
    {
        $model = new CapitalAllowance();
        $model->validate(['percentage_claimed']);

        verify($model->percentage_claimed)->equals(20.0);
    }

    /**
     * Any percentage other than 20 is rejected - a 10% claim would spread the
     * asset over 10 years, which the 5 year cap can never complete
     */
    public function testOnlyTwentyPercentIsAccepted()
    {
        $model = new CapitalAllowance();

        $model->percentage_claimed = 20;
        $model->validate(['percentage_claimed']);
        verify($model->hasErrors('percentage_claimed'))->false();

        $model->clearErrors();
        $model->percentage_claimed = 10;
        $model->validate(['percentage_claimed']);
        verify($model->hasErrors('percentage_claimed'))->true();

        $model->clearErrors();
        $model->percentage_claimed = 100;
        $model->validate(['percentage_claimed']);
        verify($model->hasErrors('percentage_claimed'))->true();
    }

    /**
     * Allowances run from year 1 to year 5 only
     */
    public function testYearNumberRange()
    {
        $model = new CapitalAllowance();

        foreach ([1, 5] as $year) {
            $model->clearErrors();
            $model->year_number = $year;
            $model->validate(['year_number']);
            verify($model->hasErrors('year_number'))->false();
        }

        foreach ([0, 6, 10] as $year) {
            $model->clearErrors();
            $model->year_number = $year;
            $model->validate(['year_number']);
            verify($model->hasErrors('year_number'))->true();
        }
    }
}
