<?php

namespace tests\unit\components;

use app\components\ExpenseHealthCheckService;
use app\models\Expense;
use app\models\ExpenseSuggestion;
use app\models\ExpenseCategory;
use app\models\Vendor;
use Codeception\Test\Unit;
use Yii;

/**
 * Test ExpenseHealthCheckService business logic
 * 
 * This service is critical for expense pattern detection and suggestion generation
 */
class ExpenseHealthCheckServiceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    /**
     * @var ExpenseHealthCheckService
     */
    protected $service;

    protected function _before()
    {
        $this->service = new ExpenseHealthCheckService();
    }

    protected function _after()
    {
    }

    /**
     * Test that service can be instantiated
     */
    public function testServiceInstantiation()
    {
        verify($this->service)->notNull();
        verify($this->service)->isInstanceOf(ExpenseHealthCheckService::class);
    }

    /**
     * Test generateSuggestionsForMonth returns proper structure
     */
    public function testGenerateSuggestionsForMonthReturnsProperStructure()
    {
        $result = $this->service->generateSuggestionsForMonth();
        
        verify($result)->isArray();
        verify(array_key_exists('created', $result))->true();
        verify(array_key_exists('skipped', $result))->true();
        verify(array_key_exists('errors', $result))->true();
        verify($result['created'])->greaterOrEquals(0);
        verify($result['skipped'])->greaterOrEquals(0);
        verify($result['errors'])->isArray();
    }

    /**
     * Test that future months are not processed
     */
    public function testDoesNotGenerateSuggestionsForFutureMonths()
    {
        $futureMonth = date('Y-m-d', strtotime('+2 months'));
        $result = $this->service->generateSuggestionsForMonth($futureMonth);
        
        verify($result['created'])->equals(0);
        verify($result['skipped'])->equals(0);
        verify($result['errors'])->isEmpty();
    }

    /**
     * Test consecutive month counting logic
     */
    public function testCountConsecutiveMonthsWithDirectSequence()
    {
        $months = [
            ['month' => '2024-01-01'],
            ['month' => '2024-02-01'],
            ['month' => '2024-03-01'],
        ];
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('countConsecutiveMonths');
        $method->setAccessible(true);
        
        $count = $method->invokeArgs($this->service, [$months]);
        
        verify($count)->equals(3);
    }

    /**
     * Test consecutive month counting with gap
     */
    public function testCountConsecutiveMonthsWithOneGap()
    {
        $months = [
            ['month' => '2024-01-01'],
            ['month' => '2024-02-01'],
            // Gap in March
            ['month' => '2024-04-01'],
        ];
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('countConsecutiveMonths');
        $method->setAccessible(true);
        
        $count = $method->invokeArgs($this->service, [$months]);
        
        // Should allow one gap
        verify($count)->equals(3);
    }

    /**
     * Test consecutive month counting with multiple gaps
     */
    public function testCountConsecutiveMonthsWithMultipleGaps()
    {
        $months = [
            ['month' => '2024-01-01'],
            ['month' => '2024-02-01'],
            // Gap in March
            ['month' => '2024-04-01'],
            // Gap in May
            ['month' => '2024-06-01'],
        ];
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('countConsecutiveMonths');
        $method->setAccessible(true);
        
        $count = $method->invokeArgs($this->service, [$months]);
        
        // Should only count up to first multi-gap
        verify($count)->lessThan(4);
    }

    /**
     * Test getPendingSuggestionsCount
     */
    public function testGetPendingSuggestionsCount()
    {
        $count = $this->service->getPendingSuggestionsCount();
        
        verify($count)->greaterOrEquals(0);
        verify(is_int($count))->true();
    }

    /**
     * Test resetIgnoredSuggestions returns count
     */
    public function testResetIgnoredSuggestionsReturnsCount()
    {
        $count = $this->service->resetIgnoredSuggestions(1, 1);
        
        verify($count)->greaterOrEquals(0);
        verify(is_int($count))->true();
    }

    /**
     * Test generateSuggestionsForAllPastMonths structure
     */
    public function testGenerateSuggestionsForAllPastMonthsReturnsProperStructure()
    {
        $result = $this->service->generateSuggestionsForAllPastMonths(3);
        
        verify($result)->isArray();
        verify(array_key_exists('created', $result))->true();
        verify(array_key_exists('skipped', $result))->true();
        verify(array_key_exists('errors', $result))->true();
        verify(array_key_exists('months_scanned', $result))->true();
        verify($result['months_scanned'])->isArray();
        verify(count($result['months_scanned']))->equals(4); // 0 to 3 = 4 months
    }

    /**
     * Test cleanupTemporaryIgnores executes without error
     */
    public function testCleanupTemporaryIgnores()
    {
        $count = $this->service->cleanupTemporaryIgnores();
        
        verify($count)->greaterOrEquals(0);
        verify(is_int($count))->true();
    }

    /**
     * Test that MIN_PATTERN_MONTHS constant exists
     */
    public function testMinPatternMonthsConstantExists()
    {
        $reflection = new \ReflectionClass($this->service);
        verify($reflection->getConstant('MIN_PATTERN_MONTHS'))->notNull();
        verify($reflection->getConstant('MIN_PATTERN_MONTHS'))->equals(2);
    }

    /**
     * Test that LOOKBACK_MONTHS constant exists
     */
    public function testLookbackMonthsConstantExists()
    {
        $reflection = new \ReflectionClass($this->service);
        verify($reflection->getConstant('LOOKBACK_MONTHS'))->notNull();
        verify($reflection->getConstant('LOOKBACK_MONTHS'))->equals(6);
    }

    /**
     * Test pattern detection with empty data
     */
    public function testDetectExpensePatternsWithNoData()
    {
        $targetDate = new \DateTime('first day of this month');
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('detectExpensePatterns');
        $method->setAccessible(true);
        
        $patterns = $method->invokeArgs($this->service, [$targetDate]);
        
        verify($patterns)->isArray();
    }

    /**
     * Test that generateSuggestionsForMonth handles null parameter
     */
    public function testGenerateSuggestionsForCurrentMonthWithNullParameter()
    {
        $result = $this->service->generateSuggestionsForMonth(null);
        
        verify($result)->isArray();
        verify($result['created'])->greaterOrEquals(0);
    }

    /**
     * Test that generateSuggestionsForMonth handles specific date
     */
    public function testGenerateSuggestionsForSpecificMonth()
    {
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        $result = $this->service->generateSuggestionsForMonth($lastMonth);
        
        verify($result)->isArray();
        verify($result['created'])->greaterOrEquals(0);
    }
}
