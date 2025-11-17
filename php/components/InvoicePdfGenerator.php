<?php

namespace app\components;

use app\helpers\ConfigHelper;
use Yii;
use app\models\Invoice;
use yii\base\Component;
use kartik\mpdf\Pdf;

class InvoicePdfGenerator extends Component
{
    public function generatePdf(Invoice $invoice, $returnContent = false)
    {
        $content = $this->getInvoiceHtml($invoice);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => $returnContent ? Pdf::DEST_STRING : Pdf::DEST_BROWSER,
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '
                body { 
                    font-family: "Helvetica", "Arial", sans-serif; 
                    color: #2d3748; 
                    line-height: 1.4;
                    font-size: 9pt;
                }
                table { width: 100%; }
                .header-table { margin-bottom: 15px; }
                .header-table td { vertical-align: top; }
                .text-right { text-align: right; }
                .org-name { 
                    font-size: 18pt; 
                    font-weight: bold; 
                    color: #1a365d; 
                    margin-bottom: 5px; 
                }
                .org-details { 
                    color: #4a5568; 
                    font-size: 9pt; 
                    line-height: 1.3; 
                }
                .entity-title { 
                    font-size: 24pt; 
                    color: #1a365d; 
                    margin-bottom: 2px; 
                    letter-spacing: 1px; 
                }
                .invoice-number { 
                    color: #4a5568; 
                    font-size: 10pt; 
                    margin-bottom: 10px; 
                }
                .balance-section { 
                    background: #f7fafc; 
                    padding: 8px; 
                    border-radius: 3px; 
                }
                .balance-label { 
                    font-size: 8pt; 
                    color: #4a5568; 
                    text-transform: uppercase; 
                    letter-spacing: 1px; 
                }
                .balance-due { 
                    font-size: 14pt; 
                    font-weight: bold; 
                    color: #2d3748; 
                }
                .bill-to-section { 
                    background: #f7fafc; 
                    padding: 10px; 
                    border-radius: 3px; 
                    margin-bottom: 15px; 
                }
                .bill-to-label { 
                    color: #4a5568; 
                    font-size: 9pt; 
                    text-transform: uppercase; 
                    letter-spacing: 1px; 
                    margin-bottom: 5px; 
                }
                .customer-details { 
                    color: #2d3748; 
                    line-height: 1.4; 
                }
                .item-table { 
                    margin: 15px 0; 
                    border-collapse: collapse; 
                }
                .item-table th { 
                    background-color: #1a365d; 
                    color: white; 
                    padding: 8px; 
                    text-align: left;
                    font-weight: normal;
                    font-size: 9pt;
                    letter-spacing: 0.5px;
                }
                .item-table td { 
                    padding: 6px 8px; 
                    border-bottom: 1px solid #e2e8f0; 
                    color: #4a5568;
                }
                .item-name { 
                    font-weight: bold; 
                    color: #2d3748; 
                    margin-bottom: 2px; 
                }
                .item-description { 
                    color: #718096; 
                    font-size: 8pt; 
                }
                .totals-table { 
                    width: 300px; 
                    margin-left: auto; 
                    margin-top: 15px;
                    background: #f7fafc;
                    padding: 8px;
                    border-radius: 3px;
                }
                .totals-table td { 
                    padding: 4px; 
                    color: #4a5568; 
                }
                .totals-table .total-row td { 
                    font-weight: bold; 
                    font-size: 10pt; 
                    color: #2d3748;
                    border-top: 1px solid #e2e8f0;
                    padding-top: 8px;
                }
                .total-in-words {
                    margin: 15px 0;
                    padding: 8px;
                    background: #f7fafc;
                    border-radius: 3px;
                    color: #4a5568;
                    font-style: italic;
                    font-size: 8pt;
                }
                .banking-details { 
                    margin-top: 15px;
                    padding: 10px;
                    background: #f7fafc;
                    border-radius: 3px;
                    font-size: 8pt;
                    line-height: 1.4;
                }
                .banking-details .section-title {
                    color: #1a365d;
                    font-size: 9pt;
                    font-weight: bold;
                    margin-bottom: 5px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .banking-details .detail-label {
                    color: #4a5568;
                    font-weight: normal;
                }
                .banking-details .detail-value {
                    color: #2d3748;
                    font-weight: bold;
                }
                .notes-section {
                    margin: 15px 0;
                    padding: 8px;
                    background: #f7fafc;
                    border-radius: 3px;
                    font-size: 8pt;
                }
                .notes-section .section-title {
                    color: #1a365d;
                    font-size: 9pt;
                    margin-bottom: 5px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .signature-name {
                    margin-top: 5px;
                    color: #2d3748;
                    font-size: 10pt;
                    font-weight: bold;
                }
                .signature-section {
                    margin-top: 15px;
                    border-top: 1px solid #e2e8f0;
                    padding-top: 15px;
                }
                .signature-line {
                    border-bottom: 1px solid #718096;
                    height: 30px;
                }
                .signature-label {
                    margin-top: 3px;
                    color: #4a5568;
                    font-size: 8pt;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
            ',
            'options' => [
                'title' => 'Invoice #' . $invoice->invoice_number,
                'subject' => 'Invoice #' . $invoice->invoice_number,
                'keywords' => 'invoice, business, payment'
            ],
            'methods' => [
                'SetHeader' => false,
                'SetFooter' => false,
                'SetMargins' => [10, 10, 10], // Left, Top, Right margins in mm
            ]
        ]);

        return $pdf->render();
    }

    private function getInvoiceHtml($invoice)
    {
        $customer = $invoice->customer;
        $bankingDetails = ConfigHelper::getBankingDetails();
        $businessAddress = ConfigHelper::getBusinessAddress();

        $html = '
        <table class="header-table">
            <tr>
                <td width="50%">
                    <div class="org-name">' . ConfigHelper::getBusinessName() . '</div>
                    <div class="org-details">
                        ' . nl2br($businessAddress['line1']) . '<br>
                        ' . nl2br($businessAddress['line2']) . '<br>
                        ' . $businessAddress['city'] . ' ' . $businessAddress['postalCode'] . '<br>
                        ' . $businessAddress['province'] . '<br>
                        SriLanka
                    </div>
                </td>
                <td width="50%" class="text-right">
                    <div class="entity-title">INVOICE</div>
                    <div class="invoice-number">#' . $invoice->invoice_number . '</div>
                    <div class="balance-section">
                        <div class="balance-label">Balance Due</div>
                        <div class="balance-due">' . Yii::$app->formatter->asCurrency($invoice->total_amount, $invoice->currency_code) . '</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="bill-to-section">
            <div class="bill-to-label">Bill To</div>
            <div class="customer-details">
                <strong>' . $customer->company_name . '</strong><br>
                ' . nl2br($customer->address ?? '') . '<br>
                ' . $customer->city . ($customer->state ? ', ' . $customer->state : '') . ' ' . $customer->postal_code . '<br>
                ' . $customer->country . '
            </div>
        </div>

        <table width="100%">
            <tr>
                <td width="50%">&nbsp;</td>
                <td width="50%" class="text-right">
                    <table width="100%">
                        <tr>
                            <td class="text-right" style="color: #4a5568;">Invoice Date:</td>
                            <td class="text-right" style="color: #2d3748; font-weight: bold;">' .
                                Yii::$app->formatter->asDate($invoice->invoice_date, 'php:M d, Y') .
                            '</td>
                        </tr>
                        <tr>
                            <td class="text-right" style="color: #4a5568;">Due Date:</td>
                            <td class="text-right" style="color: #2d3748; font-weight: bold;">' .
                                Yii::$app->formatter->asDate($invoice->due_date, 'php:M d, Y') .
                            '</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="item-table">
            <thead>
                <tr>
                    <th width="5%" style="text-align: center;">#</th>
                    <th width="80%" style="padding-left: 20px;">Item & Description</th>
                    <th width="15%" style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>';

        $i = 1;
        foreach ($invoice->invoiceItems as $item) {
            $html .= '
                <tr>
                    <td style="text-align: center;">' . $i++ . '</td>
                    <td style="padding-left: 20px;">
                        <div class="item-name">' . $item->item_name . '</div>
                        <div class="item-description">' . $item->description . '</div>
                    </td>
                    <td style="text-align: right;">' .
                        Yii::$app->formatter->asCurrency($item->total_amount, $invoice->currency_code) .
                    '</td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>Sub Total</td>
                <td class="text-right" width="150">' .
                    Yii::$app->formatter->asCurrency($invoice->subtotal, $invoice->currency_code) .
                '</td>
            </tr>';

        if ($invoice->tax_amount > 0) {
            $html .= '
            <tr>
                <td>Tax Amount</td>
                <td class="text-right">' .
                    Yii::$app->formatter->asCurrency($invoice->tax_amount, $invoice->currency_code) .
                '</td>
            </tr>';
        }

        if ($invoice->discount > 0) {
            $html .= '
            <tr>
                <td>Discount</td>
                <td class="text-right">' .
                    Yii::$app->formatter->asCurrency($invoice->discount, $invoice->currency_code) .
                '</td>
            </tr>';
        }

        $html .= '
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">' .
                    Yii::$app->formatter->asCurrency($invoice->total_amount, $invoice->currency_code) .
                '</td>
            </tr>
            <tr class="total-row">
                <td>Balance Due</td>
                <td class="text-right">' .
                    Yii::$app->formatter->asCurrency($invoice->total_amount, $invoice->currency_code) .
                '</td>
            </tr>
        </table>

        <div class="total-in-words">
            Amount in words: <strong>' . ConfigHelper::getCurrencies()[$invoice->currency_code] . ' ' . $this->numberToWords($invoice->total_amount) . '</strong>
        </div>

        <div class="notes-section">
            <div class="section-title">Notes</div>
            <p>Thanks for your business.</p>
        </div>

        <div class="banking-details">
            <div class="section-title">Banking Details</div>
            <span class="detail-label">SWIFT CODE:</span> 
            <span class="detail-value">' . $bankingDetails['swiftCode'] . '</span><br>
            
            <span class="detail-label">BANK/BRANCH NAME:</span> 
            <span class="detail-value">' . $bankingDetails['bankName'] . ', ' . $bankingDetails['branchName'] . '</span><br>
            
            <span class="detail-label">BANK CODE:</span> 
            <span class="detail-value">' . $bankingDetails['bankCode'] . '</span><br>
            
            <span class="detail-label">BRANCH CODE:</span> 
            <span class="detail-value">' . $bankingDetails['branchCode'] . '</span><br>
            
            <span class="detail-label">BANK ADDRESS:</span> 
            <span class="detail-value">' . $bankingDetails['bankAddress'] . '</span><br>
            
            <span class="detail-label">ACCOUNT NAME:</span> 
            <span class="detail-value">' . $bankingDetails['accountName'] . '</span><br>
            
            <span class="detail-label">ACCOUNT NUMBER:</span> 
            <span class="detail-value">' . $bankingDetails['accountNumber'] . '</span>
        </div>

        <div class="signature-section">
            <table width="100%">
                <tr>
                    <td width="240" style="text-align: left;">';

        $signatureImage = ConfigHelper::getSignatureImage();
        if ($signatureImage) {
            $html .= '<img src="' . Yii::getAlias('@app/web') . $signatureImage . '" style="height: 50px; margin-bottom: 5px;"><br>';
        }

        $html .= '
                        <div style="border-bottom: 1px solid #718096;"></div>
                        <div class="signature-name">' . ConfigHelper::getSignatureName() . '</div>
                        <div class="signature-label">' . ConfigHelper::getSignatureTitle() . '</div>
                    </td>
                    <td>&nbsp;</td>
                    <td width="240" class="text-right">
                        <div style="height: 50px;"></div>
                        <div class="signature-line"></div>
                    </td>
                </tr>
            </table>
        </div>';

        return $html;
    }

    private function numberToWords($number)
    {
        $number = number_format($number, 2, '.', '');
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
            6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
            11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen',
            15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
            19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'forty',
            50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty',
            90 => 'ninety'
        );
        $digits = array('', 'hundred', 'thousand', 'million', 'billion');

        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;

            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred :
                    $words[floor($number / 10) * 10] .
                    " " . $words[$number % 10] . " " .
                    $digits[$counter] . $plural . " " . $hundred;
            } else {
                $str[] = null;
            }
        }

        $str = array_reverse($str);
        $result = implode('', $str);

        if ($decimal > 0) {
            $result .= ' and ';
            $words_decimal = array(
                'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'
            );
            $decimal_str = '';
            $decimal_first = floor($decimal / 10);
            $decimal_second = $decimal % 10;

            if ($decimal < 20) {
                $decimal_str = $words[$decimal];
            } else {
                $decimal_str = $words[$decimal_first * 10];
                if ($decimal_second > 0) {
                    $decimal_str .= '-' . $words_decimal[$decimal_second];
                }
            }
            $result .= $decimal_str . ' cents';
        }

        return ucwords($result);
    }
}
