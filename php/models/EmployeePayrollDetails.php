<?php

namespace app\models;

use app\helpers\Params;
use yii\db\ActiveRecord;

class EmployeePayrollDetails extends BaseModel
{
    public static function tableName()
    {
        return '{{%employee_payroll_details}}';
    }

    public function rules()
    {
        return [
            [['employee_id', 'bank_account_id', 'basic_salary', 'tax_category', 'payment_frequency'], 'required'],
            [['employee_id', 'bank_account_id'], 'integer'],
            [['basic_salary', 'allowances', 'deductions'], 'number'],
            [['tax_category', 'payment_frequency'], 'string', 'max' => 255],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['employee_id' => 'id']],
            [['bank_account_id'], 'exist', 'skipOnError' => true, 'targetClass' => BankAccount::class, 'targetAttribute' => ['bank_account_id' => 'id']],
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['id' => 'employee_id']);
    }

    public function getBankAccount()
    {
        return $this->hasOne(BankAccount::class, ['id' => 'bank_account_id']);
    }

    /**
     * Calculate tax based on salary and tax category
     * @param float $grossSalary The gross salary amount
     * @param string|null $paymentDate Date of payment (defaults to current date)
     * @return float Monthly tax amount
     */
    public function calculateTax($grossSalary, $paymentDate = null)
    {
        if ($paymentDate === null) {
            $paymentDate = date('Y-m-d');
        }

        $year = date('Y', strtotime($paymentDate));
        $annualSalary = $grossSalary * 12;

        // Get tax configurations from params (fallback)
        $yearlyTaxRelief = Params::get("taxConfigs.$year.yearlyTaxRelief", 1200000); // Default 1.2M relief

        // Get tax rate based on payment date - will be 0% before April 1, 2025
        $taxRate = TaxConfig::getConfig('profit_tax_rate', $paymentDate);
        if ($taxRate === null) {
            $taxRate = Params::get("taxConfigs.$year.taxRate", 0); // Default to 0 if not found
        }

        // Apply tax category specific adjustments if needed
        switch ($this->tax_category) {
            case 'exempt':
                return 0;
            case 'special':
                $taxRate = $taxRate * 0.5; // 50% of normal rate for special category
                break;
            case 'standard':
            default:
                // Use standard rate
                break;
        }

        // Calculate taxable amount after deducting yearly relief
        $taxableAmount = max(0, $annualSalary - $yearlyTaxRelief);

        // Calculate annual tax
        $annualTax = ($taxableAmount * $taxRate) / 100;

        // Return monthly tax amount
        return $annualTax / 12;
    }

    public function calculateNetSalary()
    {
        $grossSalary = $this->basic_salary + ($this->allowances ?? 0);
        $deductions = $this->deductions ?? 0;
        $taxAmount = $this->calculateTax($grossSalary);
        return $grossSalary - $deductions - $taxAmount;
    }

    public function attributeLabels(){
        return [
            'id' => 'ID',
            'employee_id' => 'Employee',
            'bank_account_id' => 'Bank Account',
            'basic_salary' => 'Basic Salary',
            'allowances' => 'Allowances',
            'deductions' => 'Deductions',
            'tax_category' => 'Tax Category',
            'payment_frequency' => 'Payment Frequency',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
