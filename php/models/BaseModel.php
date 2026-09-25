<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Base model class with common behaviors and methods
 */
class BaseModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
            [
                'class' => BlameableBehavior::class,
                // Console commands (cron, recalculations, seeding) have no identity,
                // so fall back to the admin user instead of failing on a NOT NULL column
                'defaultValue' => 1,
            ],
        ];
    }
}
