<?php

use app\helpers\Params;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $invoice app\models\Invoice */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { margin-bottom: 20px; }
        .amount { font-size: 24px; font-weight: bold; color: #1a365d; margin: 20px 0; }
        .details { background: #f7fafc; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .details div { margin: 5px 0; }
        .view-button { 
            display: inline-block; 
            padding: 10px 20px; 
            background-color: #1a365d; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            margin: 20px 0; 
        }
        .signature { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Dear <?= Html::encode($invoice->customer->company_name) ?>,
        </div>

        <p>Please find the Invoice attached herewith. Your invoice can be viewed, printed and downloaded as PDF from the link below.</p>

        <div class="amount">
            INVOICE AMOUNT<br>
            <?= Yii::$app->formatter->asCurrency($invoice->total_amount, $invoice->currency_code) ?>
        </div>

        <div class="details">
            <div>Invoice No: <?= Html::encode($invoice->invoice_number) ?></div>
            <div>Invoice Date: <?= Yii::$app->formatter->asDate($invoice->invoice_date, 'php:M d, Y') ?></div>
            <div>Due Date: <?= Yii::$app->formatter->asDate($invoice->due_date, 'php:M d, Y') ?></div>
        </div>

        <?= Html::a('VIEW INVOICE', $publicUrl, ['class' => 'view-button']) ?>

        <?php if (isset($additionalNotes) && !empty($additionalNotes)): ?>
        <div class="additional-notes">
            <p><?= nl2br(Html::encode($additionalNotes)) ?></p>
        </div>
        <?php endif; ?>

        <div class="signature">
            Regards,<br>
            <?= Html::encode(Params::get('signature.name')) ?><br>
            <?= Html::encode(Params::get('businessName')) ?>
        </div>
    </div>
</body>
</html>
