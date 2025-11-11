<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

class InvoiceLink extends BaseModel
{

    public static function tableName()
    {
        return '{{%invoice_link}}';
    }

    public function rules()
    {
        return [
            [['invoice_id', 'token'], 'required'],
            [['invoice_id', 'expires_at'], 'integer'],
            [['token'], 'string', 'max' => 64],
            [['token'], 'unique'],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }

    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at < time();
    }

    public static function createForInvoice($invoiceId, $expiresAt = null)
    {
        $link = new self([
            'invoice_id' => $invoiceId,
            'token' => self::generateToken(),
            'expires_at' => $expiresAt,
        ]);

        return $link->save() ? $link : null;
    }
}
