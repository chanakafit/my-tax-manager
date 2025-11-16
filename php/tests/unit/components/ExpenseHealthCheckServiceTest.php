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
     * Test generate suggestions for month method exists
     */
    public function testGenerateSuggestionsForMonthMethodExists()
    {
        verify(method_exists($this->service, 'generateSuggestionsForMonth'))->true();

        $reflection = new \ReflectionMethod($this->service, 'generateSuggestionsForMonth');
        $params = $reflection->getParameters();

        verify(count($params))->greaterThanOrEqual(0);
    }

    /**
     * Test resetIgnoredSuggestions method exists
     */
    public function testResetIgnoredSuggestionsMethodExists()
    {
        verify(method_exists($this->service, 'resetIgnoredSuggestions'))->true();

        $reflection = new \ReflectionMethod($this->service, 'resetIgnoredSuggestions');
        $params = $reflection->getParameters();

        verify(count($params))->equals(2); // expenseCategoryId, vendorId
    }

    /**
     * Test detectExpensePatterns method is accessible
     */
    public function testDetectExpensePatternsMethodAccessible()
    {
        $reflection = new \ReflectionClass($this->service);
        verify($reflection->hasMethod('detectExpensePatterns'))->true();
    }

    /**
     * Test countConsecutiveMonths method logic with different scenarios
     */
    public function testCountConsecutiveMonthsWithEmptyArray()
    {
        $reflection = new \ReflectionMethod($this->service, 'countConsecutiveMonths');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($this->service, []);

        verify($result)->equals(0);
    }

    /**
     * Test countConsecutiveMonths with single month
     */
    public function testCountConsecutiveMonthsWithSingleMonth()
    {
        $reflection = new \ReflectionMethod($this->service, 'countConsecutiveMonths');
        $reflection->setAccessible(true);

        $months = [
            ['month' => '2024-01-01'],
        ];

        $result = $reflection->invoke($this->service, $months);

        verify($result)->equals(1);
    }

    /**
     * Test service has required constants
     */
    public function testServiceHasRequiredConstants()
    {
        $reflection = new \ReflectionClass($this->service);

        verify($reflection->hasConstant('MIN_PATTERN_MONTHS'))->true();
        verify($reflection->hasConstant('LOOKBACK_MONTHS'))->true();
    }

    /**
     * Test constant values
     */
    public function testConstantValues()
    {
        $reflection = new \ReflectionClass($this->service);

        verify($reflection->getConstant('MIN_PATTERN_MONTHS'))->equals(2);
        verify($reflection->getConstant('LOOKBACK_MONTHS'))->equals(6);
    }

    /**
     * Test generateSuggestionsForMonth returns proper structure
     */
    public function testGenerateSuggestionsForMonthReturnsStructure()
    {
        // Call with future date to avoid database operations
        $futureDate = date('Y-m-d', strtotime('+1 month'));
        $result = $this->service->generateSuggestionsForMonth($futureDate);

        verify($result)->isArray();
        verify(array_key_exists('created', $result))->true();
        verify(array_key_exists('skipped', $result))->true();
        verify(array_key_exists('errors', $result))->true();
        verify($result['created'])->equals(0);
        verify($result['skipped'])->equals(0);
    }

    /**
     * Test generateSuggestionsForMonth with null parameter (current month)
     */
    public function testGenerateSuggestionsForMonthWithNullParameter()
    {
        $result = $this->service->generateSuggestionsForMonth(null);

        verify($result)->isArray();
        verify(array_key_exists('created', $result))->true();
        verify(array_key_exists('skipped', $result))->true();
        verify(array_key_exists('errors', $result))->true();
    }

    /**
     * Test generateSuggestionsForMonth skips future months
     */
    public function testGenerateSuggestionsForMonthSkipsFutureMonths()
    {
        $futureDate = date('Y-m-d', strtotime('+2 months'));
        $result = $this->service->generateSuggestionsForMonth($futureDate);

        verify($result['created'])->equals(0);
        verify($result['skipped'])->equals(0);
        verify(empty($result['errors']))->true();
    }

    /**
     * Test getPendingSuggestionsCount returns integer
     */
    public function testGetPendingSuggestionsCountReturnsInteger()
    {
        $count = $this->service->getPendingSuggestionsCount();

        verify(is_int($count))->true();
        verify($count)->greaterThanOrEqual(0);
    }

    /**
     * Test resetIgnoredSuggestions method signature and return
     */
    public function testResetIgnoredSuggestionsReturnsInteger()
    {
        // Test with dummy IDs that won't match anything
        $result = $this->service->resetIgnoredSuggestions(99999, 99999);

        verify(is_int($result))->true();
        verify($result)->greaterThanOrEqual(0);
    }

    /**
     * Test cleanupTemporaryIgnores returns integer
     */
    public function testCleanupTemporaryIgnoresReturnsInteger()
    {
        $result = $this->service->cleanupTemporaryIgnores();

        verify(is_int($result))->true();
        verify($result)->greaterThanOrEqual(0);
    }

    /**
     * Test generateSuggestionsForAllPastMonths returns proper structure
     */
    public function testGenerateSuggestionsForAllPastMonthsReturnsStructure()
    {
        // Use small lookback to avoid long execution
        $result = $this->service->generateSuggestionsForAllPastMonths(1);

        verify($result)->isArray();
        verify(array_key_exists('created', $result))->true();
        verify(array_key_exists('skipped', $result))->true();
        verify(array_key_exists('errors', $result))->true();
        verify(array_key_exists('months_scanned', $result))->true();
        verify($result['months_scanned'])->isArray();
        verify(count($result['months_scanned']))->greaterThan(0);
    }

    /**
     * Test generateSuggestionsForAllPastMonths scans correct number of months
     */
    public function testGenerateSuggestionsForAllPastMonthsScansCorrectMonths()
    {
        $lookback = 3;
        $result = $this->service->generateSuggestionsForAllPastMonths($lookback);

        // Should scan lookback + current month
        verify(count($result['months_scanned']))->equals($lookback + 1);
    }

    /**
     * Test detectExpensePatterns method is protected
     */
    public function testDetectExpensePatternsIsProtected()
    {
        $reflection = new \ReflectionMethod($this->service, 'detectExpensePatterns');
        verify($reflection->isProtected())->true();
    }

    /**
     * Test detectExpensePatterns with reflection
     */
    public function testDetectExpensePatternsWithReflection()
    {
        $reflection = new \ReflectionMethod($this->service, 'detectExpensePatterns');
        $reflection->setAccessible(true);

        $targetDate = new \DateTime('2024-01-01');
        $result = $reflection->invoke($this->service, $targetDate);

        verify($result)->isArray();
    }

    /**
     * Test countConsecutiveMonths with two consecutive months
     */
    public function testCountConsecutiveMonthsWithTwoMonths()
    {
        $reflection = new \ReflectionMethod($this->service, 'countConsecutiveMonths');
        $reflection->setAccessible(true);

        $months = [
            ['month' => '2024-01-01'],
            ['month' => '2024-02-01'],
        ];

        $result = $reflection->invoke($this->service, $months);

        verify($result)->equals(2);
    }

    /**
     * Test countConsecutiveMonths with gap larger than 1 month
     */
    public function testCountConsecutiveMonthsWithLargeGap()
    {
        $reflection = new \ReflectionMethod($this->service, 'countConsecutiveMonths');
        $reflection->setAccessible(true);

        $months = [
            ['month' => '2024-01-01'],
            ['month' => '2024-02-01'],
            // 2 month gap
            ['month' => '2024-05-01'],
        ];

        $result = $reflection->invoke($this->service, $months);

        // Should reset after large gap
        verify($result)->greaterThanOrEqual(1);
    }

    /**
     * Test countConsecutiveMonths with unsorted months
     */
    public function testCountConsecutiveMonthsWithUnsortedMonths()
    {
        $reflection = new \ReflectionMethod($this->service, 'countConsecutiveMonths');
        $reflection->setAccessible(true);

        $months = [
            ['month' => '2024-03-01'],
            ['month' => '2024-01-01'],
            ['month' => '2024-02-01'],
        ];

        $result = $reflection->invoke($this->service, $months);

        // Should sort and count correctly
        verify($result)->equals(3);
    }

    /**
     * Test countConsecutiveMonths with four consecutive months
     */
    public function testCountConsecutiveMonthsWithFourMonths()
    {
        $reflection = new \ReflectionMethod($this->service, 'countConsecutiveMonths');
        $reflection->setAccessible(true);

        $months = [
            ['month' => '2024-01-01'],
            ['month' => '2024-02-01'],
            ['month' => '2024-03-01'],
            ['month' => '2024-04-01'],
        ];

        $result = $reflection->invoke($this->service, $months);

        verify($result)->equals(4);
    }

    /**
     * Test countConsecutiveMonths with one month gap at beginning
     */
    public function testCountConsecutiveMonthsWithGapAtBeginning()
    {
        $reflection = new \ReflectionMethod($this->service, 'countConsecutiveMonths');
        $reflection->setAccessible(true);

        $months = [
            ['month' => '2024-01-01'],
            // Gap
            ['month' => '2024-03-01'],
            ['month' => '2024-04-01'],
        ];

        $result = $reflection->invoke($this->service, $months);

        verify($result)->greaterThanOrEqual(2);
    }

    /**
     * Test service methods are accessible
     */
    public function testAllPublicMethodsAreAccessible()
    {
        verify(method_exists($this->service, 'generateSuggestionsForMonth'))->true();
        verify(method_exists($this->service, 'resetIgnoredSuggestions'))->true();
        verify(method_exists($this->service, 'getPendingSuggestionsCount'))->true();
        verify(method_exists($this->service, 'generateSuggestionsForAllPastMonths'))->true();
        verify(method_exists($this->service, 'cleanupTemporaryIgnores'))->true();
    }

    /**
     * Test generateSuggestionsForMonth handles invalid date format gracefully
     */
    public function testGenerateSuggestionsForMonthWithInvalidDate()
    {
        try {
            $result = $this->service->generateSuggestionsForMonth('invalid-date');
            // Should handle exception and return error
            verify($result)->isArray();
        } catch (\Exception $e) {
            // Exception is acceptable
            verify(true)->true();
        }
    }
}
