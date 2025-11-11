<?php

namespace app\models;

use yii\db\ActiveRecord;

class CustomerEmail extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%customer_email}}';
    }

    public function rules()
    {
        return [
            [['customer_id', 'email'], 'required'],
            ['email', 'email'],
            ['type', 'in', 'range' => ['to', 'cc', 'bcc']],
            ['type', 'default', 'value' => 'to'],
            [['customer_id'], 'integer'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
