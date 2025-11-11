<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "owner_bank_account".
 *
 * @property int $id
 * @property string $account_name
 * @property string $account_number
 * @property string $bank_name
 * @property string|null $branch_name
 * @property string|null $swift_code
 * @property string $account_type
 * @property string $account_holder_type (business or personal)
 * @property string $currency
 * @property int|null $is_active
 * @property string|null $notes
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property FinancialTransaction[] $financialTransactions
 * @property TaxYearBankBalance[] $taxYearBankBalances
 */
class OwnerBankAccount extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%owner_bank_account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['branch_name', 'swift_code', 'notes'], 'default', 'value' => null],
            [['currency'], 'default', 'value' => 'LKR'],
            [['is_active'], 'default', 'value' => 1],
            [['account_holder_type'], 'default', 'value' => 'business'],
            [['account_name', 'account_number', 'bank_name', 'account_type'], 'required'],
            [['is_active'], 'integer'],
            [['notes'], 'string'],
            [['account_name', 'account_number', 'bank_name', 'branch_name', 'swift_code', 'account_type', 'account_holder_type'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 3],
            [['account_number'], 'unique'],
            [['account_holder_type'], 'in', 'range' => ['business', 'personal']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'account_name' => 'Account Name',
            'account_number' => 'Account Number',
            'bank_name' => 'Bank Name',
            'branch_name' => 'Branch Name',
            'swift_code' => 'Swift Code',
            'account_type' => 'Account Type',
            'account_holder_type' => 'Account Holder Type',
            'currency' => 'Currency',
            'is_active' => 'Is Active',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[FinancialTransactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFinancialTransactions(): \yii\db\ActiveQuery
    {
        return $this->hasMany(FinancialTransaction::class, ['bank_account_id' => 'id']);
    }

    /**
     * Gets query for [[TaxYearBankBalances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaxYearBankBalances(): \yii\db\ActiveQuery
    {
        return $this->hasMany(TaxYearBankBalance::class, ['bank_account_id' => 'id']);
    }

    public function delete()
    {
        $this->is_active = 0;
        $this->save();
    }

    public function getAccountTitle(): string
    {
        return $this->account_name . ' - ' . $this->account_number . ' (' . $this->bank_name . ')';
    }

    /**
     * Get account type options
     * @return array
     */
    public static function getAccountTypeOptions(): array
    {
        return [
            'savings' => 'Savings Account',
            'current' => 'Current Account',
            'fixed_deposit' => 'Fixed Deposit',
            'other' => 'Other',
        ];
    }

    /**
     * Get account holder type options
     * @return array
     */
    public static function getAccountHolderTypeOptions(): array
    {
        return [
            'business' => 'Business Account',
            'personal' => 'Personal Account',
        ];
    }

    /**
     * Get bank name options
     * @return array
     */
    public static function getBankNameOptions(): array
    {
        $bankNames = Yii::$app->params['bankNames'] ?? [];
        return array_combine($bankNames, $bankNames);
    }
}

