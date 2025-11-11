<?php

namespace app\controllers;

use app\models\TaxRecord;
use Yii;
use app\models\TaxPayment;
use app\models\Invoice;
use app\models\Expense;
use yii\web\NotFoundHttpException;

class TaxYearController extends BaseController
{
    public function actionView($year)
    {
        // Get year summary
        $summary = TaxRecord::getYearSummary($year);

        return $this->render('view', [
            'year' => $year,
            'summary' => $summary,
            'invoices' => $summary['invoices'],
            'expenses' => $summary['expenses'],
            'paysheets' => $summary['paysheets'],
            'startDate' => $year . '-04-01',
            'endDate' => ($year + 1) . '-03-31',
        ]);
    }

    public function actionMakePayment($taxCode = null)
    {
        if($taxCode === null) {
            $taxCode = date('Y').'0';
        }
        $year = substr($taxCode, 0, 4);
        $quarter = substr($taxCode, 4, 1);
        $isQuarterly = in_array($quarter, ['1', '2', '3', '4']);
        $model = new TaxPayment();
        $model->tax_year = $year ?? date('Y');  // Set the tax year from URL parameter
        $model->quarter = $quarter;
        $model->payment_type = $isQuarterly ? TaxPayment::TYPE_QUARTERLY : TaxPayment::TYPE_FINAL;
        $model->payment_date = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            $model->uploadedFile = \yii\web\UploadedFile::getInstance($model, 'uploadedFile');

            if ($model->validate()) {
                try {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Tax payment recorded successfully.');
                        return $this->redirect(['view', 'year' => $model->tax_year]);
                    }
                } catch (\Exception $e) {
                    Yii::error('Error saving tax payment: ' . $e->getMessage());
                    Yii::$app->session->setFlash('error', 'Error saving tax payment. Please try again.');
                }
            }
        }

        return $this->render('payment-form', [
            'model' => $model,
        ]);
    }

    public function actionIndex()
    {
        $taxRecordTable = TaxRecord::tableName();
        $taxPaymentTable = TaxPayment::tableName();
        // Get unique tax years from both tax records and tax payments
        $query = "SELECT DISTINCT tax_year FROM (
            SELECT YEAR(tax_period_start) as tax_year FROM $taxRecordTable
            UNION
            SELECT tax_year FROM $taxPaymentTable
        ) as years ORDER BY tax_year DESC";

        $years = Yii::$app->db->createCommand($query)->queryColumn();

        return $this->render('index', [
            'years' => $years
        ]);
    }
}
