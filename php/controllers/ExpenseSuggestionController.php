<?php

namespace app\controllers;

use app\components\ExpenseHealthCheckService;
use app\models\Expense;
use app\models\ExpenseSuggestion;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ExpenseSuggestionController handles expense health check suggestions
 */
class ExpenseSuggestionController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'ignore' => ['POST'],
                    // create-expense accepts both GET (show form) and POST (submit form)
                ],
            ],
        ]);
    }

    /**
     * Lists active (pending and added) ExpenseSuggestion models.
     * Ignored suggestions are shown in a separate list.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ExpenseSuggestion::find()
                ->with(['expenseCategory', 'vendor', 'lastExpense'])
                ->where(['IN', 'status', [
                    ExpenseSuggestion::STATUS_PENDING,
                    ExpenseSuggestion::STATUS_ADDED
                ]])
                ->orderBy([
                    'status' => SORT_ASC,
                    'suggested_month' => SORT_DESC,
                ]),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'suggested_month',
                    'status',
                    'expense_category_id',
                    'vendor_id',
                    'avg_amount_lkr',
                    'generated_at',
                ],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists ignored ExpenseSuggestion models (temporary and permanent).
     *
     * @return string
     */
    public function actionIgnored()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ExpenseSuggestion::find()
                ->with(['expenseCategory', 'vendor', 'lastExpense'])
                ->where(['IN', 'status', [
                    ExpenseSuggestion::STATUS_IGNORED_TEMPORARY,
                    ExpenseSuggestion::STATUS_IGNORED_PERMANENT
                ]])
                ->orderBy([
                    'status' => SORT_ASC,
                    'suggested_month' => SORT_DESC,
                ]),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'suggested_month',
                    'status',
                    'expense_category_id',
                    'vendor_id',
                    'avg_amount_lkr',
                    'generated_at',
                    'actioned_at',
                ],
            ],
        ]);

        return $this->render('ignored', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ExpenseSuggestion model.
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Create an expense from a suggestion
     *
     * @param int $id Suggestion ID
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionCreateExpense($id)
    {
        $suggestion = $this->findModel($id);

        if ($suggestion->status !== ExpenseSuggestion::STATUS_PENDING) {
            Yii::$app->session->setFlash('error', 'This suggestion has already been actioned.');
            return $this->redirect(['index']);
        }

        $expense = new Expense();
        $expense->expense_category_id = $suggestion->expense_category_id;
        $expense->vendor_id = $suggestion->vendor_id;

        // Set date to the suggested month
        $suggestedDate = new \DateTime($suggestion->suggested_month);
        $expense->expense_date = $suggestedDate->format('Y-m-d');

        // Pre-fill with average amount
        $expense->amount = $suggestion->avg_amount_lkr;
        $expense->amount_lkr = $suggestion->avg_amount_lkr;
        $expense->currency_code = 'LKR';
        $expense->exchange_rate = 1.0;

        // Set default values
        $expense->status = 'paid';
        $expense->payment_method = 'bank_transfer';

        // Try to get title from last expense
        if ($suggestion->lastExpense) {
            $expense->title = $suggestion->lastExpense->title;
            $expense->description = $suggestion->lastExpense->description;
            $expense->payment_method = $suggestion->lastExpense->payment_method;
        } else {
            $expense->title = $suggestion->expenseCategory->name . ' - ' . $suggestion->vendor->name;
        }

        if (Yii::$app->request->isPost) {
            // Start database transaction
            $dbTransaction = Yii::$app->db->beginTransaction();

            try {
                $expense->load(Yii::$app->request->post());

                // Set receipt file before validation
                $expense->receipt_file = \yii\web\UploadedFile::getInstance($expense, 'receipt_file');

                // Validate model first
                if (!$expense->validate()) {
                    throw new \Exception('Validation failed: ' . json_encode($expense->errors));
                }

                // Save model
                if (!$expense->save(false)) { // false because we already validated
                    throw new \Exception('Failed to save expense');
                }

                // Handle receipt upload if present
                if ($expense->receipt_file && !$expense->uploadReceipt()) {
                    throw new \Exception('Failed to upload receipt');
                }

                // Create financial transaction for expense
                $transaction = new \app\models\FinancialTransaction([
                    'category' => \app\models\FinancialTransaction::CATEGORY_EXPENSE,
                    'amount' => $expense->amount,
                    'exchange_rate' => $expense->exchange_rate,
                    'amount_lkr' => $expense->amount_lkr,
                    'description' => "Expense: {$expense->title}",
                    'transaction_date' => $expense->expense_date,
                    'transaction_type' => \app\models\FinancialTransaction::TRANSACTION_TYPE_PAYMENT,
                    'reference_type' => \app\models\FinancialTransaction::REFERENCE_TYPE_EXPENSE,
                    'reference_number' => $expense->receipt_number,
                    'related_expense_id' => $expense->id,
                    'payment_method' => $expense->payment_method,
                    'status' => \app\models\FinancialTransaction::STATUS_COMPLETED,
                ]);

                if (!$transaction->save()) {
                    throw new \Exception('Failed to create financial transaction: ' . json_encode($transaction->errors));
                }

                // Reset any permanently ignored suggestions for this category/vendor combination
                if ($expense->vendor_id) {
                    $healthCheckService = new \app\components\ExpenseHealthCheckService();
                    $healthCheckService->resetIgnoredSuggestions($expense->expense_category_id, $expense->vendor_id);
                }

                // Mark suggestion as added
                $suggestion->markAsAdded(Yii::$app->user->id);

                // Commit transaction
                $dbTransaction->commit();

                Yii::$app->session->setFlash('success', 'Expense created successfully from suggestion.');
                return $this->redirect(['expense/view', 'id' => $expense->id]);

            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                Yii::error("Error creating expense from suggestion: " . $e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Failed to create expense: ' . $e->getMessage());
            }
        }

        return $this->render('create-expense', [
            'model' => $expense,
            'suggestion' => $suggestion,
        ]);
    }

    /**
     * Ignore a suggestion
     *
     * @return Response
     */
    public function actionIgnore()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        $ignoreType = Yii::$app->request->post('ignore_type'); // 'temporary' or 'permanent'
        $reason = Yii::$app->request->post('reason');

        try {
            $suggestion = $this->findModel($id);

            if ($suggestion->status !== ExpenseSuggestion::STATUS_PENDING) {
                return [
                    'success' => false,
                    'message' => 'This suggestion has already been actioned.',
                ];
            }

            if (!in_array($ignoreType, ['temporary', 'permanent'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid ignore type.',
                ];
            }

            if ($suggestion->markAsIgnored($ignoreType, $reason, Yii::$app->user->id)) {
                return [
                    'success' => true,
                    'message' => 'Suggestion ignored successfully.',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to ignore suggestion.',
                ];
            }
        } catch (\Exception $e) {
            Yii::error("Error ignoring suggestion: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a suggestion
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Suggestion deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete suggestion.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Get pending suggestions for dashboard widget
     *
     * @return Response
     */
    public function actionGetPendingSuggestions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $suggestions = ExpenseSuggestion::find()
                ->with(['expenseCategory', 'vendor'])
                ->where(['status' => ExpenseSuggestion::STATUS_PENDING])
                ->orderBy(['suggested_month' => SORT_DESC])
                ->limit(10)
                ->all();

            $data = [];
            foreach ($suggestions as $suggestion) {
                $data[] = [
                    'id' => $suggestion->id,
                    'category' => $suggestion->expenseCategory->name ?? 'Unknown',
                    'vendor' => $suggestion->vendor->name ?? 'Unknown',
                    'suggested_month' => Yii::$app->formatter->asDate($suggestion->suggested_month, 'php:M Y'),
                    'avg_amount' => Yii::$app->formatter->asCurrency($suggestion->avg_amount_lkr, 'LKR'),
                    'pattern_months' => $suggestion->getPatternMonthsArray(),
                ];
            }

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Yii::error("Error fetching pending suggestions: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Failed to fetch suggestions.',
            ];
        }
    }

    /**
     * Finds the ExpenseSuggestion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return ExpenseSuggestion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExpenseSuggestion::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested suggestion does not exist.');
    }
}

