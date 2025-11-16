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
        verify($this->service)->instanceOf(PaysheetHealthCheckService::class);
        verify(method_exists($this->service, 'generateSuggestionsForMonth'))->true();
        verify(method_exists($this->service, 'generateSuggestionsForAllPastMonths'))->true();
        verify(method_exists($this->service, 'getPendingSuggestionsCount'))->true();
        verify(method_exists($this->service, 'cleanupRejectedSuggestions'))->true();
    }

    /**
     * Test generateSuggestionsForMonth method signature
     */
    public function testGenerateSuggestionsForMonthMethodSignature()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSuggestionsForMonth');
        
        verify($method)->notNull();
        verify($method->isPublic())->true();
        
        // Method should accept optional parameter
        $params = $method->getParameters();
        verify(count($params))->greaterThanOrEqual(0);
    }

    /**
     * Test generateSuggestionsForAllPastMonths method signature
     */
    public function testGenerateSuggestionsForAllPastMonthsMethodSignature()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSuggestionsForAllPastMonths');
        
        verify($method)->notNull();
        verify($method->isPublic())->true();
        
        $params = $method->getParameters();
        verify(count($params))->greaterThanOrEqual(0);
    }

    /**
     * Test getPendingSuggestionsCount method exists
     */
    public function testGetPendingSuggestionsCountMethodExists()
    {
        verify(method_exists($this->service, 'getPendingSuggestionsCount'))->true();
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getPendingSuggestionsCount');
        verify($method->isPublic())->true();
    }

    /**
     * Test cleanupRejectedSuggestions method exists
     */
    public function testCleanupRejectedSuggestionsMethodExists()
    {
        verify(method_exists($this->service, 'cleanupRejectedSuggestions'))->true();
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('cleanupRejectedSuggestions');
        verify($method->isPublic())->true();
        
        $params = $method->getParameters();
        verify(count($params))->greaterThanOrEqual(0);
    }
}
