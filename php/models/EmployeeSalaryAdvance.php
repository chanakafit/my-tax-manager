<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "employee_salary_advance".
 *
 * @property int $id
 * @property int $employee_id
 * @property string $advance_date
 * @property float $amount
 * @property string|null $reason
 * @property string|null $notes
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property Employee $employee
 */
class EmployeeSalaryAdvance extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%employee_salary_advance}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['employee_id', 'advance_date', 'amount'], 'required'],
            [['employee_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['advance_date'], 'date', 'format' => 'php:Y-m-d'],
            [['amount'], 'number', 'min' => 0],
            [['reason'], 'string', 'max' => 500],
            [['notes'], 'string'],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['employee_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'employee_id' => 'Employee',
            'advance_date' => 'Advance Date',
            'amount' => 'Advance Amount',
            'reason' => 'Reason',
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
     * @return ActiveQuery
     */
    public function getEmployee(): ActiveQuery
    {
        return $this->hasOne(Employee::class, ['id' => 'employee_id']);
    }

    /**
     * Get total advance amount for an employee
     *
     * @param int $employeeId
     * @return float
     */
    public static function getTotalAdvanceAmount(int $employeeId): float
    {
        return (float)self::find()
            ->where(['employee_id' => $employeeId])
            ->sum('amount') ?: 0;
    }

    /**
     * Get monthly overview of salary advances for an employee
     *
     * @param int $employeeId
     * @param int|null $year If null, uses current year
     * @return array Array of months with advance totals
     */
    public static function getMonthlyOverview(int $employeeId, ?int $year = null): array
    {
        if ($year === null) {
            $year = date('Y');
        }

        $advances = self::find()
            ->where(['employee_id' => $employeeId])
            ->andWhere(['>=', 'advance_date', "$year-01-01"])
            ->andWhere(['<=', 'advance_date', "$year-12-31"])
            ->orderBy(['advance_date' => SORT_ASC])
            ->all();

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[$month] = [
                'month' => date('F', mktime(0, 0, 0, $month, 1)),
                'month_num' => $month,
                'total' => 0,
                'count' => 0,
                'advances' => [],
            ];
        }

        foreach ($advances as $advance) {
            $month = (int)date('n', strtotime($advance->advance_date));
            $monthlyData[$month]['total'] += $advance->amount;
            $monthlyData[$month]['count']++;
            $monthlyData[$month]['advances'][] = $advance;
        }

        return $monthlyData;
    }

    /**
     * Get year-to-date total for an employee
     *
     * @param int $employeeId
     * @param int|null $year
     * @return float
     */
    public static function getYearToDateTotal(int $employeeId, ?int $year = null): float
    {
        if ($year === null) {
            $year = date('Y');
        }

        return (float)self::find()
            ->where(['employee_id' => $employeeId])
            ->andWhere(['>=', 'advance_date', "$year-01-01"])
            ->andWhere(['<=', 'advance_date', "$year-12-31"])
            ->sum('amount') ?: 0;
    }

    /**
     * Get monthly total for specific month
     *
     * @param int $employeeId
     * @param int $year
     * @param int $month
     * @return float
     */
    public static function getMonthlyTotal(int $employeeId, int $year, int $month): float
    {
        $startDate = sprintf('%d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        return (float)self::find()
            ->where(['employee_id' => $employeeId])
            ->andWhere(['>=', 'advance_date', $startDate])
            ->andWhere(['<=', 'advance_date', $endDate])
            ->sum('amount') ?: 0;
    }

    /**
     * Get available years with advances for an employee
     *
     * @param int $employeeId
     * @return array
     */
    public static function getAvailableYears(int $employeeId): array
    {
        $years = self::find()
            ->select('YEAR(advance_date) as year')
            ->where(['employee_id' => $employeeId])
            ->distinct()
            ->orderBy(['year' => SORT_DESC])
            ->column();

        return array_map('intval', $years);
    }
}

