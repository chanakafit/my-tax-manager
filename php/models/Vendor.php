<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vendor".
 *
 * @property int $id
 * @property string $name
 * @property string|null $contact
 * @property string|null $email
 * @property string|null $address
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Expense[] $expenses
 */
class Vendor extends BaseModel
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vendor}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contact', 'email', 'address'], 'default', 'value' => null],
            [['name'], 'required'],
            [['address'], 'string'],
            [['name', 'contact', 'email'], 'string', 'max' => 255],
            [['currency_code'], 'string', 'max' => 3],
            [['currency_code'], 'default', 'value' => 'LKR'],
            [['currency_code'], 'in', 'range' => array_keys(\app\helpers\Params::get('currencies'))],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'contact' => 'Contact',
            'email' => 'Email',
            'address' => 'Address',
            'currency_code' => 'Default Currency',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Expenses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpenses()
    {
        return $this->hasMany(Expense::class, ['vendor_id' => 'id']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

}
