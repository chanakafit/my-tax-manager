<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "employee_attendance".
 *
 * @property int $id
 * @property int $employee_id
 * @property string $attendance_date
 * @property string $attendance_type full_day, half_day, or day_1_5
 * @property string|null $notes
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property Employee $employee
 */
class EmployeeAttendance extends BaseModel
{
    // Attendance type constants
    const TYPE_FULL_DAY = 'full_day';
    const TYPE_HALF_DAY = 'half_day';
    const TYPE_DAY_1_5 = 'day_1_5';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%employee_attendance}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['employee_id', 'attendance_date'], 'required'],
            [['employee_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['attendance_date'], 'date', 'format' => 'php:Y-m-d'],
            [['attendance_type'], 'string', 'max' => 20],
            [['attendance_type'], 'in', 'range' => [self::TYPE_FULL_DAY, self::TYPE_HALF_DAY, self::TYPE_DAY_1_5]],
            [['attendance_type'], 'default', 'value' => self::TYPE_FULL_DAY],
            [['notes'], 'string'],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['employee_id' => 'id']],
            // Unique validation for employee_id and attendance_date combination
            [['attendance_date'], 'unique', 'targetAttribute' => ['employee_id', 'attendance_date'],
                'message' => 'Attendance for this employee on this date already exists.'],
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
            'attendance_date' => 'Date',
            'attendance_type' => 'Type',
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
     * Get all attendance types
     *
     * @return array
     */
    public static function getAttendanceTypes(): array
    {
        return [
            self::TYPE_FULL_DAY => 'Full Day',
            self::TYPE_HALF_DAY => 'Half Day',
            self::TYPE_DAY_1_5 => '1.5 Days',
        ];
    }

    /**
     * Get attendance type label
     *
     * @return string
     */
    public function getAttendanceTypeLabel(): string
    {
        $types = self::getAttendanceTypes();
        return $types[$this->attendance_type] ?? $this->attendance_type;
    }

    /**
     * Get numeric value for attendance type (for calculations)
     *
     * @return float
     */
    public function getAttendanceValue(): float
    {
        switch ($this->attendance_type) {
            case self::TYPE_HALF_DAY:
                return 0.5;
            case self::TYPE_DAY_1_5:
                return 1.5;
            case self::TYPE_FULL_DAY:
            default:
                return 1.0;
        }
    }

    /**
     * Get monthly summary for an employee
     *
     * @param int $employeeId
     * @param string $year Year in YYYY format
     * @param string $month Month in MM format
     * @return array
     */
    public static function getMonthlySummary(int $employeeId, string $year, string $month): array
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $attendances = self::find()
            ->where(['employee_id' => $employeeId])
            ->andWhere(['between', 'attendance_date', $startDate, $endDate])
            ->all();

        $summary = [
            self::TYPE_FULL_DAY => ['count' => 0, 'days' => 0],
            self::TYPE_HALF_DAY => ['count' => 0, 'days' => 0],
            self::TYPE_DAY_1_5 => ['count' => 0, 'days' => 0],
            'total_days' => 0,
        ];

        foreach ($attendances as $attendance) {
            $summary[$attendance->attendance_type]['count']++;
            $summary[$attendance->attendance_type]['days'] += $attendance->getAttendanceValue();
            $summary['total_days'] += $attendance->getAttendanceValue();
        }

        return $summary;
    }

    /**
     * Get yearly summary for an employee
     *
     * @param int $employeeId
     * @param string $year Year in YYYY format
     * @return array Array of monthly summaries
     */
    public static function getYearlySummary(int $employeeId, string $year): array
    {
        $monthlySummaries = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
            $monthlySummaries[$monthStr] = self::getMonthlySummary($employeeId, $year, $monthStr);
        }

        return $monthlySummaries;
    }

    /**
     * Get date range summary
     *
     * @param int $employeeId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getDateRangeSummary(int $employeeId, string $startDate, string $endDate): array
    {
        $attendances = self::find()
            ->where(['employee_id' => $employeeId])
            ->andWhere(['between', 'attendance_date', $startDate, $endDate])
            ->all();

        $summary = [
            self::TYPE_FULL_DAY => ['count' => 0, 'days' => 0],
            self::TYPE_HALF_DAY => ['count' => 0, 'days' => 0],
            self::TYPE_DAY_1_5 => ['count' => 0, 'days' => 0],
            'total_days' => 0,
            'total_records' => count($attendances),
        ];

        foreach ($attendances as $attendance) {
            $summary[$attendance->attendance_type]['count']++;
            $summary[$attendance->attendance_type]['days'] += $attendance->getAttendanceValue();
            $summary['total_days'] += $attendance->getAttendanceValue();
        }

        return $summary;
    }
}

