<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "paysheet_suggestion".
 *
 * @property int $id
 * @property int $employee_id
 * @property string $suggested_month First day of the month for which paysheet is suggested
 * @property float $basic_salary Suggested basic salary amount
 * @property float|null $allowances
 * @property float|null $deductions
 * @property float|null $tax_amount
 * @property float $net_salary
 * @property string $status pending, approved, rejected
 * @property string|null $notes
 * @property int $generated_at When this suggestion was generated
 * @property int|null $actioned_at
 * @property int|null $actioned_by
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Employee $employee
 * @property User $actionedBy
 */
class PaysheetSuggestion extends BaseModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = [
            \yii\behaviors\TimestampBehavior::class,
        ];

        // In console mode, set default values for created_by and updated_by
        if (Yii::$app instanceof \yii\console\Application) {
            $behaviors['blameable'] = [
                'class' => \yii\behaviors\BlameableBehavior::class,
                'defaultValue' => 1, // System user ID for console operations
            ];
        } else {
            $behaviors[] = \yii\behaviors\BlameableBehavior::class;
        }

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%paysheet_suggestion}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['employee_id', 'suggested_month', 'basic_salary', 'net_salary', 'generated_at'], 'required'],
            [['employee_id', 'generated_at', 'actioned_at', 'actioned_by'], 'integer'],
            [['suggested_month'], 'date', 'format' => 'php:Y-m-d'],
            [['basic_salary', 'allowances', 'deductions', 'tax_amount', 'net_salary'], 'number'],
            [['notes'], 'string'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_APPROVED,
                self::STATUS_REJECTED
            ]],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['employee_id' => 'id']],
            [['actioned_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['actioned_by' => 'id']],
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
            'suggested_month' => 'Pay Period',
            'basic_salary' => 'Basic Salary',
            'allowances' => 'Allowances',
            'deductions' => 'Deductions',
            'tax_amount' => 'Tax Amount',
            'net_salary' => 'Net Salary',
            'status' => 'Status',
            'notes' => 'Notes',
            'generated_at' => 'Generated At',
            'actioned_at' => 'Actioned At',
            'actioned_by' => 'Actioned By',
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
     * Gets query for [[ActionedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActionedBy()
    {
        return $this->hasOne(User::class, ['id' => 'actioned_by']);
    }

    /**
     * Get status label with color
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_PENDING => '<span class="label label-warning" style="background-color: #f0ad4e; color: #000; padding: 6px 12px; border-radius: 3px; font-weight: bold; display: inline-block;">Pending Review</span>',
            self::STATUS_APPROVED => '<span class="label label-success" style="background-color: #5cb85c; color: #fff; padding: 6px 12px; border-radius: 3px; font-weight: bold; display: inline-block;">Approved</span>',
            self::STATUS_REJECTED => '<span class="label label-danger" style="background-color: #d9534f; color: #fff; padding: 6px 12px; border-radius: 3px; font-weight: bold; display: inline-block;">Rejected</span>',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Mark suggestion as approved and create paysheet
     *
     * @param int $userId
     * @return Paysheet|null
     * @deprecated Use approveWithDetails() instead
     */
    public function approve($userId)
    {
        return $this->approveWithDetails($userId, date('Y-m-d'), 'Bank Transfer', null, 'Generated from paysheet suggestion');
    }

    /**
     * Mark suggestion as approved and create paysheet with specified details
     *
     * @param int $userId
     * @param string $paymentDate
     * @param string $paymentMethod
     * @param string|null $paymentReference
     * @param string|null $notes
     * @return Paysheet|null
     */
    public function approveWithDetails($userId, $paymentDate, $paymentMethod, $paymentReference = null, $notes = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Create paysheet from suggestion
            $paysheet = new Paysheet();
            $paysheet->employee_id = $this->employee_id;
            $paysheet->pay_period_start = date('Y-m-01', strtotime($this->suggested_month));
            $paysheet->pay_period_end = date('Y-m-t', strtotime($this->suggested_month));
            $paysheet->payment_date = $paymentDate;
            $paysheet->basic_salary = $this->basic_salary;
            $paysheet->allowances = $this->allowances ?? 0;
            $paysheet->deductions = $this->deductions ?? 0;
            $paysheet->tax_amount = $this->tax_amount ?? 0;
            $paysheet->net_salary = $this->net_salary;
            $paysheet->payment_method = $paymentMethod;
            $paysheet->payment_reference = $paymentReference;
            $paysheet->status = Paysheet::STATUS_PENDING;
            $paysheet->notes = $notes ?? 'Generated from paysheet suggestion';

            if (!$paysheet->save()) {
                throw new \Exception('Failed to create paysheet: ' . json_encode($paysheet->errors));
            }

            // Update suggestion status
            $this->status = self::STATUS_APPROVED;
            $this->actioned_at = time();
            $this->actioned_by = $userId;

            if (!$this->save(false)) {
                throw new \Exception('Failed to update suggestion status');
            }

            $transaction->commit();
            return $paysheet;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Failed to approve paysheet suggestion: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Mark suggestion as rejected
     *
     * @param int $userId
     * @param string|null $reason
     * @return bool
     */
    public function reject($userId, $reason = null)
    {
        $this->status = self::STATUS_REJECTED;
        $this->actioned_at = time();
        $this->actioned_by = $userId;
        if ($reason) {
            $this->notes = $reason;
        }
        return $this->save(false);
    }

    /**
     * Get formatted month name
     *
     * @return string
     */
    public function getFormattedMonth()
    {
        return date('F Y', strtotime($this->suggested_month));
    }

    /**
     * Check if suggestion can be edited
     *
     * @return bool
     */
    public function canEdit()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if suggestion can be deleted
     *
     * @return bool
     */
    public function canDelete()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_REJECTED]);
    }
}

