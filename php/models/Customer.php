<?php

namespace app\models;

use app\helpers\Params;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string $company_name
 * @property string|null $contact_person
 * @property string $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property string|null $tax_number VAT/Tax registration number
 * @property string|null $website
 * @property string|null $notes
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $default_currency
 *
 * @property CustomerEmail[] $customerEmails
 * @property Invoice[] $invoices
 */
class Customer extends \app\models\BaseModel
{
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_name', 'email', 'default_currency'], 'required'],
            [['address', 'notes'], 'string'],
            [['status'], 'integer'],
            [['company_name', 'contact_person', 'email', 'phone', 'city', 'state', 'postal_code', 'country', 'tax_number', 'website'], 'string', 'max' => 255],
            [['default_currency'], 'string', 'max' => 3],
            [['email'], 'email'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['default_currency'], 'in', 'range' => array_keys(Params::get('currencies'))],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => 'Company Name',
            'contact_person' => 'Contact Person',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'postal_code' => 'Postal Code',
            'country' => 'Country',
            'tax_number' => 'Tax Number',
            'website' => 'Website',
            'notes' => 'Notes',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'default_currency' => 'Default Currency',
        ];
    }

    /**
     * Gets query for [[Invoices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoice::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return array status list
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * @return string the status text
     */
    public function getStatusText()
    {
        return static::getStatusList()[$this->status] ?? 'Unknown';
    }

    /**
     * @return string customer full representation
     */
    public function getFullName()
    {
        return $this->company_name . ($this->contact_person ? ' (' . $this->contact_person . ')' : '');
    }

    /**
     * Gets available currencies list
     * @return array
     */
    public static function getCurrencyList()
    {
        return Params::get('currencies');
    }

    /**
     * Get currency name
     * @return string
     */
    public function getCurrencyName()
    {
        return self::getCurrencyList()[$this->default_currency] ?? $this->default_currency;
    }
}
