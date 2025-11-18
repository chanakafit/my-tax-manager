<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\TaxReturnSupportDocument */
/* @var $year string */
/* @var $snapshot app\models\TaxYearSnapshot */

$this->title = 'Upload Support Document';
$this->params['breadcrumbs'][] = ['label' => 'Tax Returns', 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => 'Tax Year ' . $year, 'url' => ['view-report', 'year' => $year]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tax-return-support-document-upload">

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h1 class="h4 mb-0">
                <i class="fas fa-file-upload"></i> <?= Html::encode($this->title) ?>
                <small class="float-right">Tax Year: <?= Html::encode($year) ?></small>
            </h1>
        </div>

        <div class="card-body">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

            <?= $form->field($model, 'document_title')->textInput([
                'maxlength' => true,
                'placeholder' => 'e.g., Additional Income Statement, Property Documents, etc.'
            ]) ?>

            <?= $form->field($model, 'document_description')->textarea([
                'rows' => 3,
                'placeholder' => 'Optional description of the document'
            ]) ?>

            <?= $form->field($model, 'uploadedFile')->widget(FileInput::class, [
                'options' => ['accept' => '.pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx'],
                'pluginOptions' => [
                    'showPreview' => true,
                    'showCaption' => true,
                    'showRemove' => true,
                    'showUpload' => false,
                    'showCancel' => false,
                    'browseClass' => 'btn btn-primary',
                    'browseIcon' => '<i class="fas fa-folder-open"></i> ',
                    'browseLabel' =>  'Browse',
                    'allowedFileExtensions' => ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx', 'xls', 'xlsx'],
                    'maxFileSize' => 10240, // 10 MB
                    'msgSizeTooLarge' => 'File "{name}" ({size} KB) exceeds maximum allowed upload size of 10 MB.',
                ]
            ]) ?>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Supported File Types:</strong> PDF, PNG, JPG, JPEG, DOC, DOCX, XLS, XLSX<br>
                <strong>Maximum File Size:</strong> 10 MB
            </div>

            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-upload"></i> Upload Document', [
                    'class' => 'btn btn-success'
                ]) ?>
                <?= Html::a('<i class="fas fa-times"></i> Cancel', ['view-report', 'year' => $year], [
                    'class' => 'btn btn-secondary'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>

