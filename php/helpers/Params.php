<?php

namespace app\helpers;

use Yii;

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
               return $default;
           }
       }

       return $params;
   }

}