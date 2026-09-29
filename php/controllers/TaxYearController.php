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

    /**
     * Record a tax payment. Reached with a tax code (from a tax record) or with
     * just a year (from the tax year pages).
     * @param string|null $taxCode e.g. 20251 for Q1 of 2025/2026, 20250 for the final payment
     * @param string|null $year Year of assessment, when no quarter is implied
     */
    public function actionMakePayment($taxCode = null, $year = null)
    {
        if ($taxCode === null) {
            $taxCode = ($year ?? date('Y')) . '0';
        }
        $taxYear = substr($taxCode, 0, 4);
        $quarter = substr($taxCode, 4, 1);
        $isQuarterly = in_array($quarter, ['1', '2', '3', '4']);
        $model = new TaxPayment();
        $model->tax_year = $taxYear;
        $model->quarter = $isQuarterly ? (int)$quarter : null;
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
            'taxYears' => self::selectableTaxYears(),
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'years' => self::taxYears()
        ]);
    }

    /**
     * Years of assessment that already have a tax record or a payment, newest first
     * @return array of year strings
     */
    public static function taxYears()
    {
        $taxRecordTable = TaxRecord::tableName();
        $taxPaymentTable = TaxPayment::tableName();
        // Get unique tax years from both tax records and tax payments
        $query = "SELECT DISTINCT tax_year FROM (
            SELECT YEAR(tax_period_start) as tax_year FROM $taxRecordTable
            UNION
            SELECT tax_year FROM $taxPaymentTable
        ) as years ORDER BY tax_year DESC";

        return Yii::$app->db->createCommand($query)->queryColumn();
    }

    /**
     * Years of assessment offered when recording a payment: every year already in
     * use, plus the current and next one, so a late payment for an older year can
     * still be recorded.
     * @return array ['2025' => '2025/2026', ...] newest first
     */
    public static function selectableTaxYears()
    {
        $currentYear = (int)date('Y');
        $years = array_map('intval', self::taxYears());
        $years = array_merge($years, range($currentYear - 2, $currentYear + 1));
        $years = array_unique(array_filter($years));
        rsort($years);

        $list = [];
        foreach ($years as $year) {
            $list[(string)$year] = $year . '/' . ($year + 1);
        }

        return $list;
    }
}
