<?php

namespace app\helpers;

use Yii;
use app\models\SystemConfig;

class Params
{
   public static function get($key, $default = null)
   {
       $params = Yii::$app->params;
       $keys = explode('.', $key);

       foreach ($keys as $k) {
           if (isset($params[$k])) {
               $params = $params[$k];
           } else {
               // Not found in params.php, try SystemConfig
               return self::getFromSystemConfig($key, $default);
           }
       }

       // Found in params.php but value is null, try SystemConfig as fallback
       if ($params === null) {
           return self::getFromSystemConfig($key, $default);
       }

       return $params;
   }

   /**
    * Get parameter from SystemConfig database table
    *
    * @param string $key The config key (supports dot notation like 'business.name')
    * @param mixed $default Default value if not found
    * @return mixed
    */
   private static function getFromSystemConfig($key, $default = null)
   {
       // Convert dot notation to underscore for database keys
       // e.g., 'business.name' -> 'business_name'
       $configKey = str_replace('.', '_', $key);

       // Try to get from SystemConfig
       $value = SystemConfig::get($configKey, null);

       // If still not found, return default
       return $value !== null ? $value : $default;
   }

}