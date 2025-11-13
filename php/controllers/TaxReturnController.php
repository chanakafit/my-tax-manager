<?php

namespace app\controllers;

use app\models\TaxYearSnapshot;
use app\models\TaxYearBankBalance;
use app\models\TaxYearLiabilityBalance;
use app\models\OwnerBankAccount;
use app\models\Liability;
use app\models\CapitalAsset;
use app\models\TaxRecord;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * TaxReturnController handles tax return submission and reporting
 */
class TaxReturnController extends BaseController
{
    /**
     * List all tax returns (snapshots)
     * @return string
     */
    public function actionList()
    {
        $snapshots = TaxYearSnapshot::find()
            ->orderBy(['tax_year' => SORT_DESC])
            ->all();

        return $this->render('list', [
            'snapshots' => $snapshots,
        ]);
    }

    /**
     * Display tax return submission form for a tax year
     * @param string $year
     * @return string
     */
    public function actionIndex($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $snapshot = TaxYearSnapshot::findOne(['tax_year' => $year]);

        return $this->render('index', [
            'year' => $year,
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * Manage year-end balances
     * @param string $year
     * @return string|\yii\web\Response
     */
    public function actionManageBalances($year)
    {
        $snapshot = TaxYearSnapshot::getOrCreate($year);

        if (!$snapshot) {
            Yii::$app->session->setFlash('error', 'Failed to create or load tax year snapshot.');
            return $this->redirect(['index', 'year' => $year]);
        }

        // Calculate tax year end date
        $taxYearEnd = ($year + 1) . '-03-31';

        // Get all owner bank accounts that were active
        $bankAccounts = OwnerBankAccount::find()->where(['is_active' => 1])->all();

        // Get liabilities that were active at the end of the tax year
        // This includes liabilities that:
        // 1. Started on or before the tax year end date
        // 2. Either still active OR settled after the tax year end date
        $liabilities = Liability::find()
            ->where(['<=', 'start_date', $taxYearEnd])
            ->andWhere([
                'or',
                ['status' => Liability::STATUS_ACTIVE],
                ['and',
                    ['status' => Liability::STATUS_SETTLED],
                    ['>', 'settlement_date', $taxYearEnd]
                ],
                ['and',
                    ['status' => Liability::STATUS_SETTLED],
                    ['settlement_date' => null]
                ]
            ])
            ->all();

        // Get capital assets acquired before the end of the tax year and not disposed
        $capitalAssets = CapitalAsset::find()
            ->where(['<=', 'purchase_date', $taxYearEnd])
            ->andWhere([
                'or',
                ['status' => 'active'],
                ['and',
                    ['status' => 'disposed'],
                    ['>', 'disposal_date', $taxYearEnd]
                ],
                ['and',
                    ['status' => 'disposed'],
                    ['disposal_date' => null]
                ]
            ])
            ->all();

        // Load existing balances
        $existingBankBalances = ArrayHelper::index($snapshot->bankBalances, 'bank_account_id');
        $existingLiabilityBalances = ArrayHelper::index($snapshot->liabilityBalances, 'liability_id');

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Update snapshot notes
                if (isset($post['TaxYearSnapshot'])) {
                    $snapshot->load($post);
                    $snapshot->save();
                }

                // Delete existing balances
                TaxYearBankBalance::deleteAll(['tax_year_snapshot_id' => $snapshot->id]);
                TaxYearLiabilityBalance::deleteAll(['tax_year_snapshot_id' => $snapshot->id]);

                // Save bank balances
                if (isset($post['BankBalance'])) {
                    foreach ($post['BankBalance'] as $accountId => $data) {
                        if (!empty($data['balance'])) {
                            // Check if we're updating existing or creating new
                            $balance = isset($existingBankBalances[$accountId])
                                ? $existingBankBalances[$accountId]
                                : new TaxYearBankBalance();

                            $balance->tax_year_snapshot_id = $snapshot->id;
                            $balance->bank_account_id = $accountId;
                            $balance->balance = $data['balance'];
                            $balance->balance_lkr = $data['balance_lkr'] ?? $data['balance'];

                            // Handle file upload
                            $uploadedFile = \yii\web\UploadedFile::getInstanceByName("BankBalance[{$accountId}][document]");
                            if ($uploadedFile) {
                                $balance->uploadedFile = $uploadedFile;
                            }

                            if (!$balance->save()) {
                                throw new \Exception('Failed to save bank balance for account ' . $accountId);
                            }
                        }
                    }
                }

                // Save liability balances
                if (isset($post['LiabilityBalance'])) {
                    foreach ($post['LiabilityBalance'] as $liabilityId => $data) {
                        if (!empty($data['outstanding_balance'])) {
                            $balance = new TaxYearLiabilityBalance();
                            $balance->tax_year_snapshot_id = $snapshot->id;
                            $balance->liability_id = $liabilityId;
                            $balance->outstanding_balance = $data['outstanding_balance'];
                            $balance->save();
                        }
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Year-end balances saved successfully.');
                return $this->redirect(['view-report', 'year' => $year]);

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to save balances: ' . $e->getMessage());
            }
        }

        return $this->render('manage-balances', [
            'snapshot' => $snapshot,
            'year' => $year,
            'taxYearEnd' => $taxYearEnd,
            'bankAccounts' => $bankAccounts,
            'liabilities' => $liabilities,
            'capitalAssets' => $capitalAssets,
            'existingBankBalances' => $existingBankBalances,
            'existingLiabilityBalances' => $existingLiabilityBalances,
        ]);
    }

    /**
     * View tax return report
     * @param string $year
     * @return string
     */
    public function actionViewReport($year)
    {
        $snapshot = TaxYearSnapshot::findOne(['tax_year' => $year]);

        if (!$snapshot) {
            Yii::$app->session->setFlash('error', 'No snapshot found for this tax year. Please manage balances first.');
            return $this->redirect(['manage-balances', 'year' => $year]);
        }

        $taxYearStart = $year . '-04-01';
        $taxYearEnd = ($year + 1) . '-03-31';

        // Get assets categorized
        $data = $this->prepareReportData($year, $snapshot, $taxYearStart, $taxYearEnd);

        return $this->render('view-report', [
            'snapshot' => $snapshot,
            'year' => $year,
            'data' => $data,
            'taxYearStart' => $taxYearStart,
            'taxYearEnd' => $taxYearEnd,
        ]);
    }

    /**
     * Export tax return report with bank statements as ZIP
     * @param string $year
     * @return \yii\web\Response
     */
    public function actionExportExcel($year)
    {
        $snapshot = TaxYearSnapshot::findOne(['tax_year' => $year]);

        if (!$snapshot) {
            Yii::$app->session->setFlash('error', 'No snapshot found for this tax year.');
            return $this->redirect(['index', 'year' => $year]);
        }

        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            Yii::error('ZipArchive class not found. PHP zip extension may not be installed.');
            Yii::$app->session->setFlash('error', 'ZIP functionality is not available. Please contact system administrator.');
            return $this->redirect(['view-report', 'year' => $year]);
        }

        $taxYearStart = $year . '-04-01';
        $taxYearEnd = ($year + 1) . '-03-31';
        $data = $this->prepareReportData($year, $snapshot, $taxYearStart, $taxYearEnd);

        // Create temporary directory for files
        $tempDir = Yii::getAlias('@runtime/tax-return-export-' . $year . '-' . time());
        mkdir($tempDir, 0777, true);

        // 1. Generate Tax Return Excel file
        $spreadsheet = $this->generateExcelReport($year, $data);
        $writer = new Xlsx($spreadsheet);
        $taxReturnFilename = "Tax_Return_{$year}_" . date('Y-m-d') . ".xlsx";
        $taxReturnPath = $tempDir . '/' . $taxReturnFilename;
        $writer->save($taxReturnPath);

        // 2. Generate Expenses Excel file
        $expensesFilename = "Expenses_{$year}_" . date('Y-m-d') . ".xlsx";
        $expensesPath = $tempDir . '/' . $expensesFilename;
        $this->generateExpensesExcel($year, $taxYearStart, $taxYearEnd, $expensesPath);

        // 3. Generate Invoices Excel file
        $invoicesFilename = "Invoices_{$year}_" . date('Y-m-d') . ".xlsx";
        $invoicesPath = $tempDir . '/' . $invoicesFilename;
        $this->generateInvoicesExcel($year, $taxYearStart, $taxYearEnd, $invoicesPath);

        // 4. Generate Paysheets Excel file
        $paysheetsFilename = "Paysheets_{$year}_" . date('Y-m-d') . ".xlsx";
        $paysheetsPath = $tempDir . '/' . $paysheetsFilename;
        $this->generatePaysheetsExcel($year, $taxYearStart, $taxYearEnd, $paysheetsPath);

        // Create ZIP file
        $zipFilename = "Tax_Return_Package_{$year}_" . date('Y-m-d') . ".zip";
        $zipPath = $tempDir . '/' . $zipFilename;
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            Yii::error('Could not create ZIP file');
            Yii::$app->session->setFlash('error', 'Failed to create ZIP file.');
            return $this->redirect(['view-report', 'year' => $year]);
        }

        // Add all Excel files to ZIP root
        $zip->addFile($taxReturnPath, $taxReturnFilename);
        $zip->addFile($expensesPath, $expensesFilename);
        $zip->addFile($invoicesPath, $invoicesFilename);
        $zip->addFile($paysheetsPath, $paysheetsFilename);

        // Add bank statements to ZIP
        $bankBalances = $snapshot->getBankBalances()->with('bankAccount')->all();
        foreach ($bankBalances as $balance) {
            if ($balance->supporting_document) {
                $documentPath = Yii::getAlias('@webroot/' . $balance->supporting_document);
                if (file_exists($documentPath)) {
                    $account = $balance->bankAccount;
                    $extension = pathinfo($balance->supporting_document, PATHINFO_EXTENSION);
                    $docFilename = "Bank_Statements/" . $account->bank_name . '_' . $account->account_number . '.' . $extension;
                    $zip->addFile($documentPath, $docFilename);
                }
            }
        }

        // Add expense receipts to ZIP
        $expenses = \app\models\Expense::find()
            ->where(['between', 'expense_date', $taxYearStart, $taxYearEnd])
            ->all();

        foreach ($expenses as $expense) {
            if ($expense->receipt_path && file_exists(Yii::getAlias('@webroot/' . $expense->receipt_path))) {
                $extension = pathinfo($expense->receipt_path, PATHINFO_EXTENSION);
                $safeTitle = preg_replace('/[^A-Za-z0-9_-]/', '_', $expense->title);
                $receiptFilename = "Expense_Receipts/" . date('Y-m-d', strtotime($expense->expense_date)) . '_' . $safeTitle . '_' . $expense->id . '.' . $extension;
                $zip->addFile(Yii::getAlias('@webroot/' . $expense->receipt_path), $receiptFilename);
            }
        }

        // Add invoice PDFs to ZIP
        $invoices = \app\models\Invoice::find()
            ->where(['between', 'invoice_date', $taxYearStart, $taxYearEnd])
            ->all();

        foreach ($invoices as $invoice) {
            // Generate PDF for each invoice
            $pdfPath = $this->generateInvoicePDF($invoice, $tempDir);
            if ($pdfPath && file_exists($pdfPath)) {
                $pdfFilename = "Invoice_PDFs/" . $invoice->invoice_number . '.pdf';
                $zip->addFile($pdfPath, $pdfFilename);
            }
        }

        $zip->close();

        // Send ZIP file to browser
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment;filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);

        // Clean up temporary files
        $this->cleanupTempDirectory($tempDir);

        exit;
    }

    /**
     * Prepare report data
     * @param string $year
     * @param TaxYearSnapshot $snapshot
     * @param string $taxYearStart
     * @param string $taxYearEnd
     * @return array
     */
    private function prepareReportData($year, $snapshot, $taxYearStart, $taxYearEnd)
    {
        // Immovable properties - personal
        $personalImmovableExisting = CapitalAsset::find()
            ->where([
                'asset_type' => 'personal',
                'asset_category' => 'immovable',
                'status' => 'active'
            ])
            ->andWhere(['<', 'purchase_date', $taxYearStart])
            ->all();

        $personalImmovablePurchased = CapitalAsset::find()
            ->where([
                'asset_type' => 'personal',
                'asset_category' => 'immovable',
            ])
            ->andWhere(['between', 'purchase_date', $taxYearStart, $taxYearEnd])
            ->all();

        // Movable properties - personal
        $personalMovableExisting = CapitalAsset::find()
            ->where([
                'asset_type' => 'personal',
                'asset_category' => 'movable',
                'status' => 'active'
            ])
            ->andWhere(['<', 'purchase_date', $taxYearStart])
            ->all();

        $personalMovablePurchased = CapitalAsset::find()
            ->where([
                'asset_type' => 'personal',
                'asset_category' => 'movable',
            ])
            ->andWhere(['between', 'purchase_date', $taxYearStart, $taxYearEnd])
            ->all();

        // Business assets
        $businessImmovableExisting = CapitalAsset::find()
            ->where([
                'asset_type' => 'business',
                'asset_category' => 'immovable',
                'status' => 'active'
            ])
            ->andWhere(['<', 'purchase_date', $taxYearStart])
            ->all();

        $businessImmovablePurchased = CapitalAsset::find()
            ->where([
                'asset_type' => 'business',
                'asset_category' => 'immovable',
            ])
            ->andWhere(['between', 'purchase_date', $taxYearStart, $taxYearEnd])
            ->all();

        $businessMovableExisting = CapitalAsset::find()
            ->where([
                'asset_type' => 'business',
                'asset_category' => 'movable',
                'status' => 'active'
            ])
            ->andWhere(['<', 'purchase_date', $taxYearStart])
            ->all();

        $businessMovablePurchased = CapitalAsset::find()
            ->where([
                'asset_type' => 'business',
                'asset_category' => 'movable',
            ])
            ->andWhere(['between', 'purchase_date', $taxYearStart, $taxYearEnd])
            ->all();

        // Disposed assets
        $assetsDisposed = CapitalAsset::find()
            ->where(['status' => 'disposed'])
            ->andWhere(['between', 'disposal_date', $taxYearStart, $taxYearEnd])
            ->all();

        // Bank balances
        $bankBalances = $snapshot->getBankBalances()->with('bankAccount')->all();

        // Liabilities
        $personalLiabilitiesExisting = Liability::find()
            ->where([
                'liability_type' => Liability::TYPE_PERSONAL,
                'status' => Liability::STATUS_ACTIVE
            ])
            ->andWhere(['<', 'start_date', $taxYearStart])
            ->all();

        $personalLiabilitiesStarted = Liability::find()
            ->where(['liability_type' => Liability::TYPE_PERSONAL])
            ->andWhere(['between', 'start_date', $taxYearStart, $taxYearEnd])
            ->all();

        $businessLiabilitiesExisting = Liability::find()
            ->where([
                'liability_type' => Liability::TYPE_BUSINESS,
                'status' => Liability::STATUS_ACTIVE
            ])
            ->andWhere(['<', 'start_date', $taxYearStart])
            ->all();

        $businessLiabilitiesStarted = Liability::find()
            ->where(['liability_type' => Liability::TYPE_BUSINESS])
            ->andWhere(['between', 'start_date', $taxYearStart, $taxYearEnd])
            ->all();

        // Get liability balances
        $liabilityBalances = ArrayHelper::index($snapshot->getLiabilityBalances()->with('liability')->all(), 'liability_id');

        return [
            'personalImmovableExisting' => $personalImmovableExisting,
            'personalImmovablePurchased' => $personalImmovablePurchased,
            'personalMovableExisting' => $personalMovableExisting,
            'personalMovablePurchased' => $personalMovablePurchased,
            'businessImmovableExisting' => $businessImmovableExisting,
            'businessImmovablePurchased' => $businessImmovablePurchased,
            'businessMovableExisting' => $businessMovableExisting,
            'businessMovablePurchased' => $businessMovablePurchased,
            'assetsDisposed' => $assetsDisposed,
            'bankBalances' => $bankBalances,
            'personalLiabilitiesExisting' => $personalLiabilitiesExisting,
            'personalLiabilitiesStarted' => $personalLiabilitiesStarted,
            'businessLiabilitiesExisting' => $businessLiabilitiesExisting,
            'businessLiabilitiesStarted' => $businessLiabilitiesStarted,
            'liabilityBalances' => $liabilityBalances,
        ];
    }

    /**
     * Generate Excel report
     * @param string $year
     * @param array $data
     * @return Spreadsheet
     */
    private function generateExcelReport($year, $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Tax Return {$year}");

        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "TAX RETURN SUBMISSION - ASSESSMENT YEAR {$year}-" . ($year + 1));
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        // Assets Section
        $row = $this->addAssetSection($sheet, $row, 'IMMOVABLE PROPERTIES (PERSONAL)',
            $data['personalImmovableExisting'], $data['personalImmovablePurchased']);

        $row = $this->addAssetSection($sheet, $row, 'MOVABLE PROPERTIES (PERSONAL)',
            $data['personalMovableExisting'], $data['personalMovablePurchased']);

        $row = $this->addAssetSection($sheet, $row, 'IMMOVABLE PROPERTIES (BUSINESS)',
            $data['businessImmovableExisting'], $data['businessImmovablePurchased']);

        $row = $this->addAssetSection($sheet, $row, 'MOVABLE PROPERTIES (BUSINESS)',
            $data['businessMovableExisting'], $data['businessMovablePurchased']);

        // Disposed Assets
        $row = $this->addDisposedAssetsSection($sheet, $row, $data['assetsDisposed']);

        // Bank Balances
        $row = $this->addBankBalancesSection($sheet, $row, $data['bankBalances']);

        // Liabilities
        $row = $this->addLiabilitiesSection($sheet, $row, 'PERSONAL LIABILITIES',
            $data['personalLiabilitiesExisting'], $data['personalLiabilitiesStarted'], $data['liabilityBalances']);

        $row = $this->addLiabilitiesSection($sheet, $row, 'BUSINESS LIABILITIES',
            $data['businessLiabilitiesExisting'], $data['businessLiabilitiesStarted'], $data['liabilityBalances']);

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function addAssetSection($sheet, $row, $title, $existingAssets, $purchasedAssets)
    {
        $sheet->setCellValue("A{$row}", $title);
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        if (count($existingAssets) > 0) {
            $sheet->setCellValue("A{$row}", "Existing (before tax year):");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue("A{$row}", "Asset Name");
            $sheet->setCellValue("B{$row}", "Purchase Date");
            $sheet->setCellValue("C{$row}", "Purchase Cost");
            $sheet->setCellValue("D{$row}", "Current Value");
            $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
            $row++;

            foreach ($existingAssets as $asset) {
                $sheet->setCellValue("A{$row}", $asset->asset_name);
                $sheet->setCellValue("B{$row}", $asset->purchase_date);
                $sheet->setCellValue("C{$row}", number_format($asset->purchase_cost, 2));
                $sheet->setCellValue("D{$row}", number_format($asset->current_written_down_value, 2));
                $row++;
            }
            $row++;
        }

        if (count($purchasedAssets) > 0) {
            $sheet->setCellValue("A{$row}", "Purchased during tax year:");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue("A{$row}", "Asset Name");
            $sheet->setCellValue("B{$row}", "Purchase Date");
            $sheet->setCellValue("C{$row}", "Purchase Cost");
            $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
            $row++;

            foreach ($purchasedAssets as $asset) {
                $sheet->setCellValue("A{$row}", $asset->asset_name);
                $sheet->setCellValue("B{$row}", $asset->purchase_date);
                $sheet->setCellValue("C{$row}", number_format($asset->purchase_cost, 2));
                $row++;
            }
            $row++;
        }

        $row++;
        return $row;
    }

    private function addDisposedAssetsSection($sheet, $row, $disposedAssets)
    {
        if (count($disposedAssets) == 0) {
            return $row;
        }

        $sheet->setCellValue("A{$row}", "ASSETS DISPOSED DURING TAX YEAR");
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", "Asset Name");
        $sheet->setCellValue("B{$row}", "Type");
        $sheet->setCellValue("C{$row}", "Purchase Cost");
        $sheet->setCellValue("D{$row}", "Disposal Date");
        $sheet->setCellValue("E{$row}", "Disposal Value");
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($disposedAssets as $asset) {
            $sheet->setCellValue("A{$row}", $asset->asset_name);
            $sheet->setCellValue("B{$row}", ucfirst($asset->asset_type));
            $sheet->setCellValue("C{$row}", number_format($asset->purchase_cost, 2));
            $sheet->setCellValue("D{$row}", $asset->disposal_date);
            $sheet->setCellValue("E{$row}", number_format($asset->disposal_value ?? 0, 2));
            $row++;
        }

        $row += 2;
        return $row;
    }

    private function addBankBalancesSection($sheet, $row, $bankBalances)
    {
        $sheet->setCellValue("A{$row}", "BANK BALANCES (AS AT END OF TAX YEAR)");
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", "Bank & Account");
        $sheet->setCellValue("B{$row}", "Account Number");
        $sheet->setCellValue("C{$row}", "Type");
        $sheet->setCellValue("D{$row}", "Balance (LKR)");
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($bankBalances as $balance) {
            $account = $balance->bankAccount;
            $sheet->setCellValue("A{$row}", $account->bank_name . ' - ' . $account->account_name);
            $sheet->setCellValue("B{$row}", $account->account_number);
            $sheet->setCellValue("C{$row}", ucfirst($account->account_holder_type));
            $sheet->setCellValue("D{$row}", number_format($balance->balance_lkr, 2));
            $row++;
        }

        $row += 2;
        return $row;
    }

    private function addLiabilitiesSection($sheet, $row, $title, $existingLiabilities, $startedLiabilities, $balances)
    {
        $sheet->setCellValue("A{$row}", $title);
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        if (count($existingLiabilities) > 0) {
            $sheet->setCellValue("A{$row}", "Existing (before tax year):");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue("A{$row}", "Lender");
            $sheet->setCellValue("B{$row}", "Type");
            $sheet->setCellValue("C{$row}", "Start Date");
            $sheet->setCellValue("D{$row}", "Original Amount");
            $sheet->setCellValue("E{$row}", "Outstanding Balance");
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
            $row++;

            foreach ($existingLiabilities as $liability) {
                $sheet->setCellValue("A{$row}", $liability->lender_name);
                $sheet->setCellValue("B{$row}", ucfirst($liability->liability_category));
                $sheet->setCellValue("C{$row}", $liability->start_date);
                $sheet->setCellValue("D{$row}", number_format($liability->original_amount, 2));
                $outstanding = isset($balances[$liability->id]) ? $balances[$liability->id]->outstanding_balance : 0;
                $sheet->setCellValue("E{$row}", number_format($outstanding, 2));
                $row++;
            }
            $row++;
        }

        if (count($startedLiabilities) > 0) {
            $sheet->setCellValue("A{$row}", "Started during tax year:");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue("A{$row}", "Lender");
            $sheet->setCellValue("B{$row}", "Type");
            $sheet->setCellValue("C{$row}", "Start Date");
            $sheet->setCellValue("D{$row}", "Amount");
            $sheet->setCellValue("E{$row}", "Outstanding Balance");
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
            $row++;

            foreach ($startedLiabilities as $liability) {
                $sheet->setCellValue("A{$row}", $liability->lender_name);
                $sheet->setCellValue("B{$row}", ucfirst($liability->liability_category));
                $sheet->setCellValue("C{$row}", $liability->start_date);
                $sheet->setCellValue("D{$row}", number_format($liability->original_amount, 2));
                $outstanding = isset($balances[$liability->id]) ? $balances[$liability->id]->outstanding_balance : 0;
                $sheet->setCellValue("E{$row}", number_format($outstanding, 2));
                $row++;
            }
            $row++;
        }

        $row++;
        return $row;
    }

    /**
     * Generate Expenses Excel file
     */
    private function generateExpensesExcel($year, $taxYearStart, $taxYearEnd, $filePath)
    {
        $expenses = \app\models\Expense::find()
            ->where(['between', 'expense_date', $taxYearStart, $taxYearEnd])
            ->orderBy(['expense_date' => SORT_ASC])
            ->with(['expenseCategory', 'vendor'])
            ->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Expenses {$year}");

        // Title
        $sheet->setCellValue('A1', "EXPENSES - TAX YEAR {$year}-" . ($year + 1));
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Headers
        $sheet->setCellValue('A3', 'Date');
        $sheet->setCellValue('B3', 'Category');
        $sheet->setCellValue('C3', 'Title');
        $sheet->setCellValue('D3', 'Description');
        $sheet->setCellValue('E3', 'Vendor');
        $sheet->setCellValue('F3', 'Amount (LKR)');
        $sheet->setCellValue('G3', 'Payment Method');
        $sheet->setCellValue('H3', 'Receipt');
        $sheet->getStyle('A3:H3')->getFont()->setBold(true);

        $row = 4;
        $totalAmount = 0;

        foreach ($expenses as $expense) {
            $sheet->setCellValue("A{$row}", $expense->expense_date);
            $sheet->setCellValue("B{$row}", $expense->expenseCategory ? $expense->expenseCategory->name : 'N/A');
            $sheet->setCellValue("C{$row}", $expense->title);
            $sheet->setCellValue("D{$row}", $expense->description);
            $sheet->setCellValue("E{$row}", $expense->vendor ? $expense->vendor->name : 'N/A');
            $sheet->setCellValue("F{$row}", number_format($expense->amount_lkr, 2));
            $sheet->setCellValue("G{$row}", $expense->payment_method);
            $sheet->setCellValue("H{$row}", $expense->receipt_path ? 'Yes' : 'No');
            $totalAmount += $expense->amount_lkr;
            $row++;
        }

        // Total row
        $sheet->setCellValue("E{$row}", 'TOTAL:');
        $sheet->setCellValue("F{$row}", number_format($totalAmount, 2));
        $sheet->getStyle("E{$row}:F{$row}")->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    /**
     * Generate Invoices Excel file
     */
    private function generateInvoicesExcel($year, $taxYearStart, $taxYearEnd, $filePath)
    {
        $invoices = \app\models\Invoice::find()
            ->where(['between', 'invoice_date', $taxYearStart, $taxYearEnd])
            ->orderBy(['invoice_date' => SORT_ASC])
            ->with('customer')
            ->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Invoices {$year}");

        // Title
        $sheet->setCellValue('A1', "INVOICES - TAX YEAR {$year}-" . ($year + 1));
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Headers
        $sheet->setCellValue('A3', 'Invoice #');
        $sheet->setCellValue('B3', 'Date');
        $sheet->setCellValue('C3', 'Customer');
        $sheet->setCellValue('D3', 'Due Date');
        $sheet->setCellValue('E3', 'Payment Date');
        $sheet->setCellValue('F3', 'Subtotal');
        $sheet->setCellValue('G3', 'Tax');
        $sheet->setCellValue('H3', 'Total (LKR)');
        $sheet->setCellValue('I3', 'Status');
        $sheet->getStyle('A3:I3')->getFont()->setBold(true);

        $row = 4;
        $totalAmount = 0;

        foreach ($invoices as $invoice) {
            $sheet->setCellValue("A{$row}", $invoice->invoice_number);
            $sheet->setCellValue("B{$row}", $invoice->invoice_date);
            $sheet->setCellValue("C{$row}", $invoice->customer ? $invoice->customer->company_name : 'N/A');
            $sheet->setCellValue("D{$row}", $invoice->due_date);
            $sheet->setCellValue("E{$row}", $invoice->payment_date ?? 'N/A');
            $sheet->setCellValue("F{$row}", number_format($invoice->subtotal, 2));
            $sheet->setCellValue("G{$row}", number_format($invoice->tax_amount, 2));
            $sheet->setCellValue("H{$row}", number_format($invoice->total_amount_lkr, 2));
            $sheet->setCellValue("I{$row}", ucfirst($invoice->status));
            $totalAmount += $invoice->total_amount_lkr;
            $row++;
        }

        // Total row
        $sheet->setCellValue("G{$row}", 'TOTAL:');
        $sheet->setCellValue("H{$row}", number_format($totalAmount, 2));
        $sheet->getStyle("G{$row}:H{$row}")->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    /**
     * Generate Paysheets Excel file
     */
    private function generatePaysheetsExcel($year, $taxYearStart, $taxYearEnd, $filePath)
    {
        $paysheets = \app\models\Paysheet::find()
            ->where(['between', 'payment_date', $taxYearStart, $taxYearEnd])
            ->orderBy(['payment_date' => SORT_ASC])
            ->with('employee')
            ->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Paysheets {$year}");

        // Title
        $sheet->setCellValue('A1', "PAYSHEETS - TAX YEAR {$year}-" . ($year + 1));
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Headers
        $sheet->setCellValue('A3', 'Payment Date');
        $sheet->setCellValue('B3', 'Employee');
        $sheet->setCellValue('C3', 'Period Start');
        $sheet->setCellValue('D3', 'Period End');
        $sheet->setCellValue('E3', 'Basic Salary');
        $sheet->setCellValue('F3', 'Allowances');
        $sheet->setCellValue('G3', 'Deductions');
        $sheet->setCellValue('H3', 'Net Salary');
        $sheet->setCellValue('I3', 'Payment Method');
        $sheet->setCellValue('J3', 'Status');
        $sheet->getStyle('A3:J3')->getFont()->setBold(true);

        $row = 4;
        $totalNetSalary = 0;

        foreach ($paysheets as $paysheet) {
            $employeeName = $paysheet->employee ? ($paysheet->employee->first_name . ' ' . $paysheet->employee->last_name) : 'N/A';
            $sheet->setCellValue("A{$row}", $paysheet->payment_date);
            $sheet->setCellValue("B{$row}", $employeeName);
            $sheet->setCellValue("C{$row}", $paysheet->pay_period_start);
            $sheet->setCellValue("D{$row}", $paysheet->pay_period_end);
            $sheet->setCellValue("E{$row}", number_format($paysheet->basic_salary, 2));
            $sheet->setCellValue("F{$row}", number_format($paysheet->allowances ?? 0, 2));
            $sheet->setCellValue("G{$row}", number_format($paysheet->deductions ?? 0, 2));
            $sheet->setCellValue("H{$row}", number_format($paysheet->net_salary, 2));
            $sheet->setCellValue("I{$row}", $paysheet->payment_method ?? 'N/A');
            $sheet->setCellValue("J{$row}", ucfirst($paysheet->status));
            $totalNetSalary += $paysheet->net_salary;
            $row++;
        }

        // Total row
        $sheet->setCellValue("G{$row}", 'TOTAL NET SALARY:');
        $sheet->setCellValue("H{$row}", number_format($totalNetSalary, 2));
        $sheet->getStyle("G{$row}:H{$row}")->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    /**
     * Generate Invoice PDF
     */
    private function generateInvoicePDF($invoice, $tempDir)
    {
        try {
            $pdfGenerator = new \app\components\InvoicePdfGenerator();
            $pdfContent = $pdfGenerator->generate($invoice);

            $pdfFilename = $invoice->invoice_number . '.pdf';
            $pdfPath = $tempDir . '/' . $pdfFilename;

            file_put_contents($pdfPath, $pdfContent);

            return $pdfPath;
        } catch (\Exception $e) {
            Yii::error('Failed to generate invoice PDF: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean up temporary directory
     */
    private function cleanupTempDirectory($tempDir)
    {
        if (!is_dir($tempDir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($tempDir);
    }
}

