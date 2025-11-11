<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_term".
 *
 * @property int $id
 * @property string $name
 * @property int $days
 * @property string|null $description
 * @property int|null $is_default
 * @property int|null $is_active
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Invoice[] $invoices
 */
class PaymentTerm extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_term}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['is_default'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['name', 'days', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'required'],
            [['days', 'is_default', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
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
            'days' => 'Days',
            'description' => 'Description',
            'is_default' => 'Is Default',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Invoices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoice::class, ['payment_term_id' => 'id']);
    }

    public static function getList()
    {
        return yii\helpers\ArrayHelper::map(self::find()->where(['is_active' => 1])->all(), 'id', 'name');
    }

}
