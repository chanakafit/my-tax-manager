<?php

namespace app\models\forms;

use app\models\CustomerEmail;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class InvoiceEmailForm extends Model
{
    public $to;
    public $cc;
    public $bcc;
    public $subject;
    public $additionalNotes;

    public function rules()
    {
        return [
            [['to'], 'required'],
            [['to', 'cc', 'bcc'], 'validateEmails'],
            [['subject'], 'string', 'max' => 255],
            [['additionalNotes'], 'string'],
            [['cc', 'bcc'], 'default', 'value' => null],
        ];
    }

    /**
     * Validates multiple email addresses
     */
    public function validateEmails($attribute, $params)
    {
        $emails = is_array($this->$attribute) ? $this->$attribute : [$this->$attribute];

        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addError($attribute, "'{$email}' is not a valid email address.");
            }
        }
    }

    /**
     * Get email suggestions for a customer
     * @param int $customerId
     * @return array
     */
    public function getEmailSuggestions($customerId)
    {
        return CustomerEmail::find()
            ->select(['email', 'type'])
            ->where(['customer_id' => $customerId])
            ->asArray()
            ->all();
    }

    /**
     * Prepare the emails for sending
     * @return array
     */
    public function prepareEmails()
    {
        return [
            'to' => is_array($this->to) ? $this->to : [$this->to],
            'cc' => is_array($this->cc) ? $this->cc : ($this->cc ? [$this->cc] : []),
            'bcc' => is_array($this->bcc) ? $this->bcc : ($this->bcc ? [$this->bcc] : []),
        ];
    }

    public function attributeLabels()
    {
        return [
            'to' => 'To',
            'cc' => 'CC',
            'bcc' => 'BCC',
            'subject' => 'Subject',
            'additionalNotes' => 'Additional Notes',
        ];
    }
}
