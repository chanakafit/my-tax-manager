<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "expense_suggestion".
 *
 * @property int $id
 * @property int $expense_category_id
 * @property int $vendor_id
 * @property string $suggested_month First day of the month for which expense is suggested
 * @property string|null $pattern_months JSON array of months where this pattern was detected
 * @property float|null $avg_amount_lkr Average amount from pattern
 * @property int|null $last_expense_id Reference to the most recent expense in the pattern
 * @property string $status pending, added, ignored_temporary, ignored_permanent
 * @property string|null $ignored_reason User provided reason for ignoring
 * @property int $generated_at When this suggestion was generated
 * @property int|null $actioned_at When user took action (add/ignore)
 * @property int|null $actioned_by User who took action
 * @property int $created_at
 * @property int $updated_at
 *
 * @property ExpenseCategory $expenseCategory
 * @property Vendor $vendor
 * @property Expense $lastExpense
 */
class ExpenseSuggestion extends BaseModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_ADDED = 'added';
    const STATUS_IGNORED_TEMPORARY = 'ignored_temporary';
    const STATUS_IGNORED_PERMANENT = 'ignored_permanent';

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
        return '{{%expense_suggestion}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['expense_category_id', 'vendor_id', 'suggested_month', 'pattern_months', 'generated_at'], 'required'],
            [['expense_category_id', 'vendor_id', 'last_expense_id', 'generated_at', 'actioned_at', 'actioned_by'], 'integer'],
            [['suggested_month'], 'date', 'format' => 'php:Y-m-d'],
            [['pattern_months', 'ignored_reason'], 'string'],
            [['avg_amount_lkr'], 'number'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_ADDED,
                self::STATUS_IGNORED_TEMPORARY,
                self::STATUS_IGNORED_PERMANENT
            ]],
            [['expense_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExpenseCategory::class, 'targetAttribute' => ['expense_category_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vendor::class, 'targetAttribute' => ['vendor_id' => 'id']],
            [['last_expense_id'], 'exist', 'skipOnError' => true, 'targetClass' => Expense::class, 'targetAttribute' => ['last_expense_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'expense_category_id' => 'Expense Category',
            'vendor_id' => 'Vendor',
            'suggested_month' => 'Suggested Month',
            'pattern_months' => 'Pattern Months',
            'avg_amount_lkr' => 'Average Amount (LKR)',
            'last_expense_id' => 'Last Expense',
            'status' => 'Status',
            'ignored_reason' => 'Reason for Ignoring',
            'generated_at' => 'Generated At',
            'actioned_at' => 'Actioned At',
            'actioned_by' => 'Actioned By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[ExpenseCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpenseCategory()
    {
        return $this->hasOne(ExpenseCategory::class, ['id' => 'expense_category_id']);
    }

    /**
     * Gets query for [[Vendor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    /**
     * Gets query for [[LastExpense]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLastExpense()
    {
        return $this->hasOne(Expense::class, ['id' => 'last_expense_id']);
    }

    /**
     * Get pattern months as array
     *
     * @return array
     */
    public function getPatternMonthsArray()
    {
        if (empty($this->pattern_months)) {
            return [];
        }
        return json_decode($this->pattern_months, true) ?: [];
    }

    /**
     * Set pattern months from array
     *
     * @param array $months
     */
    public function setPatternMonthsArray($months)
    {
        $this->pattern_months = json_encode($months);
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
            self::STATUS_ADDED => '<span class="label label-success" style="background-color: #5cb85c; color: #fff; padding: 6px 12px; border-radius: 3px; font-weight: bold; display: inline-block;">Added</span>',
            self::STATUS_IGNORED_TEMPORARY => '<span class="label label-default" style="background-color: #999; color: #fff; padding: 6px 12px; border-radius: 3px; font-weight: bold; display: inline-block;">Ignored (Temp)</span>',
            self::STATUS_IGNORED_PERMANENT => '<span class="label label-default" style="background-color: #333; color: #fff; padding: 6px 12px; border-radius: 3px; font-weight: bold; display: inline-block;">Ignored (Permanent)</span>',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Mark suggestion as added
     *
     * @param int $userId
     * @return bool
     */
    public function markAsAdded($userId)
    {
        $this->status = self::STATUS_ADDED;
        $this->actioned_at = time();
        $this->actioned_by = $userId;
        return $this->save(false);
    }

    /**
     * Mark suggestion as ignored
     *
     * @param string $ignoreType 'temporary' or 'permanent'
     * @param string|null $reason
     * @param int $userId
     * @return bool
     */
    public function markAsIgnored($ignoreType, $reason, $userId)
    {
        $this->status = $ignoreType === 'permanent'
            ? self::STATUS_IGNORED_PERMANENT
            : self::STATUS_IGNORED_TEMPORARY;
        $this->ignored_reason = $reason;
        $this->actioned_at = time();
        $this->actioned_by = $userId;
        return $this->save(false);
    }
}

