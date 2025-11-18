<?php

namespace app\models;

use app\helpers\Params;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "employee".
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $nic
 * @property string $phone
 * @property string $position
 * @property string $department
 * @property string $hire_date
 * @property float $salary
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property EmployeePayrollDetails[] $employeePayrollDetails
 * @property Paysheet[] $paysheets
 */
class Employee extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%employee}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['first_name', 'last_name', 'nic', 'phone', 'position', 'department', 'hire_date'], 'required'],
            [['hire_date','left_date'], 'safe'],
            [['first_name', 'last_name', 'nic', 'phone', 'position', 'department'], 'string', 'max' => 255],
            [['nic'], 'unique'],
            // other rules...
            ['nic', 'match', 'pattern' => '/^([12][0-9]{11}|[0-9]{9}[vVxX])$/',
                'message' => 'Please enter a valid NIC number'],
            ['phone', 'match', 'pattern' => '/^0[0-9]{9}$/',
                'message' => 'Please enter a valid phone number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'nic' => 'NIC',
            'phone' => 'Phone',
            'position' => 'Position',
            'department' => 'Department',
            'hire_date' => 'Hire Date',
            'left_date' => 'Left Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[EmployeePayrollDetails]].
     *
     * @return ActiveQuery
     */
    public function getEmployeePayrollDetails(): ActiveQuery
    {
        return $this->hasMany(EmployeePayrollDetails::class, ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[Paysheets]].
     *
     * @return ActiveQuery
     */
    public function getPaysheets(): ActiveQuery
    {
        return $this->hasMany(Paysheet::class, ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[EmployeeAttendance]].
     *
     * @return ActiveQuery
     */
    public function getAttendances(): ActiveQuery
    {
        return $this->hasMany(EmployeeAttendance::class, ['employee_id' => 'id']);
    }

    /**
     * Gets query for [[EmployeeSalaryAdvance]].
     *
     * @return ActiveQuery
     */
    public function getSalaryAdvances(): ActiveQuery
    {
        return $this->hasMany(EmployeeSalaryAdvance::class, ['employee_id' => 'id']);
    }

    /**
     * Get full name of employee
     */
    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get list of employees for dropdown
     */
    public static function getList(): array
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'fullName');
    }

    /**
     * Calculate monthly paysheet including tax and deductions
     */
    public function calculateMonthlyPaysheet($month, $year): Paysheet
    {
        $paysheet = new Paysheet();
        $paysheet->employee_id = $this->id;
        $paysheet->basic_salary = $this->salary;

        // Set pay period
        $paysheet->pay_period_start = date('Y-m-01', strtotime("$year-$month-01"));
        $paysheet->pay_period_end = date('Y-m-t', strtotime("$year-$month-01"));
        $paysheet->payment_date = date('Y-m-d');

        // Calculate tax (15% of basic salary)
        $annualSalary = $this->salary * 12;
        $taxableAmount = max(0, $annualSalary - Params::get('taxConfigs.'.$year.'.yearlyTaxRelief'));
        $monthlyTax = ($taxableAmount > 0) ? ($taxableAmount * Params::get('taxConfigs.'.$year.'.taxRate')/100) / 12 : 0;

        $paysheet->tax_amount = $monthlyTax;
        $paysheet->net_salary = $this->salary - $monthlyTax;

        return $paysheet;
    }

    public function getPayrollDetails(): ActiveQuery
    {
        return $this->hasOne(EmployeePayrollDetails::class, ['employee_id' => 'id']);
    }
}
