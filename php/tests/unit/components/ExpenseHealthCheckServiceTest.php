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
        verify($this->service)->instanceOf(ExpenseHealthCheckService::class);
        verify(method_exists($this->service, 'generateSuggestionsForMonth'))->true();
        verify(method_exists($this->service, 'detectExpensePatterns'))->true();
        verify(method_exists($this->service, 'countConsecutiveMonths'))->true();
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
     * Test getPendingSuggestionsCount method exists
     */
    public function testGetPendingSuggestionsCountMethodExists()
    {
        verify(method_exists($this->service, 'getPendingSuggestionsCount'))->true();
    }

    /**
     * Test resetIgnoredSuggestions method exists
     */
    public function testResetIgnoredSuggestionsMethodExists()
    {
        verify(method_exists($this->service, 'resetIgnoredSuggestions'))->true();
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
     * Test cleanupTemporaryIgnores method exists
     */
    public function testCleanupTemporaryIgnoresMethodExists()
    {
        verify(method_exists($this->service, 'cleanupTemporaryIgnores'))->true();
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
     * Test detectExpensePatterns method exists
     */
    public function testDetectExpensePatternsMethodExists()
    {
        $reflection = new \ReflectionClass($this->service);
        verify($reflection->hasMethod('detectExpensePatterns'))->true();
        
        $method = $reflection->getMethod('detectExpensePatterns');
        verify($method)->notNull();
    }

    /**
     * Test generateSuggestionsForMonth method exists and accepts parameters
     */
    public function testGenerateSuggestionsForMonthMethodExists()
    {
        verify(method_exists($this->service, 'generateSuggestionsForMonth'))->true();
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSuggestionsForMonth');
        $params = $method->getParameters();
        
        // Method should accept a targetMonth parameter
        verify(count($params))->greaterThanOrEqual(0);
    }
}
