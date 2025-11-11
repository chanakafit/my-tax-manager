<?php

namespace app\controllers;

use app\helpers\Params;
use app\models\Expense;
use app\models\FinancialTransaction;
use app\models\forms\PaysheetGenerateForm;
use app\models\PaysheetSearch;
use Yii;
use app\models\Paysheet;
use app\models\Employee;
use app\models\EmployeePayrollDetails;
use yii\web\NotFoundHttpException;

class PaysheetController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new PaysheetSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCalculate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $employeeId = Yii::$app->request->post('employee_id');
        $employee = Employee::findOne($employeeId);

        if (!$employee) {
            return ['error' => 'Employee not found'];
        }

        $payrollDetails = $employee->payrollDetails;
        if (!$payrollDetails) {
            return ['error' => 'Payroll details not found'];
        }

        $grossSalary = $payrollDetails->basic_salary + $payrollDetails->allowances;
        $deductions = $payrollDetails->deductions;
        $tax = $payrollDetails->calculateTax($grossSalary, date('Y-m-d'));
        $netSalary = $grossSalary - $deductions - $tax;

        return [
            'employee' => $employee->attributes,
            'calculations' => [
                'grossSalary' => $grossSalary,
                'deductions' => $deductions,
                'tax' => $tax,
                'netSalary' => $netSalary,
            ]
        ];
    }

    public function actionProcess()
    {
        if (Yii::$app->request->isPost) {
            $employeeIds = Yii::$app->request->post('employee_ids', []);
            $month = Yii::$app->request->post('month');
            $year = Yii::$app->request->post('year');

            foreach ($employeeIds as $employeeId) {
                $employee = Employee::findOne($employeeId);
                if ($employee && $employee->payrollDetails) {
                    $paymentDate = "$year-$month-01"; // Use first day of payment month
                    $paysheet = new Paysheet([
                        'employee_id' => $employeeId,
                        'month' => $month,
                        'year' => $year,
                        'gross_salary' => $employee->payrollDetails->basic_salary + $employee->payrollDetails->allowances,
                        'deductions' => $employee->payrollDetails->deductions,
                        'tax_amount' => $employee->payrollDetails->calculateTax($employee->payrollDetails->basic_salary + $employee->payrollDetails->allowances, $paymentDate),
                        'status' => 'pending'
                    ]);
                    $paysheet->save();
                }
            }

            Yii::$app->session->setFlash('success', 'Paysheets generated successfully');
            return $this->redirect(['index']);
        }

        return $this->redirect(['generate']);
    }

    public function actionGenerate()
    {
        $model = new PaysheetGenerateForm();
        $employeeList = Employee::getList();

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->employee_ids)) {
                Yii::$app->session->setFlash('error', 'Please select at least one employee.');
                return $this->render('generate', [
                    'model' => $model,
                    'employeeList' => $employeeList,
                ]);
            }

            if ($model->validate()) {
                $successCount = 0;
                $errorCount = 0;
                $errors = [];

                // Define the range of months to process
                $monthsToProcess = $model->month ? [$model->month] : range(1, 12);

                // Get current date components for future date validation
                $currentYear = (int)date('Y');
                $currentMonth = (int)date('n');

                foreach ($model->employee_ids as $employeeId) {
                    $employee = Employee::findOne($employeeId);
                    if (!$employee || !$employee->payrollDetails) {
                        $errors[] = "Payroll details not found for employee ID $employeeId";
                        $errorCount++;
                        continue;
                    }

                    foreach ($monthsToProcess as $month) {
                        // Skip future months
                        if ($model->year > $currentYear || ($model->year == $currentYear && $month > $currentMonth)) {
                            $errors[] = "Cannot generate paysheet for future period: {$model->year}-{$month}";
                            continue;
                        }

                        // Calculate dates for the month
                        $startDate = date('Y-m-01', strtotime("{$model->year}-{$month}-01"));
                        $endDate = date('Y-m-t', strtotime($startDate));

                        // Check if paysheet already exists for this period
                        $existingPaysheet = Paysheet::find()
                            ->where(['employee_id' => $employeeId])
                            ->andWhere(['>=', 'pay_period_start', $startDate])
                            ->andWhere(['<=', 'pay_period_end', $endDate])
                            ->one();

                        if ($existingPaysheet) {
                            $errors[] = "Paysheet already exists for {$employee->fullName} for period {$model->year}-{$month}";
                            $errorCount++;
                            continue;
                        }

                        $payrollDetails = $employee->payrollDetails;
                        $grossSalary = $payrollDetails->basic_salary + ($payrollDetails->allowances ?? 0);
                        $deductions = $payrollDetails->deductions ?? 0;
                        $paymentDate = date('Y-m-d'); // Use current date or paysheet date
                        $taxAmount = $payrollDetails->calculateTax($grossSalary, $paymentDate);
                        $netSalary = $grossSalary - $deductions - $taxAmount;

                        $paysheet = new Paysheet([
                            'employee_id' => $employeeId,
                            'pay_period_start' => $startDate,
                            'pay_period_end' => $endDate,
                            'payment_date' => date('Y-m-d'),
                            'basic_salary' => $payrollDetails->basic_salary,
                            'allowances' => $payrollDetails->allowances ?? 0,
                            'deductions' => $deductions,
                            'tax_amount' => $taxAmount,
                            'net_salary' => $netSalary,
                            'payment_method' => $payrollDetails->payment_method ?? 'cash',
                            'status' => 'pending'
                        ]);

                        if ($paysheet->save()) {
                            $successCount++;
                        } else {
                            $errors[] = "Failed to save paysheet for {$employee->fullName} for period {$model->year}-{$month}: " .
                                json_encode($paysheet->getErrors());
                            $errorCount++;
                        }
                    }
                }

                $message = "Generated $successCount paysheets successfully.";
                if ($errorCount > 0) {
                    $message .= " Failed to generate $errorCount paysheets.";
                    if (!empty($errors)) {
                        Yii::$app->session->setFlash('error', implode("\n", $errors));
                    }
                }

                Yii::$app->session->setFlash('success', $message);
                return $this->redirect(['index']);
            }
        }

        return $this->render('generate', [
            'model' => $model,
            'employeeList' => $employeeList,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Paysheet::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Paysheet updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionMarkAsPaid($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== 'pending') {
            Yii::$app->session->setFlash('error', 'Only pending paysheets can be marked as paid.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->status = 'paid';

                // Create expense record
                $expense = new Expense([
                    'title' => 'Payroll Payment - ' . $model->employee->fullName. ' (' . date('Y-m', strtotime($model->pay_period_start)) . ')',
                    'expense_date' => $model->payment_date,
                    'amount' => $model->net_salary,
                    'currency_code' => 'LKR',
                    'exchange_rate' => 1,
                    'amount_lkr' => $model->net_salary,
                    'expense_category_id' => Params::get('payrollExpenseCategoryId'),
                    'payment_method' => $model->payment_method,
                    'description' => "Payroll payment for " . $model->employee->fullName . " for period " .
                              date('Y-m', strtotime($model->pay_period_start)),
                    'payment_reference' => $model->payment_reference,
                ]);

                // Create financial transaction record
                $financialTransaction = new FinancialTransaction([
                    'transaction_date' => $model->payment_date,
                    'transaction_type' => FinancialTransaction::TRANSACTION_TYPE_WITHDRAWAL,
                    'amount' => $model->net_salary,
                    'exchange_rate' => 1,
                    'amount_lkr' => $model->net_salary,
                    'reference_type' => 'paysheet',
                    'reference_number' => $model->payment_reference,
                    'related_paysheet_id' => $model->id,
                    'related_expense_id' => null, // Will be set after expense is saved
                    'description' => "Payroll payment for " . $model->employee->fullName . " - " . date('Y-m', strtotime($model->pay_period_start)),
                    'category' => FinancialTransaction::CATEGORY_PAYROLL,
                    'payment_method' => $model->payment_method,
                    'status' => FinancialTransaction::STATUS_COMPLETED,
                ]);

                if ($model->save() && $expense->save()) {
                    // Set the related expense ID after expense is saved
                    $financialTransaction->related_expense_id = $expense->id;

                    if ($financialTransaction->save()) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Paysheet has been marked as paid and records updated.');
                        return $this->redirect(['index']);
                    }
                }

                throw new \Exception('Failed to save records: ' . json_encode(array_merge(
                    $model->getErrors(),
                    $expense->getErrors(),
                    $financialTransaction->getErrors()
                )));
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to process payment: ' . $e->getMessage());
            }
        }

        return $this->render('confirm-payment', [
            'model' => $model,
        ]);
    }
}
