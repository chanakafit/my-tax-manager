<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Invoice;
use app\models\Expense;
use app\models\Employee;
use app\models\TaxRecord;

class FinanceController extends Controller
{
    /**
     * Handle daily financial tasks
     */
    public function actionDaily()
    {
        $this->processRecurringExpenses();
        $this->updateInvoiceStatuses();
        $this->notifyUpcomingPayments();
    }

    /**
     * Handle monthly financial tasks
     */
    public function actionMonthly()
    {
        $this->generatePaysheets();
        $this->calculateMonthlyTax();
    }

    /**
     * Process recurring expenses
     */
    protected function processRecurringExpenses()
    {
        $today = date('Y-m-d');
        $expenses = Expense::find()
            ->where(['is_recurring' => true])
            ->andWhere(['<=', 'next_recurring_date', $today])
            ->all();

        foreach ($expenses as $expense) {
            // Create new expense instance
            $newExpense = new Expense();
            $newExpense->attributes = $expense->attributes;
            $newExpense->expense_date = $today;

            // Calculate next recurring date
            switch ($expense->recurring_interval) {
                case 'monthly':
                    $nextDate = strtotime('+1 month');
                    break;
                case 'quarterly':
                    $nextDate = strtotime('+3 months');
                    break;
                case 'yearly':
                    $nextDate = strtotime('+1 year');
                    break;
                default:
                    $nextDate = false;
            }

            if ($nextDate) {
                $expense->next_recurring_date = date('Y-m-d', $nextDate);
                $expense->save();
            }

            $newExpense->save();
        }
    }

    /**
     * Update invoice statuses for overdue invoices
     */
    protected function updateInvoiceStatuses()
    {
        Invoice::updateOverdueStatus();
    }

    /**
     * Send notifications for upcoming payments
     */
    protected function notifyUpcomingPayments()
    {
        $weekLater = date('Y-m-d', strtotime('+1 week'));

        // Notify about upcoming expenses
        $expenses = Expense::find()
            ->where(['status' => 'pending'])
            ->andWhere(['<=', 'expense_date', $weekLater])
            ->all();

        foreach ($expenses as $expense) {
            Yii::$app->mailer->compose('expense-reminder', ['model' => $expense])
                ->setTo(Yii::$app->params['adminEmail'])
                ->setSubject('Upcoming Expense Payment')
                ->send();
        }

        // Notify about unpaid taxes
        $taxes = TaxRecord::find()
            ->where(['payment_status' => 'pending'])
            ->andWhere(['<=', 'tax_period_end', $weekLater])
            ->all();

        foreach ($taxes as $tax) {
            Yii::$app->mailer->compose('tax-reminder', ['model' => $tax])
                ->setTo(Yii::$app->params['adminEmail'])
                ->setSubject('Upcoming Tax Payment')
                ->send();
        }
    }

    /**
     * Generate monthly paysheets
     */
    protected function generatePaysheets()
    {
        if (date('d') === '01') { // Only run on first day of month
            $employees = Employee::find()->all();
            $month = date('m');
            $year = date('Y');

            foreach ($employees as $employee) {
                $paysheet = $employee->calculateMonthlyPaysheet($month, $year);
                $paysheet->save();
            }
        }
    }

    /**
     * Calculate monthly tax obligations
     */
    protected function calculateQuarterlyTax($taxCode = null)
    {
        if (!$taxCode) {
            $taxCode = date('Ym') . (ceil(date('m') / 3) - 1);
        }

        $taxRecord = TaxRecord::find()->where(['tax_code' => $taxCode])->one();
        if ($taxRecord) {
            if ($taxRecord->payment_status === 'paid') {

                echo "Tax record for period {$taxCode} is already marked as paid.\n";
                return;
            }
        } else {
            $taxRecord = new TaxRecord();
            $taxRecord->tax_code = $taxCode;
        }

        $taxRecord->calculateTax();


        if ($taxRecord) {
            echo "Tax record for period {$taxCode} calculated successfully.\n";
        } else {
            echo "Failed to calculate tax record for period {$taxCode}.\n";
        }

    }
}
