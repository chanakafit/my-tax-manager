<?php

use app\helpers\Params;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Invoice */
/* @var $emailForm app\models\forms\InvoiceEmailForm */

$this->title = 'Send Invoice #' . $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoice_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Send Email';

// Get email suggestions for the customer
$emailSuggestions = $emailForm->getEmailSuggestions($model->customer_id);
$toEmails = ArrayHelper::map(
    array_filter($emailSuggestions, fn($e) => $e['type'] === 'to'),
    'email',
    'email'
);
$ccEmails = ArrayHelper::map(
    array_filter($emailSuggestions, fn($e) => $e['type'] === 'cc'),
    'email',
    'email'
);
$bccEmails = ArrayHelper::map(
    array_filter($emailSuggestions, fn($e) => $e['type'] === 'bcc'),
    'email',
    'email'
);
?>

<div class="invoice-email-form">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <?php $form = \app\widgets\BActiveForm::begin(); ?>

                    <?= $form->field($emailForm, 'to')->widget(Select2::classname(), [
                        'options' => [
                            'placeholder' => 'Enter email addresses...',
                            'multiple' => true
                        ],
                        'data' => $toEmails,
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'maximumInputLength' => 254,
                            'validateOnBlur' => true
                        ]
                    ]) ?>

                    <?= $form->field($emailForm, 'cc')->widget(Select2::classname(), [
                        'options' => [
                            'placeholder' => 'Enter CC email addresses...',
                            'multiple' => true
                        ],
                        'data' => $ccEmails,
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'maximumInputLength' => 254,
                            'validateOnBlur' => true
                        ]
                    ]) ?>

                    <?= $form->field($emailForm, 'bcc')->widget(Select2::classname(), [
                        'options' => [
                            'placeholder' => 'Enter BCC email addresses...',
                            'multiple' => true
                        ],
                        'data' => $bccEmails,
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'maximumInputLength' => 254,
                            'validateOnBlur' => true
                        ]
                    ]) ?>

                    <?= $form->field($emailForm, 'subject')->textInput([
                        'value' => "Invoice #{$model->invoice_number} from " . Params::get('businessName')
                    ]) ?>

                    <?= $form->field($emailForm, 'additionalNotes')->textarea([
                        'rows' => 6,
                        'placeholder' => 'Add any additional notes or message to include in the email'
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Send Email', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Cancel', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                    </div>

                    <?php \app\widgets\BActiveForm::end(); ?>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Invoice Summary</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Invoice Number:</th>
                                    <td><?= Html::encode($model->invoice_number) ?></td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td><?= Html::encode($model->customer->company_name) ?></td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td><?= Yii::$app->formatter->asCurrency($model->total_amount, $model->currency_code) ?></td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td><?= Yii::$app->formatter->asDate($model->due_date) ?></td>
                                </tr>
                            </table>

                            <div class="alert alert-info mt-3">
                                <i class="fa fa-info-circle"></i> The invoice will be automatically attached as a PDF.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
