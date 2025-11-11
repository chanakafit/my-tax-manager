<?php

namespace app\controllers;

use app\helpers\ConstantsHelper;
use app\models\ExpenseCategory;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\Invoice;
use app\models\Expense;
use app\models\TaxRecord;
use app\models\FinancialTransaction;
use yii\data\ActiveDataProvider;

class SiteController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return Response
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }
        return $this->redirect(['dashboard']);
    }

    /**
     * Financial dashboard
     */
    public function actionDashboard()
    {
        // Get current tax year
        $currentDate = new \DateTime();
        $currentMonth = (int)$currentDate->format('n');
        $currentYear = (int)$currentDate->format('Y');

        // If we're in January-March, we're in the previous year's tax year
        $taxYear = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
        $taxYearStart = new \DateTime("$taxYear-04-01");
        $taxYearEnd = (clone $taxYearStart)->modify('+1 year')->modify('-1 day'); // March 31st next year

        // Get daily transactions for cumulative balance
        try {
            $transactions = (new \yii\db\Query())
                ->select([
                    'transaction_date',
                    'category',
                    'SUM(COALESCE(amount_lkr, 0)) as total_amount'
                ])
                ->from(FinancialTransaction::tableName())
                ->where(['and',
                    ['>=', 'transaction_date', $taxYearStart->format('Y-m-d')],
                    ['<=', 'transaction_date', $taxYearEnd->format('Y-m-d')],
                    ['in', 'category', ConstantsHelper::getConstants(FinancialTransaction::class, 'CATEGORY_')],
                ])
                ->groupBy(['transaction_date', 'category'])
                ->orderBy(['transaction_date' => SORT_ASC])
                ->all();
        } catch (\Exception $e) {
            Yii::error("Failed to fetch transaction data: " . $e->getMessage());
            $transactions = [];
        }

        // Calculate cumulative balance
        $cumulativeData = [];
        $balance = 0;
        $currentDate = clone $taxYearStart;
        while ($currentDate <= $taxYearEnd) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayTransactions = array_filter($transactions, function($t) use ($dateStr) {
                return $t['transaction_date'] === $dateStr;
            });

            foreach ($dayTransactions as $trans) {
                if ($trans['category'] === FinancialTransaction::CATEGORY_INCOME) {
                    $balance += (float)$trans['total_amount'];
                } else if ($trans['category'] === FinancialTransaction::CATEGORY_EXPENSE) {
                    $balance -= (float)$trans['total_amount'];
                } else if ($trans['category'] == FinancialTransaction::CATEGORY_PAYROLL) {
                    $balance -= (float)$trans['total_amount'];
                }
            }

            $cumulativeData[] = [
                'date' => $dateStr,
                'balance' => $balance
            ];

            $currentDate->modify('+1 day');
        }

        // Get yearly data with error handling
        try {
            $yearlyData = (new \yii\db\Query())
                ->select([
                    'YEAR(invoice_date) as year',
                    'SUM(COALESCE(total_amount_lkr, 0)) as total_income',
                ])
                ->from(Invoice::tableName())
                ->where(['and',
                    ['status' => Invoice::STATUS_PAID],
                    ['>=', 'invoice_date', $taxYearStart->format('Y-m-d')],
                    ['<=', 'invoice_date', $taxYearEnd->format('Y-m-d')]
                ])
                ->groupBy(['YEAR(invoice_date)'])
                ->orderBy(['year' => SORT_DESC])
                ->limit(5)
                ->all();
        } catch (\Exception $e) {
            Yii::error("Failed to fetch yearly income data: " . $e->getMessage());
            $yearlyData = [];
        }

        // Get expense data with error handling
        try {
            $yearlyExpenses = (new \yii\db\Query())
                ->select([
                    'YEAR(expense_date) as year',
                    'SUM(COALESCE(amount_lkr, 0)) as total_expenses',
                ])
                ->from(Expense::tableName())
                ->where(['and',
                    ['status' => 'completed'],
                    ['>=', 'expense_date', $taxYearStart->format('Y-m-d')],
                    ['<=', 'expense_date', $taxYearEnd->format('Y-m-d')]
                ])
                ->groupBy(['YEAR(expense_date)'])
                ->orderBy(['year' => SORT_DESC])
                ->limit(5)
                ->all();
        } catch (\Exception $e) {
            Yii::error("Failed to fetch yearly expense data: " . $e->getMessage());
            $yearlyExpenses = [];
        }

        // Get tax summary with error handling
        try {
            $taxSummary = TaxRecord::find()
                ->select(['tax_code', 'tax_amount', 'taxable_amount', 'payment_status'])
                ->where(['and',
                    ['>=', 'tax_period_start', $taxYearStart->format('Y-m-d')],
                    ['<=', 'tax_period_end', $taxYearEnd->format('Y-m-d')]
                ])
                ->orderBy(['tax_period_start' => SORT_DESC])
                ->all();
        } catch (\Exception $e) {
            Yii::error("Failed to fetch tax summary: " . $e->getMessage());
            $taxSummary = [];
        }

        // Recent transactions with error handling
        try {
            $recentTransactions = new \yii\data\ActiveDataProvider([
                'query' => FinancialTransaction::find()
                    ->where(['and',
                        ['>=', 'transaction_date', $taxYearStart->format('Y-m-d')],
                        ['<=', 'transaction_date', $taxYearEnd->format('Y-m-d')]
                    ])
                    ->orderBy(['transaction_date' => SORT_DESC])
                    ->limit(10),
                'pagination' => false,
            ]);
        } catch (\Exception $e) {
            Yii::error("Failed to fetch recent transactions: " . $e->getMessage());
            $recentTransactions = new \yii\data\ArrayDataProvider([
                'allModels' => [],
                'pagination' => false,
            ]);
        }

        // Monthly trends with error handling
        try {
            $monthlyTrends = (new \yii\db\Query())
                ->select([
                    'MONTH(transaction_date) as month',
                    'category',
                    'SUM(COALESCE(amount_lkr, 0)) as total_amount'
                ])
                ->from(FinancialTransaction::tableName())
                ->where(['and',
                    ['>=', 'transaction_date', $taxYearStart->format('Y-m-d')],
                    ['<=', 'transaction_date', $taxYearEnd->format('Y-m-d')]
                ])
                ->groupBy(['MONTH(transaction_date)', 'category'])
                ->orderBy(['month' => SORT_ASC])
                ->all();
        } catch (\Exception $e) {
            Yii::error("Failed to fetch monthly trends: " . $e->getMessage());
            $monthlyTrends = [];
        }

        // Format tax year string (e.g., "2025/2026")
        $taxYearString = sprintf("%d/%d", $taxYear, $taxYear + 1);

        return $this->render('dashboard', [
            'yearlyData' => $yearlyData,
            'yearlyExpenses' => $yearlyExpenses,
            'taxSummary' => $taxSummary,
            'recentTransactions' => $recentTransactions,
            'monthlyTrends' => $monthlyTrends,
            'cumulativeData' => $cumulativeData,
            'taxYear' => $taxYear,
            'taxYearString' => $taxYearString,
            'taxYearStart' => $taxYearStart,
            'taxYearEnd' => $taxYearEnd,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
