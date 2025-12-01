<?php

namespace app\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "system_config".
 *
 * @property int $id
 * @property string $config_key
 * @property string|null $config_value
 * @property string $config_type string, integer, boolean, json, array
 * @property string $category business, banking, system, invoice, etc
 * @property string|null $description
 * @property bool $is_editable
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class SystemConfig extends BaseModel
{
    // Config types
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_ARRAY = 'array';

    // Categories
    const CATEGORY_BUSINESS = 'business';
    const CATEGORY_BANKING = 'banking';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_INVOICE = 'invoice';

    /**
     * Cache for config values
     * @var array
     */
    private static $_cache = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%system_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['config_key', 'config_type', 'category'], 'required'],
            [['config_value', 'description'], 'string'],
            [['is_editable'], 'boolean'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['config_key'], 'string', 'max' => 100],
            [['config_type'], 'string', 'max' => 20],
            [['category'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 255],
            [['config_key'], 'unique'],
            [['config_type'], 'in', 'range' => [self::TYPE_STRING, self::TYPE_INTEGER, self::TYPE_BOOLEAN, self::TYPE_JSON, self::TYPE_ARRAY]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'config_key' => 'Config Key',
            'config_value' => 'Config Value',
            'config_type' => 'Config Type',
            'category' => 'Category',
            'description' => 'Description',
            'is_editable' => 'Is Editable',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Get a configuration value by key
     *
     * @param string $key The configuration key
     * @param mixed $default Default value if not found
     * @return mixed The configuration value
     */
    public static function get(string $key, $default = null)
    {
        // Check cache first
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }

        $config = self::findOne(['config_key' => $key]);

        // If not found, try alternate naming conventions (camelCase <-> snake_case)
        if (!$config) {
            $alternateKey = self::alternateKeyName($key);
            if ($alternateKey && $alternateKey !== $key) {
                $config = self::findOne(['config_key' => $alternateKey]);
            }
        }

        if (!$config) {
            return $default;
        }

        $value = self::parseValue($config->config_value, $config->config_type);

        // Cache the value
        self::$_cache[$key] = $value;

        return $value;
    }

    /**
     * Generate an alternate key name by converting between snake_case and camelCase
     *
     * @param string $key
     * @return string|null
     */
    private static function alternateKeyName(string $key): ?string
    {
        // If key contains underscore, convert to camelCase
        if (strpos($key, '_') !== false) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
        }

        // Otherwise, convert camelCase to snake_case
        $snake = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $key));
        if ($snake !== $key) {
            return $snake;
        }

        return null;
    }

    /**
     * Set a configuration value
     *
     * @param string $key The configuration key
     * @param mixed $value The value to set
     * @param string|null $type The type of value (auto-detected if null)
     * @param string $category The category
     * @param string|null $description Description
     * @return bool Whether the operation succeeded
     */
    public static function set(string $key, $value, ?string $type = null, string $category = self::CATEGORY_SYSTEM, ?string $description = null): bool
    {
        $config = self::findOne(['config_key' => $key]);

        if (!$config) {
            $config = new self();
            $config->config_key = $key;
            $config->category = $category;
            $config->description = $description;
        }

        // Auto-detect type if not provided
        if ($type === null) {
            $type = self::detectType($value);
        }

        $config->config_type = $type;
        $config->config_value = self::formatValue($value, $type);

        if ($config->save()) {
            // Update cache
            self::$_cache[$key] = $value;
            return true;
        }

        return false;
    }

    /**
     * Get all configurations by category
     *
     * @param string $category
     * @return array
     */
    public static function getByCategory(string $category): array
    {
        $configs = self::find()
            ->where(['category' => $category])
            ->orderBy(['config_key' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($configs as $config) {
            $result[$config->config_key] = self::parseValue($config->config_value, $config->config_type);
        }

        return $result;
    }

    /**
     * Get all configurations as associative array
     *
     * @return array
     */
    public static function getAll(): array
    {
        $configs = self::find()->all();

        $result = [];
        foreach ($configs as $config) {
            $result[$config->config_key] = self::parseValue($config->config_value, $config->config_type);
        }

        return $result;
    }

    /**
     * Parse value based on type
     *
     * @param string|null $value
     * @param string $type
     * @return mixed
     */
    private static function parseValue(?string $value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case self::TYPE_INTEGER:
                return (int)$value;
            case self::TYPE_BOOLEAN:
                return (bool)$value;
            case self::TYPE_JSON:
            case self::TYPE_ARRAY:
                return Json::decode($value);
            case self::TYPE_STRING:
            default:
                return $value;
        }
    }

    /**
     * Format value for storage based on type
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private static function formatValue($value, string $type): string
    {
        switch ($type) {
            case self::TYPE_JSON:
            case self::TYPE_ARRAY:
                return Json::encode($value);
            case self::TYPE_BOOLEAN:
                return $value ? '1' : '0';
            default:
                return (string)$value;
        }
    }

    /**
     * Auto-detect type of value
     *
     * @param mixed $value
     * @return string
     */
    private static function detectType($value): string
    {
        if (is_bool($value)) {
            return self::TYPE_BOOLEAN;
        }
        if (is_int($value)) {
            return self::TYPE_INTEGER;
        }
        if (is_array($value)) {
            return self::TYPE_JSON;
        }
        return self::TYPE_STRING;
    }

    /**
     * Clear the cache
     */
    public static function clearCache(): void
    {
        self::$_cache = [];
    }

    /**
     * Get business address as array
     *
     * @return array
     */
    public static function getBusinessAddress(): array
    {
        return [
            'line1' => self::get('business_address_line1', ''),
            'line2' => self::get('business_address_line2', ''),
            'city' => self::get('business_city', ''),
            'postalCode' => self::get('business_postal_code', ''),
            'province' => self::get('business_province', ''),
        ];
    }

    /**
     * Get banking details as array
     *
     * @return array
     */
    public static function getBankingDetails(): array
    {
        // Check for a composite banking details entry first (may be stored as JSON)
        $composite = self::get('banking_details', null);
        if ($composite === null) {
            $composite = self::get('bankingDetails', null);
        }

        $defaults = [
            'swiftCode' => '',
            'bankName' => '',
            'branchName' => '',
            'bankCode' => '',
            'branchCode' => '',
            'bankAddress' => '',
            'accountName' => '',
            'accountNumber' => '',
        ];

        if (is_array($composite) && !empty($composite)) {
            // Normalize keys in composite (support snake_case and camelCase)
            $normalized = [];
            foreach ($composite as $k => $v) {
                $key = $k;
                // convert snake_case keys to camelCase mapping used here
                if (strpos($k, '_') !== false) {
                    $key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $k))));
                }
                $normalized[$key] = $v;
            }

            // Merge normalized composite with defaults
            return array_merge($defaults, array_intersect_key($normalized, $defaults) + $defaults);
        }

        // If composite is a JSON string, try decoding
        if (is_string($composite) && !empty($composite)) {
            try {
                $decoded = Json::decode($composite);
                if (is_array($decoded)) {
                    $normalized = [];
                    foreach ($decoded as $k => $v) {
                        $key = $k;
                        if (strpos($k, '_') !== false) {
                            $key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $k))));
                        }
                        $normalized[$key] = $v;
                    }
                    return array_merge($defaults, array_intersect_key($normalized, $defaults) + $defaults);
                }
            } catch (\Throwable $e) {
                // ignore decode errors and fall back to individual keys
            }
        }

        // Fallback to individual config keys
        return [
            'swiftCode' => self::get('bank_swift_code', ''),
            'bankName' => self::get('bank_name', ''),
            'branchName' => self::get('bank_branch_name', ''),
            'bankCode' => self::get('bank_code', ''),
            'branchCode' => self::get('bank_branch_code', ''),
            'bankAddress' => self::get('bank_address', ''),
            'accountName' => self::get('bank_account_name', ''),
            'accountNumber' => self::get('bank_account_number', ''),
        ];
    }

    /**
     * After save, clear cache
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        self::clearCache();
    }

    /**
     * After delete, clear cache
     */
    public function afterDelete()
    {
        parent::afterDelete();
        self::clearCache();
    }
}
