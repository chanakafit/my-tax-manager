<?php

namespace tests\unit\models;

use app\models\SystemConfig;
use Codeception\Test\Unit;
use Yii;

/**
 * Test SystemConfig model
 */
class SystemConfigTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // Clean up test data
        SystemConfig::deleteAll(['like', 'config_key', 'test_']);
        SystemConfig::deleteAll(['in', 'config_key', ['business_address_line1', 'business_address_line2', 'business_city', 'business_postal_code', 'business_province', 'bank_swift_code', 'bank_name', 'bank_account_number']]);
        SystemConfig::clearCache();
    }

    protected function _after()
    {
        // Clean up test data
        SystemConfig::deleteAll(['like', 'config_key', 'test_']);
        SystemConfig::deleteAll(['in', 'config_key', ['business_address_line1', 'business_address_line2', 'business_city', 'business_postal_code', 'business_province', 'bank_swift_code', 'bank_name', 'bank_account_number']]);
        SystemConfig::clearCache();
    }

    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        $model = new SystemConfig();
        $this->assertInstanceOf(SystemConfig::class, $model);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(SystemConfig::tableName())->stringContainsString('system_config');
    }

    /**
     * Test required fields
     */
    public function testRequiredFields()
    {
        $model = new SystemConfig();

        verify($model->validate())->false();
        verify($model->hasErrors('config_key'))->true();
        verify($model->hasErrors('config_type'))->true();
        verify($model->hasErrors('category'))->true();
    }

    /**
     * Test creating a configuration
     */
    public function testCreateConfig()
    {
        $model = new SystemConfig();
        $model->config_key = 'test_config_key';
        $model->config_value = 'test_value';
        $model->config_type = SystemConfig::TYPE_STRING;
        $model->category = SystemConfig::CATEGORY_SYSTEM;
        $model->description = 'Test configuration';

        verify($model->save())->true();
        verify($model->id)->notNull();

        // Verify it was saved
        $saved = SystemConfig::findOne(['config_key' => 'test_config_key']);
        verify($saved)->notNull();
        verify($saved->config_value)->equals('test_value');
    }

    /**
     * Test unique key constraint
     */
    public function testUniqueKeyConstraint()
    {
        $model1 = new SystemConfig();
        $model1->config_key = 'test_config_key';
        $model1->config_value = 'value1';
        $model1->config_type = SystemConfig::TYPE_STRING;
        $model1->category = SystemConfig::CATEGORY_SYSTEM;

        verify($model1->save())->true();

        // Try to create duplicate
        $model2 = new SystemConfig();
        $model2->config_key = 'test_config_key';
        $model2->config_value = 'value2';
        $model2->config_type = SystemConfig::TYPE_STRING;
        $model2->category = SystemConfig::CATEGORY_SYSTEM;

        verify($model2->save())->false();
        verify($model2->hasErrors('config_key'))->true();
    }

    /**
     * Test get method
     */
    public function testGetMethod()
    {
        // Create a test config
        $model = new SystemConfig();
        $model->config_key = 'test_config_key';
        $model->config_value = 'test_value';
        $model->config_type = SystemConfig::TYPE_STRING;
        $model->category = SystemConfig::CATEGORY_SYSTEM;
        $model->save();

        // Test get
        $value = SystemConfig::get('test_config_key');
        verify($value)->equals('test_value');

        // Test default value for non-existent key
        $value = SystemConfig::get('non_existent_key', 'default_value');
        verify($value)->equals('default_value');
    }

    /**
     * Test set method
     */
    public function testSetMethod()
    {
        // Set a new config
        $result = SystemConfig::set('test_config_key', 'test_value', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_SYSTEM, 'Test config');
        verify($result)->true();

        // Verify it was saved
        $value = SystemConfig::get('test_config_key');
        verify($value)->equals('test_value');

        // Update existing config
        $result = SystemConfig::set('test_config_key', 'updated_value');
        verify($result)->true();

        $value = SystemConfig::get('test_config_key');
        verify($value)->equals('updated_value');
    }

    /**
     * Test integer type handling
     */
    public function testIntegerType()
    {
        SystemConfig::set('test_integer', 42, SystemConfig::TYPE_INTEGER, SystemConfig::CATEGORY_SYSTEM);

        $value = SystemConfig::get('test_integer');
        verify($value)->equals(42);
        $this->assertIsInt($value);
    }

    /**
     * Test boolean type handling
     */
    public function testBooleanType()
    {
        SystemConfig::set('test_boolean', true, SystemConfig::TYPE_BOOLEAN, SystemConfig::CATEGORY_SYSTEM);

        $value = SystemConfig::get('test_boolean');
        verify($value)->true();
        $this->assertIsBool($value);

        SystemConfig::set('test_boolean', false, SystemConfig::TYPE_BOOLEAN, SystemConfig::CATEGORY_SYSTEM);

        $value = SystemConfig::get('test_boolean');
        verify($value)->false();
    }

    /**
     * Test JSON type handling
     */
    public function testJsonType()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        SystemConfig::set('test_json', $data, SystemConfig::TYPE_JSON, SystemConfig::CATEGORY_SYSTEM);

        $value = SystemConfig::get('test_json');
        $this->assertIsArray($value);
        verify($value['key1'])->equals('value1');
        verify($value['key2'])->equals('value2');
    }

    /**
     * Test get by category
     */
    public function testGetByCategory()
    {
        // Create test configs
        SystemConfig::set('test_config_1', 'value1', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BUSINESS);
        SystemConfig::set('test_config_2', 'value2', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BUSINESS);
        SystemConfig::set('test_config_3', 'value3', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_SYSTEM);

        $businessConfigs = SystemConfig::getByCategory(SystemConfig::CATEGORY_BUSINESS);

        $this->assertArrayHasKey('test_config_1', $businessConfigs);
        $this->assertArrayHasKey('test_config_2', $businessConfigs);
        $this->assertArrayNotHasKey('test_config_3', $businessConfigs);
    }

    /**
     * Test get all configurations
     */
    public function testGetAll()
    {
        SystemConfig::set('test_config_1', 'value1', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_SYSTEM);
        SystemConfig::set('test_config_2', 'value2', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_SYSTEM);

        $allConfigs = SystemConfig::getAll();

        $this->assertIsArray($allConfigs);
        verify(count($allConfigs))->greaterThanOrEqual(2);
    }

    /**
     * Test cache functionality
     */
    public function testCache()
    {
        // Create a config
        SystemConfig::set('test_config_key', 'original_value', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_SYSTEM);

        // Get it (should be cached)
        $value1 = SystemConfig::get('test_config_key');
        verify($value1)->equals('original_value');

        // Manually update in DB (bypass model)
        Yii::$app->db->createCommand()
            ->update(SystemConfig::tableName(), ['config_value' => 'updated_value'], ['config_key' => 'test_config_key'])
            ->execute();

        // Get it again (should still be cached)
        $value2 = SystemConfig::get('test_config_key');
        verify($value2)->equals('original_value'); // Still cached

        // Clear cache
        SystemConfig::clearCache();

        // Get it again (should be fresh from DB)
        $value3 = SystemConfig::get('test_config_key');
        verify($value3)->equals('updated_value'); // Fresh from DB
    }

    /**
     * Test business address helper
     */
    public function testBusinessAddressHelper()
    {
        SystemConfig::set('business_address_line1', 'Line 1', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BUSINESS);
        SystemConfig::set('business_address_line2', 'Line 2', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BUSINESS);
        SystemConfig::set('business_city', 'City', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BUSINESS);
        SystemConfig::set('business_postal_code', '12345', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BUSINESS);
        SystemConfig::set('business_province', 'Province', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BUSINESS);

        $address = SystemConfig::getBusinessAddress();

        $this->assertIsArray($address);
        verify($address['line1'])->equals('Line 1');
        verify($address['line2'])->equals('Line 2');
        verify($address['city'])->equals('City');
        verify($address['postalCode'])->equals('12345');
        verify($address['province'])->equals('Province');
    }

    /**
     * Test banking details helper
     */
    public function testBankingDetailsHelper()
    {
        SystemConfig::set('bank_swift_code', 'SWIFT123', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BANKING);
        SystemConfig::set('bank_name', 'Test Bank', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BANKING);
        SystemConfig::set('bank_account_number', '123456789', SystemConfig::TYPE_STRING, SystemConfig::CATEGORY_BANKING);

        $banking = SystemConfig::getBankingDetails();

        $this->assertIsArray($banking);
        verify($banking['swiftCode'])->equals('SWIFT123');
        verify($banking['bankName'])->equals('Test Bank');
        verify($banking['accountNumber'])->equals('123456789');
    }

    /**
     * Test updating configuration
     */
    public function testUpdateConfig()
    {
        $model = new SystemConfig();
        $model->config_key = 'test_config_key';
        $model->config_value = 'original_value';
        $model->config_type = SystemConfig::TYPE_STRING;
        $model->category = SystemConfig::CATEGORY_SYSTEM;
        $model->save();

        // Update
        $model->config_value = 'updated_value';
        verify($model->save())->true();

        // Verify update
        $updated = SystemConfig::findOne(['config_key' => 'test_config_key']);
        verify($updated->config_value)->equals('updated_value');
    }

    /**
     * Test deleting configuration
     */
    public function testDeleteConfig()
    {
        $model = new SystemConfig();
        $model->config_key = 'test_config_key';
        $model->config_value = 'test_value';
        $model->config_type = SystemConfig::TYPE_STRING;
        $model->category = SystemConfig::CATEGORY_SYSTEM;
        $model->save();

        $id = $model->id;

        verify($model->delete())->notEquals(false);

        // Verify deletion
        $deleted = SystemConfig::findOne($id);
        verify($deleted)->null();
    }

    /**
     * Test auto type detection
     */
    public function testAutoTypeDetection()
    {
        // String
        SystemConfig::set('test_string', 'value', null, SystemConfig::CATEGORY_SYSTEM);
        $config = SystemConfig::findOne(['config_key' => 'test_string']);
        verify($config->config_type)->equals(SystemConfig::TYPE_STRING);

        // Integer
        SystemConfig::set('test_int', 123, null, SystemConfig::CATEGORY_SYSTEM);
        $config = SystemConfig::findOne(['config_key' => 'test_int']);
        verify($config->config_type)->equals(SystemConfig::TYPE_INTEGER);

        // Boolean
        SystemConfig::set('test_bool', true, null, SystemConfig::CATEGORY_SYSTEM);
        $config = SystemConfig::findOne(['config_key' => 'test_bool']);
        verify($config->config_type)->equals(SystemConfig::TYPE_BOOLEAN);

        // Array
        SystemConfig::set('test_array', ['a', 'b'], null, SystemConfig::CATEGORY_SYSTEM);
        $config = SystemConfig::findOne(['config_key' => 'test_array']);
        verify($config->config_type)->equals(SystemConfig::TYPE_JSON);
    }
}

