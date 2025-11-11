<?php

namespace app\controllers;

use app\components\ExpenseHealthCheckService;
use app\models\ExpenseSearch;
use Yii;
use app\models\Expense;
use app\models\ExpenseCategory;
use app\models\FinancialTransaction;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ExpenseController extends BaseController
{
    public function actionIndex(): string
    {
        $searchModel = new ExpenseSearch();
        $dataProvider = new ActiveDataProvider([
            'query' => Expense::find()
                ->with(['expenseCategory']),
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'expense_date' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Expense();
        $model->expense_date = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            // Start database transaction
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                // Set receipt file before validation
                $model->receipt_file = UploadedFile::getInstance($model, 'receipt_file');
                
                // Validate model first
                if (!$model->validate()) {
                    throw new \Exception('Validation failed: ' . json_encode($model->errors));
                }

                // Save model
                if (!$model->save(false)) { // false because we already validated
                    throw new \Exception('Failed to save expense');
                }

                // Handle receipt upload if present
                if ($model->receipt_file && !$model->uploadReceipt()) {
                    throw new \Exception('Failed to upload receipt');
                }

                // Create financial transaction for expense
                $transaction = new FinancialTransaction([
                    'category' => FinancialTransaction::CATEGORY_EXPENSE,
                    'amount' => $model->amount,
                    'exchange_rate' => $model->exchange_rate,
                    'amount_lkr' => $model->amount_lkr,
                    'description' => "Expense: {$model->title}",
                    'transaction_date' => $model->expense_date,
                    'transaction_type' => FinancialTransaction::TRANSACTION_TYPE_PAYMENT,
                    'reference_type' => FinancialTransaction::REFERENCE_TYPE_EXPENSE,
                    'reference_number' => $model->receipt_number,
                    'related_expense_id' => $model->id,
                    'payment_method' => $model->payment_method,
                    'status' => FinancialTransaction::STATUS_COMPLETED,
                ]);

                if (!$transaction->save()) {
                    throw new \Exception('Failed to create financial transaction: ' . json_encode($transaction->errors));
                }

                // Handle recurring expense scheduling if needed
                if ($model->is_recurring) {
                    $this->scheduleRecurringExpense($model);
                }

                // Reset any permanently ignored suggestions for this category/vendor combination
                if ($model->vendor_id) {
                    $healthCheckService = new ExpenseHealthCheckService();
                    $healthCheckService->resetIgnoredSuggestions($model->expense_category_id, $model->vendor_id);
                }

                $dbTransaction->commit();
                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                Yii::error("Failed to create expense: " . $e->getMessage());
                $model->addError('', 'Failed to save expense: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => ArrayHelper::map(ExpenseCategory::find()->all(), 'id', 'name'),
            'vendorName' => '',
        ]);
    }

    public function actionMarkAsPaid($id)
    {
        $model = $this->findModel($id);
        $model->status = 'paid';
        $model->payment_date = date('Y-m-d');

        if ($model->save()) {
            return $this->redirect(['view', 'id' => $id]);
        }

        throw new \Exception('Failed to update expense status');
    }

    protected function scheduleRecurringExpense($expense)
    {
        // Calculate next recurring date based on interval
        $nextDate = strtotime($expense->expense_date);
        switch ($expense->recurring_interval) {
            case 'weekly':
                $nextDate = strtotime('+1 week', $nextDate);
                break;
            case 'monthly':
                $nextDate = strtotime('+1 month', $nextDate);
                break;
            case 'quarterly':
                $nextDate = strtotime('+3 months', $nextDate);
                break;
            case 'yearly':
                $nextDate = strtotime('+1 year', $nextDate);
                break;
        }

        $expense->next_recurring_date = date('Y-m-d', $nextDate);
        $expense->save();
    }

    protected function findModel($id)
    {
        if (($model = Expense::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested expense does not exist.');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Start database transaction
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                // Set receipt file before validation
                $model->receipt_file = UploadedFile::getInstance($model, 'receipt_file');
                
                // Validate model first
                if (!$model->validate()) {
                    throw new \Exception('Validation failed: ' . json_encode($model->errors));
                }

                // Save model
                if (!$model->save(false)) { // false because we already validated
                    throw new \Exception('Failed to update expense');
                }

                // Handle receipt upload if present
                if ($model->receipt_file && !$model->uploadReceipt()) {
                    throw new \Exception('Failed to upload receipt');
                }

                // Update or create financial transaction
                $transaction = FinancialTransaction::findOne(['related_expense_id' => $model->id]) ?? new FinancialTransaction();
                $transaction->setAttributes([
                    'category' => FinancialTransaction::CATEGORY_EXPENSE,
                    'amount' => $model->amount,
                    'exchange_rate' => $model->exchange_rate,
                    'amount_lkr' => $model->amount_lkr,
                    'description' => "Expense: {$model->title}",
                    'transaction_date' => $model->expense_date,
                    'transaction_type' => FinancialTransaction::TRANSACTION_TYPE_PAYMENT,
                    'reference_type' => FinancialTransaction::REFERENCE_TYPE_EXPENSE,
                    'reference_number' => $model->receipt_number,
                    'related_expense_id' => $model->id,
                    'payment_method' => $model->payment_method,
                    'status' => FinancialTransaction::STATUS_COMPLETED,
                ]);

                if (!$transaction->save()) {
                    throw new \Exception('Failed to update financial transaction: ' . json_encode($transaction->errors));
                }

                // If recurring settings changed, update schedule
                if ($model->is_recurring) {
                    $this->scheduleRecurringExpense($model);
                }

                $dbTransaction->commit();
                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                Yii::error("Failed to update expense: " . $e->getMessage());
                $model->addError('', 'Failed to update expense: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => ArrayHelper::map(ExpenseCategory::find()->all(), 'id', 'name'),
            'vendorName' => $model->vendor ? $model->vendor->name : '',
        ]);
    }
}
