<?php

namespace app\controllers;

use app\helpers\Params;
use app\models\Customer;
use app\models\FinancialTransaction;
use app\models\InvoiceLink;
use app\models\InvoiceSearch;
use Yii;
use app\models\Invoice;
use app\models\InvoiceItem;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class InvoiceController extends BaseController
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'generate-public-link' => ['post'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex(): string
    {
        $searchModel = new InvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionCreate()
    {
        $model = new Invoice();
        $model->invoice_number = $model->generateInvoiceNumber();
        $model->invoice_date = date('Y-m-d');
        $model->due_date = date('Y-m-d', strtotime('+30 days'));

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $items = Yii::$app->request->post('InvoiceItem', []);
                    $totalTax = 0;
                    $subtotal = 0;

                    foreach ($items as $item) {
                        $invoiceItem = new InvoiceItem();
                        $invoiceItem->invoice_id = $model->id;
                        $invoiceItem->item_name = $item['item_name'];
                        $invoiceItem->description = $item['description'];
                        $invoiceItem->quantity = $item['quantity'];
                        $invoiceItem->unit_price = $item['unit_price'];
                        $invoiceItem->tax_rate = $item['tax_rate'] ?? 0;

                        // Calculate item level totals
                        $itemSubtotal = $invoiceItem->quantity * $invoiceItem->unit_price;
                        $itemTax = ($itemSubtotal * (float)$invoiceItem->tax_rate) / 100;
                        $invoiceItem->tax_amount = $itemTax;
                        $invoiceItem->discount = $item['discount'] ?? 0;
                        $invoiceItem->total_amount = $itemSubtotal - (float)$invoiceItem->discount;

                        if (!$invoiceItem->save()) {
                            throw new \Exception('Failed to save invoice item: ' . json_encode($invoiceItem->errors));
                        }

                        $totalTax += $itemTax;
                        $subtotal += $invoiceItem->total_amount;
                    }

                    // Update invoice totals
                    $model->subtotal = $subtotal;
                    $model->tax_amount = $totalTax;
                    $model->total_amount = $subtotal + $totalTax - $model->discount;

                    if ($model->save()) {
                        $transaction->commit();
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        $invoiceItems = [new InvoiceItem()];

        return $this->render('create', [
            'model' => $model,
            'invoiceItems' => $invoiceItems,
        ]);
    }

    public function actionMarkAsPaid($id)
    {
        $model = $this->findModel($id);

        // Only allow marking unpaid invoices as paid
        if ($model->status === 'paid') {
            return $this->redirect(['view', 'id' => $id]);
        }

        // Set default values
        if (!$model->payment_date) {
            $model->payment_date = date('Y-m-d');
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'paid';

            $transaction = Yii::$app->db->beginTransaction();
            try {
                // If it's a foreign currency invoice, update the LKR amount
                if ($model->currency_code !== 'LKR') {
                    $model->total_amount_lkr = $model->total_amount * $model->exchange_rate;
                }

                if ($model->save()) {
                    // Create financial transaction record
                    $financialTransaction = new \app\models\FinancialTransaction([
                        'transaction_date' => $model->payment_date,
                        'transaction_type' => FinancialTransaction::TRANSACTION_TYPE_REMITTANCE,
                        'category' => FinancialTransaction::CATEGORY_INCOME,
                        'amount' => (float)$model->total_amount,
                        'amount_lkr' => (float)$model->total_amount_lkr,
                        'exchange_rate' => (float)$model->exchange_rate,
                        'description' => "Payment received for Invoice #{$model->invoice_number}",
                        'reference_type' => FinancialTransaction::REFERENCE_TYPE_INVOICE,
                        'reference_number' => $model->reference_number,
                        'related_invoice_id' => $model->id,
                        'payment_method' => $model->payment_method,
                        'status' => 'completed',
                    ]);

                    if ($financialTransaction->save()) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Invoice marked as paid successfully.');
                        return $this->redirect(['view', 'id' => $id]);
                    }

                    throw new \Exception('Failed to save financial transaction: ' . json_encode($financialTransaction->errors));
                }

                $transaction->rollBack();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('mark-paid', [
            'model' => $model,
        ]);
    }

    /**
     * Get customer details including currency
     */
    public function actionGetCustomerDetails($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $customer = Customer::findOne($id);

        if ($customer) {
            return [
                'success' => true,
                'currency' => $customer->default_currency,
                'currencyName' => $customer->getCurrencyName()
            ];
        }

        return ['success' => false];
    }

    /**
     * Displays a single Invoice model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Eager load related data to avoid N+1 queries
        $model = Invoice::find()
            ->with([
                'customer',
                'paymentTerm',
                'invoiceItems',
            ])
            ->where(['id' => $id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('The requested invoice does not exist.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $invoiceItems = InvoiceItem::find()->where(['invoice_id' => $model->id])->all();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $items = Yii::$app->request->post('InvoiceItem', []);
                    $existingItemIds = [];
                    $totalTax = 0;
                    $subtotal = 0;

                    $invoiceItems = [];
                    foreach ($items as $item) {
                        $invoiceItem = isset($item['id']) ? InvoiceItem::findOne($item['id']) : new InvoiceItem();
                        if (!$invoiceItem) {
                            $invoiceItem = new InvoiceItem();
                        }
                        $invoiceItem->invoice_id = $model->id;
                        $invoiceItem->item_name = $item['item_name'];
                        $invoiceItem->description = $item['description'];
                        $invoiceItem->quantity = $item['quantity'];
                        $invoiceItem->unit_price = $item['unit_price'];
                        $invoiceItem->tax_rate = $item['tax_rate'] ?? 0;
                        $itemSubtotal = $invoiceItem->quantity * $invoiceItem->unit_price;
                        $itemTax = ($itemSubtotal * (float)$invoiceItem->tax_rate) / 100;
                        $invoiceItem->tax_amount = $itemTax;
                        $invoiceItem->discount = $item['discount'] ?? 0;
                        $invoiceItem->total_amount = $itemSubtotal - (float)$invoiceItem->discount;
                        if (!$invoiceItem->save()) {
                            throw new \Exception('Failed to save invoice item: ' . json_encode($invoiceItem->errors));
                        }
                        $existingItemIds[] = $invoiceItem->id;
                        $totalTax += $itemTax;
                        $subtotal += $invoiceItem->total_amount;
                        $invoiceItems[] = $invoiceItem;
                    }
                    // Delete removed items
                    InvoiceItem::deleteAll(['and', ['invoice_id' => $model->id], ['not in', 'id', $existingItemIds]]);
                    // Update invoice totals
                    $model->subtotal = $subtotal;
                    $model->tax_amount = $totalTax;
                    $model->total_amount = $subtotal + $totalTax - (float)$model->discount;
                    if ($model->save()) {
                        $transaction->commit();
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                // On error, reload invoice items from DB
                $invoiceItems = InvoiceItem::find()->where(['invoice_id' => $model->id])->all();
            }
        }
        // If not POST or failed, show form with items
        if (empty($invoiceItems)) {
            $invoiceItems = [new InvoiceItem()];
        }
        return $this->render('update', [
            'model' => $model,
            'invoiceItems' => $invoiceItems,
        ]);
    }

    public function actionDownloadPdf($id)
    {
        $model = $this->findModel($id);

        // Ensure customer's currency matches invoice currency
        if ($model->currency_code !== $model->customer->default_currency) {
            throw new \yii\web\BadRequestHttpException('Invoice currency must match customer\'s default currency.');
        }

        $pdfGenerator = new \app\components\InvoicePdfGenerator();
        return $pdfGenerator->generatePdf($model);
    }

    public function actionSendEmail($id)
    {
        $model = $this->findModel($id);
        $emailForm = new \app\models\forms\InvoiceEmailForm();

        if ($emailForm->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Generate public link
                $link = InvoiceLink::createForInvoice($model->id, time() + (30 * 24 * 60 * 60));
                if (!$link) {
                    throw new \Exception('Failed to generate public link for invoice');
                }
                $publicUrl = Yii::$app->urlManager->createAbsoluteUrl(['/public-invoice/view', 'token' => $link->token]);

                // Store the emails in CustomerEmail table
                $emails = $emailForm->prepareEmails();
                foreach ($emails as $type => $addressList) {
                    if (!empty($addressList)) {
                        foreach ($addressList as $email) {
                            $customerEmail = new \app\models\CustomerEmail([
                                'customer_id' => $model->customer_id,
                                'email' => $email,
                                'type' => $type
                            ]);
                            if (!$customerEmail->save()) {
                                throw new \Exception('Failed to save customer email: ' . json_encode($customerEmail->errors));
                            }
                        }
                    }
                }

                // Generate PDF
                $pdfGenerator = new \app\components\InvoicePdfGenerator();
                $pdfContent = $pdfGenerator->generatePdf($model, true);

                // Send email
                $mailer = Yii::$app->mailer;
                $message = $mailer->compose('invoice', [
                    'invoice' => $model,
                    'notes' => $emailForm->additionalNotes,
                    'publicUrl' => $publicUrl
                ])
                    ->setFrom([\app\helpers\ConfigHelper::getSenderEmail() => \app\helpers\ConfigHelper::getSenderName()])
                    ->setSubject($emailForm->subject)
                    ->attachContent($pdfContent, [
                        'fileName' => "Invoice_{$model->invoice_number}.pdf",
                        'contentType' => 'application/pdf'
                    ]);

                // Set recipients
                if (!empty($emails['to'])) {
                    $message->setTo($emails['to']);
                }
                if (!empty($emails['cc'])) {
                    $message->setCc($emails['cc']);
                }
                if (!empty($emails['bcc'])) {
                    $message->setBcc($emails['bcc']);
                }

                if ($message->send()) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Invoice has been sent successfully.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }

                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to send the invoice.');
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'An error occurred: ' . $e->getMessage());
                throw $e;
            }
        }

        return $this->render('email-form', [
            'model' => $model,
            'emailForm' => $emailForm,
        ]);
    }

    public function actionGeneratePublicLink($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        // Generate link valid for 30 days
        $link = InvoiceLink::createForInvoice($model->id, time() + (30 * 24 * 60 * 60));

        if ($link) {
            return [
                'success' => true,
                'url' => Yii::$app->urlManager->createAbsoluteUrl(['/public-invoice/view', 'token' => $link->token])
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to generate public link'
        ];
    }

    protected function findModel($id)
    {
        if (($model = Invoice::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested invoice does not exist.');
    }
}
