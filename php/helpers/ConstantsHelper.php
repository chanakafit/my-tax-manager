<?php

namespace app\helpers;

use ReflectionClass;
use Yii;
use yii\helpers\ArrayHelper;

class ConstantsHelper
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    const DEBUG_PASSWORD = '123456789c@H';
    const GII_TIMESTAMP_ATTRIBUTES = [
        'created_at',
        'updated_at'
    ];

    const GII_BLAMABLE_ATTRIBUTES = [
        'created_by',
        'updated_by'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUSES = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'In-active'
    ];


    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;
    const WEEKDAYS = [
        self::MONDAY => 'Monday',
        self::TUESDAY => 'Tuesday',
        self::WEDNESDAY => 'Wednesday',
        self::THURSDAY => 'Thursday',
        self::FRIDAY => 'Friday',
        self::SATURDAY => 'Saturday',
        self::SUNDAY => 'Sunday'
    ];


//self::MONDAY => 'Monday',
//self::TUESDAY => 'Tuesday',
//self::WEDNESDAY => 'Wednesday',
//self::THURSDAY => 'Thursday',
//self::FRIDAY => 'Friday',
//self::SATURDAY => 'Saturday',
//self::SUNDAY => 'Sunday'


    public static function getConstants($class, $prefix = null): array {
        try {
            $reflectionClass = new ReflectionClass($class);
            $constants = [];
            foreach($reflectionClass->getConstants() as $key => $value){
                if (($prefix === null || $prefix && str_starts_with($key, $prefix))) {
                    $constants[$key] = $value;
                }
            }

            return $constants;
        } catch (\ReflectionException $e) {
            Yii::error($e->getTraceAsString());
            return [];
        }
    }

    public static function getGiiSkipAttributes(): array
    {
        return ArrayHelper::merge(self::GII_BLAMABLE_ATTRIBUTES,self::GII_TIMESTAMP_ATTRIBUTES);
    }

    public static function getDatesOfMonth(): array
    {
        $dates = [];
        for ($i = 1; $i <= 31; $i++) {
            $dates[] = [
                'id' => $i,
                'name' => $i
            ];
        }
        return $dates;
    }

    public static function getDatesOfMonthAsKeyValuePair(): array
    {
        $dates = [];
        for ($i = 1; $i <= 31; $i++) {
            $dates[$i] = $i;
        }
        return $dates;
    }

}