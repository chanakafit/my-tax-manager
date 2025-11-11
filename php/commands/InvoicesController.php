<?php

namespace app\commands;

use app\models\Customer;
use app\models\Invoice;
use app\models\InvoiceItem;
use yii\console\Controller;
use yii\db\Migration;
use yii\helpers\Console;
use DateTime;

class InvoicesController extends Controller
{
    public function actionImport()
    {
        $this->stdout("Starting invoice import process...\n", Console::FG_YELLOW);

        $csvFile = dirname(__DIR__) . '/commands/Invoices.csv';
        if (!file_exists($csvFile)) {
            $this->stderr("Error: CSV file not found at {$csvFile}\n", Console::FG_RED);
            return 1;
        }

        $this->stdout("Reading CSV file from: {$csvFile}\n", Console::FG_YELLOW);

        try {
            $data = array_map(function($line) {
                return str_getcsv($line);
            }, file($csvFile));
        } catch (\Exception $e) {
            $this->stderr("Error reading CSV file: " . $e->getMessage() . "\n", Console::FG_RED);
            return 1;
        }

        $headers = array_shift($data); // Remove headers
        $this->stdout("Found " . count($data) . " invoices to import\n", Console::FG_YELLOW);

        try {
            $imported = 0;
            foreach ($data as $index => $column) {
                $this->stdout("Processing row " . ($index + 1) . "...\n", Console::FG_YELLOW);

                try {
                    $date = DateTime::createFromFormat('d-M-y', $column[0]);
                    if (!$date) {
                        throw new \Exception("Invalid date format: {$column[0]}");
                    }

                    $dueDate = DateTime::createFromFormat('d-M-y', $column[5]);
                    if (!$dueDate) {
                        throw new \Exception("Invalid due date format: {$column[5]}");
                    }

                    // Convert amount from "€4.000,00" format to decimal
                    $amount = str_replace(['€', '.', ','], ['', '', '.'], trim($column[6]));
                    if (!is_numeric($amount)) {
                        throw new \Exception("Invalid amount format: {$column[6]}");
                    }

                    $balanceDue = str_replace(['€', '.', ','], ['', '', '.'], trim($column[7]));
                    if (!is_numeric($balanceDue)) {
                        throw new \Exception("Invalid balance due format: {$column[7]}");
                    }

                    // Check if invoice already exists
                    $exists = Invoice::find()->where(['invoice_number' => $column[1]])->exists();

                    if ($exists) {
                        $this->stdout("Invoice {$column[1]} already exists, skipping...\n", Console::FG_YELLOW);
                        continue;
                    }

                    $db = \Yii::$app->db;
                    $transaction = $db->beginTransaction();

                    $customer = Customer::find()->where(['id' => $column[3]])->one();
                    if(!$customer){
                        $customer = new Customer();
                        if($column[3] == 1) {
                            $customer->id = 1;
                            $customer->company_name = 'Creditstar International';
                            $customer->contact_person = 'Irina Zozulja';
                            $customer->email = 'invoices@creditstar.com';
                            $customer->phone = '+372 698 8710';
                            $customer->address = 'Kai 4';
                            $customer->city = 'Tallinn';
                            $customer->postal_code = '10111';
                            $customer->country = 'Estonia';
                            $customer->tax_number = 'EE102901648';
                            $customer->website = 'https://creditstar.com';
                            $customer->notes = 'Main corporate customer';
                            $customer->default_currency = 'EUR';
                            $customer->status = Customer::STATUS_ACTIVE;
                            $customer->created_at = $date->getTimestamp();
                            $customer->updated_at = $date->getTimestamp();
                            $customer->created_by = 1;
                            $customer->updated_by = 1;
                        }else{
                            $customer->company_name = 'Mr. Ahsan Butt';
                            $customer->contact_person = 'Mr. Ahsan Butt';
                            $customer->email = 'ahsan@keycodesyntax.com';
                            $customer->website = 'https://keycodesyntax.com';
                            $customer->default_currency = 'USD';
                            $customer->status = Customer::STATUS_ACTIVE;
                            $customer->created_at = $date->getTimestamp();
                            $customer->updated_at = $date->getTimestamp();
                            $customer->created_by = 1;
                            $customer->updated_by = 1;
                        }

                        $customer->save();
                    }

                    $invoice = new Invoice();
                    $invoice->invoice_date = $date->format('Y-m-d');
                    $invoice->invoice_number = $column[1];
                    $invoice->customer_id = $customer->id;
                    $invoice->status = Invoice::STATUS_PENDING;
                    $invoice->due_date = $dueDate->format('Y-m-d');
                    $invoice->total_amount = $amount;
                    $invoice->subtotal = $amount;
                    $invoice->tax_amount = 0;
                    $invoice->discount = 0;
                    $invoice->created_at = $date->getTimestamp();
                    $invoice->updated_at = $date->getTimestamp();
                    $invoice->created_by = 1;
                    $invoice->updated_by = 1;
                    $invoice->currency_code = $invoice->customer_id == 1 ? 'EUR' : 'USD';
                    $invoice->exchange_rate = $invoice->currency_code == 'EUR' ? 320.00 : 300.00;
                    $invoice->total_amount_lkr = $amount * $invoice->exchange_rate;
                    $invoice->payment_date = $date->format('Y-m-d');
                    $invoice->payment_term_id = 1;

                    if(!$invoice->save()) {
                        $errors = implode(', ', array_map(function($errs) {
                            return implode('; ', $errs);
                        }, $invoice->getErrors()));
                        throw new \Exception("Validation failed: {$errors}");
                    }

                    $invoiceId = $db->getLastInsertID();

                    $startDayOfLastMonth = (clone $date)->modify('-1 month');
                    $lastDayOfLastMonth = (clone $startDayOfLastMonth)->modify('last day of this month');

                    $invoiceItem = new InvoiceItem();
                    $invoiceItem->invoice_id = $invoiceId;
                    $invoiceItem->item_name = 'Software Engineering Services';
                    $invoiceItem->description = 'From '.$startDayOfLastMonth->format('Y-m-d').' to '.$lastDayOfLastMonth->format('Y-m-d');
                    $invoiceItem->unit_price = $amount;
                    $invoiceItem->tax_rate = 0;
                    $invoiceItem->tax_amount = 0;
                    $invoiceItem->discount = 0;
                    $invoiceItem->total_amount = $amount;
                    $invoiceItem->created_at = $date->getTimestamp();
                    $invoiceItem->updated_at = $date->getTimestamp();
                    $invoiceItem->quantity = 1;
                    $invoiceItem->created_by = 1;
                    $invoiceItem->updated_by = 1;
                    if(!$invoiceItem->save()) {
                        $errors = implode(', ', array_map(function($errs) {
                            return implode('; ', $errs);
                        }, $invoiceItem->getErrors()));
                        throw new \Exception("Validation failed: {$errors}");
                    }

                    $transaction->commit();

                    $imported++;
                    $this->stdout("Successfully imported invoice {$column[1]}\n", Console::FG_GREEN);

                } catch (\Exception $e) {
                    $transaction->rollBack();
                    $this->stderr("\nError importing invoices. All changes have been rolled back.\n", Console::FG_RED);
                    $this->stderr("Error message: " . $e->getMessage() . "\n", Console::FG_RED);
                    $this->stderr("Error processing row " . ($index + 1) . ": " . $e->getMessage() . "\n", Console::FG_RED);
                    throw $e;
                }
            }

            $this->stdout("\nImport completed successfully!\n", Console::FG_GREEN);
            $this->stdout("Total invoices imported: {$imported}\n", Console::FG_GREEN);

        } catch (\Exception $e) {

            return 1;
        }

        return 0;
    }
}
