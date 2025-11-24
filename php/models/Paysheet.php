<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "paysheet".
 *
 * @property int $id
 * @property int $employee_id
 * @property string $pay_period_start
 * @property string $pay_period_end
 * @property string $payment_date
 * @property float $basic_salary
 * @property float|null $allowances
 * @property float|null $deductions
 * @property float|null $tax_amount
 * @property float $net_salary
 * @property string $payment_method
 * @property string|null $payment_reference
 * @property string $status
 * @property string|null $notes
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Employee $employee
 * @property FinancialTransaction[] $financialTransactions
 */
class Paysheet extends BaseModel
{
    const STATUS_PAID = 'paid';
    const STATUS_PENDING = 'pending';

    /**
     * Store payment date before deletion for tax recalculation
     * @var string
     */
    private $_paymentDateBeforeDelete;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Attach event handlers for tax recalculation
        $this->on(self::EVENT_AFTER_INSERT, function ($event) {
            try {
                \app\models\TaxRecord::recalculateForDate($this->payment_date);
            } catch (\Exception $e) {
                Yii::error("Failed to trigger tax recalculation after paysheet insert: " . $e->getMessage(), __METHOD__);
            }
        });

        $this->on(self::EVENT_AFTER_UPDATE, function ($event) {
            try {
                \app\models\TaxRecord::recalculateForDate($this->payment_date);
            } catch (\Exception $e) {
                Yii::error("Failed to trigger tax recalculation after paysheet update: " . $e->getMessage(), __METHOD__);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%paysheet}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_reference', 'notes'], 'default', 'value' => null],
            [['tax_amount'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 'pending'],
            [['employee_id', 'pay_period_start', 'pay_period_end', 'payment_date', 'basic_salary', 'net_salary', 'payment_method'], 'required'],
            [['employee_id'], 'integer'],
            [['pay_period_start', 'pay_period_end', 'payment_date'], 'safe'],
            [['basic_salary', 'allowances', 'deductions', 'tax_amount', 'net_salary'], 'number'],
            [['notes'], 'string'],
            [['payment_method', 'payment_reference', 'status'], 'string', 'max' => 255],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['employee_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employee_id' => 'Employee',
            'pay_period_start' => 'Pay Period Start',
            'pay_period_end' => 'Pay Period End',
            'payment_date' => 'Payment Date',
            'basic_salary' => 'Basic Salary',
            'allowances' => 'Allowances',
            'deductions' => 'Deductions',
            'tax_amount' => 'Tax Amount',
            'net_salary' => 'Net Salary',
            'payment_method' => 'Payment Method',
            'payment_reference' => 'Payment Reference',
            'status' => 'Status',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Employee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['id' => 'employee_id']);
    }

    /**
     * Gets query for [[FinancialTransactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFinancialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, ['related_paysheet_id' => 'id']);
    }


    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // Store payment date for afterDelete
        $this->_paymentDateBeforeDelete = $this->payment_date;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // Trigger tax recalculation in TaxRecord
        if ($this->_paymentDateBeforeDelete) {
            try {
                \app\models\TaxRecord::recalculateForDate($this->_paymentDateBeforeDelete);
            } catch (\Exception $e) {
                Yii::error("Failed to trigger tax recalculation from Paysheet afterDelete: " . $e->getMessage(), __METHOD__);
            }
        }
    }

}
