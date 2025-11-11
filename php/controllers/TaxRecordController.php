<?php

namespace app\controllers;

use app\models\Paysheet;
use Yii;
use app\models\TaxRecord;
use app\models\TaxRecordSearch;
use app\models\TaxConfig;
use app\models\Invoice;
use app\models\Expense;
use yii\web\NotFoundHttpException;

class  TaxRecordController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new TaxRecordSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCalculate()
    {
        if (Yii::$app->request->isPost) {
            $taxCode = Yii::$app->request->post('taxCode');

            $taxRecord = TaxRecord::find()->where(['tax_code' => $taxCode])->one();
            if (!$taxRecord) {
                $taxRecord = new TaxRecord();
                $taxRecord->tax_code = $taxCode;
            } else {
                if ($taxRecord->payment_status === 'paid') {
                    return $this->asJson(['success' => false, 'message' => 'Tax record already marked as paid']);
                }
            }

            if ($taxRecord->calculateTax()) {
                return $this->asJson([
                    'success' => true,
                    'data' => [
                        'income' => $taxRecord->total_income,
                        'expenses' => $taxRecord->total_expenses,
                        'profit' => $taxRecord->total_income - $taxRecord->total_expenses,
                        'taxableAmount' => $taxRecord->taxable_amount,
                        'taxAmount' => $taxRecord->tax_amount,
                    ]
                ]);
            }

            return $this->asJson([
                'success' => false,
                'message' => 'Failed to save tax record',
                'errors' => $taxRecord->errors
            ]);

        }

        return $this->asJson(['success' => false, 'message' => 'Invalid request method']);
    }

    public function actionMarkAsPaid($id)
    {
        $model = $this->findModel($id);
        $model->payment_status = 'paid';
        $model->payment_date = date('Y-m-d');

        if ($model->save()) {
            return $this->redirect(['index']);
        }

        throw new \Exception('Failed to update tax record status');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Get related invoices, expenses and paysheets
        $relatedInvoices = [];
        $relatedExpenses = [];
        $relatedPaysheets = [];
        $transactions = [];

        if ($model->related_invoice_ids) {
            $invoiceIds = json_decode($model->related_invoice_ids);
            if (!empty($invoiceIds)) {
                $relatedInvoices = Invoice::find()
                    ->where(['id' => $invoiceIds])
                    ->all();
            }
        }

        if ($model->related_expense_ids) {
            $expenseIds = json_decode($model->related_expense_ids);
            if (!empty($expenseIds)) {
                $relatedExpenses = Expense::find()
                    ->where(['id' => $expenseIds])
                    ->all();
            }
        }

        if ($model->related_paysheet_ids) {
            $paysheetIds = json_decode($model->related_paysheet_ids);
            if (!empty($paysheetIds)) {
                $relatedPaysheets = \app\models\Paysheet::find()
                    ->where([
                        'id' => $paysheetIds,
                        'status' => Paysheet::STATUS_PAID
                    ])
                    ->all();
            }
        }
        
        return $this->render('view', [
            'model' => $model,
            'relatedInvoices' => $relatedInvoices,
            'relatedExpenses' => $relatedExpenses,
            'relatedPaysheets' => $relatedPaysheets
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Prevent updating paid tax records
        if ($model->payment_status === 'paid') {
            Yii::$app->session->setFlash('error', 'Cannot update a paid tax record.');
            return $this->redirect(['view', 'id' => $id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Tax record updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = TaxRecord::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested tax record does not exist.');
    }
}
