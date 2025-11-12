<?php

namespace app\components;

use app\models\Employee;
use app\models\EmployeePayrollDetails;
use app\models\Paysheet;
use app\models\PaysheetSuggestion;
use Yii;

/**
 * Service to analyze employee paysheets and generate suggestions for missing monthly salaries
 */
class PaysheetHealthCheckService
{
    /**
     * Generate paysheet suggestions for the current month
     * Creates suggestions for all active employees who don't have a paysheet for the month
     *
     * @param string|null $targetMonth YYYY-MM-DD format (first day of month). If null, uses current month.
     * @return array ['created' => count, 'skipped' => count, 'errors' => array]
     */
    public function generateSuggestionsForMonth($targetMonth = null)
    {
        $result = [
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            // Determine target month (first day of the month)
            if ($targetMonth === null) {
                $targetDate = new \DateTime('first day of this month');
            } else {
                $targetDate = new \DateTime($targetMonth);
                $targetDate->modify('first day of this month');
            }
            $targetMonthStr = $targetDate->format('Y-m-d');

            // Don't generate suggestions for future months
            $currentMonth = new \DateTime('first day of this month');
            if ($targetDate > $currentMonth) {
                Yii::info("Skipping suggestions for future month: {$targetMonthStr}", __METHOD__);
                return $result;
            }

            // Get all active employees (those without left_date or left_date is after target month)
            $employees = Employee::find()
                ->andWhere([
                    'or',
                    ['left_date' => null],
                    ['>', 'left_date', $targetDate->format('Y-m-t')]
                ])
                ->andWhere(['<=', 'hire_date', $targetDate->format('Y-m-t')])
                ->all();

            Yii::info("Found " . count($employees) . " active employees for month {$targetMonthStr}", __METHOD__);

            foreach ($employees as $employee) {
                try {
                    // Check if paysheet already exists for this month
                    $paysheetExists = Paysheet::find()
                        ->where(['employee_id' => $employee->id])
                        ->andWhere(['>=', 'pay_period_start', $targetMonthStr])
                        ->andWhere(['<=', 'pay_period_start', $targetDate->format('Y-m-t')])
                        ->exists();

                    if ($paysheetExists) {
                        $result['skipped']++;
                        Yii::info("Paysheet already exists for employee {$employee->id}", __METHOD__);
                        continue;
                    }

                    // Check if suggestion already exists
                    $existingSuggestion = PaysheetSuggestion::find()
                        ->where([
                            'employee_id' => $employee->id,
                            'suggested_month' => $targetMonthStr,
                        ])
                        ->one();

                    if ($existingSuggestion) {
                        // Update existing suggestion if it's still pending
                        if ($existingSuggestion->status === PaysheetSuggestion::STATUS_PENDING) {
                            $this->updateSuggestionData($existingSuggestion, $employee, $targetMonthStr);
                            $existingSuggestion->generated_at = time();
                            $existingSuggestion->save(false);
                        }
                        $result['skipped']++;
                        continue;
                    }

                    // Create new suggestion
                    $suggestion = $this->createSuggestion($employee, $targetMonthStr);

                    if ($suggestion->save()) {
                        $result['created']++;
                        Yii::info("Created paysheet suggestion for employee {$employee->id}", __METHOD__);
                    } else {
                        $result['errors'][] = "Failed to save suggestion for employee {$employee->fullName}: " . json_encode($suggestion->errors);
                    }
                } catch (\Exception $e) {
                    $result['errors'][] = "Employee {$employee->fullName}: " . $e->getMessage();
                    Yii::error("Error processing employee {$employee->id}: " . $e->getMessage(), __METHOD__);
                }
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Yii::error("Error generating paysheet suggestions: " . $e->getMessage(), __METHOD__);
        }

        return $result;
    }

    /**
     * Generate paysheet suggestions for all past months (including current)
     *
     * @param int $months Number of months back to scan
     * @return array
     */
    public function generateSuggestionsForAllPastMonths($months = 6)
    {
        $result = [
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
            'months_scanned' => [],
        ];

        try {
            $currentDate = new \DateTime('first day of this month');

            for ($i = 0; $i < $months; $i++) {
                $targetDate = clone $currentDate;
                $targetDate->modify("-{$i} months");
                $targetMonthStr = $targetDate->format('Y-m-d');

                $result['months_scanned'][] = $targetDate->format('F Y');

                $monthResult = $this->generateSuggestionsForMonth($targetMonthStr);

                $result['created'] += $monthResult['created'];
                $result['skipped'] += $monthResult['skipped'];
                $result['errors'] = array_merge($result['errors'], $monthResult['errors']);
            }
        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();
            Yii::error("Error generating suggestions for all past months: " . $e->getMessage(), __METHOD__);
        }

        return $result;
    }

    /**
     * Create a new paysheet suggestion for an employee
     *
     * @param Employee $employee
     * @param string $targetMonth YYYY-MM-DD format
     * @return PaysheetSuggestion
     */
    protected function createSuggestion($employee, $targetMonth)
    {
        // Get employee payroll details if available
        $payrollDetails = $employee->payrollDetails;

        if ($payrollDetails) {
            $basicSalary = $payrollDetails->basic_salary;
            $allowances = $payrollDetails->allowances ?? 0;
            $deductions = $payrollDetails->deductions ?? 0;
            $taxAmount = $payrollDetails->calculateTax($basicSalary, $targetMonth);
        } else {
            // Fallback: get last paysheet or use default values
            $lastPaysheet = Paysheet::find()
                ->where(['employee_id' => $employee->id])
                ->orderBy(['pay_period_start' => SORT_DESC])
                ->one();

            if ($lastPaysheet) {
                $basicSalary = $lastPaysheet->basic_salary;
                $allowances = $lastPaysheet->allowances ?? 0;
                $deductions = $lastPaysheet->deductions ?? 0;
                $taxAmount = $lastPaysheet->tax_amount ?? 0;
            } else {
                // No payroll details and no previous paysheet - use basic defaults
                $basicSalary = 50000; // Default basic salary
                $allowances = 0;
                $deductions = 0;
                $taxAmount = 0;
            }
        }

        $netSalary = $basicSalary + $allowances - $deductions - $taxAmount;

        $suggestion = new PaysheetSuggestion([
            'employee_id' => $employee->id,
            'suggested_month' => $targetMonth,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'tax_amount' => $taxAmount,
            'net_salary' => $netSalary,
            'status' => PaysheetSuggestion::STATUS_PENDING,
            'generated_at' => time(),
        ]);

        return $suggestion;
    }

    /**
     * Update suggestion data with latest employee information
     *
     * @param PaysheetSuggestion $suggestion
     * @param Employee $employee
     * @param string $targetMonth
     */
    protected function updateSuggestionData($suggestion, $employee, $targetMonth)
    {
        $payrollDetails = $employee->payrollDetails;

        if ($payrollDetails) {
            $suggestion->basic_salary = $payrollDetails->basic_salary;
            $suggestion->allowances = $payrollDetails->allowances ?? 0;
            $suggestion->deductions = $payrollDetails->deductions ?? 0;
            $suggestion->tax_amount = $payrollDetails->calculateTax($payrollDetails->basic_salary, $targetMonth);
        }

        $suggestion->net_salary = $suggestion->basic_salary + $suggestion->allowances - $suggestion->deductions - $suggestion->tax_amount;
    }

    /**
     * Get count of pending paysheet suggestions
     *
     * @return int
     */
    public function getPendingSuggestionsCount()
    {
        return PaysheetSuggestion::find()
            ->where(['status' => PaysheetSuggestion::STATUS_PENDING])
            ->count();
    }

    /**
     * Delete rejected suggestions older than specified days
     *
     * @param int $days
     * @return int Number of deleted suggestions
     */
    public function cleanupRejectedSuggestions($days = 90)
    {
        try {
            $threshold = time() - ($days * 24 * 60 * 60);

            $deleted = PaysheetSuggestion::deleteAll([
                'and',
                ['status' => PaysheetSuggestion::STATUS_REJECTED],
                ['<', 'actioned_at', $threshold]
            ]);

            Yii::info("Cleaned up {$deleted} old rejected paysheet suggestions", __METHOD__);
            return $deleted;
        } catch (\Exception $e) {
            Yii::error("Error cleaning up rejected suggestions: " . $e->getMessage(), __METHOD__);
            return 0;
        }
    }
}

