<?php

namespace tests\unit\components;

use app\components\PaysheetHealthCheckService;
use app\models\Employee;
use app\models\Paysheet;
use app\models\PaysheetSuggestion;
use Codeception\Test\Unit;
use Yii;

/**
 * Test PaysheetHealthCheckService business logic
 * 
 * This service is critical for paysheet suggestion generation
 */
class PaysheetHealthCheckServiceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    /**
     * @var PaysheetHealthCheckService
     */
    protected $service;

    protected function _before()
    {
        $this->service = new PaysheetHealthCheckService();
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
        verify($this->service)->isInstanceOf(PaysheetHealthCheckService::class);
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
        verify(count($result['months_scanned']))->equals(3);
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
     * Test cleanupRejectedSuggestions executes without error
     */
    public function testCleanupRejectedSuggestions()
    {
        $count = $this->service->cleanupRejectedSuggestions(90);
        
        verify($count)->greaterOrEquals(0);
        verify(is_int($count))->true();
    }

    /**
     * Test cleanupRejectedSuggestions with custom days
     */
    public function testCleanupRejectedSuggestionsWithCustomDays()
    {
        $count = $this->service->cleanupRejectedSuggestions(30);
        
        verify($count)->greaterOrEquals(0);
        verify(is_int($count))->true();
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
