<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SystemConfig $signatureImage */
/** @var app\models\SystemConfig $signatureName */
/** @var app\models\SystemConfig $signatureTitle */

$this->title = 'Signature Settings';
$this->params['breadcrumbs'][] = ['label' => 'System Configuration', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="signature-settings">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Back to Config', ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('<i class="fas fa-list"></i> Bulk Update', ['bulk-update'], ['class' => 'btn btn-info']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-signature"></i> Signature Configuration</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'options' => ['enctype' => 'multipart/form-data'],
                    ]); ?>

                    <div class="form-group">
                        <label for="signature_name">Signature Name</label>
                        <input type="text"
                               class="form-control"
                               id="signature_name"
                               name="signature_name"
                               value="<?= Html::encode($signatureName->config_value) ?>"
                               placeholder="e.g., John Smith">
                        <small class="form-text text-muted">The name to display below the signature</small>
                    </div>

                    <div class="form-group">
                        <label for="signature_title">Signature Title/Position</label>
                        <input type="text"
                               class="form-control"
                               id="signature_title"
                               name="signature_title"
                               value="<?= Html::encode($signatureTitle->config_value) ?>"
                               placeholder="e.g., Managing Director, CEO">
                        <small class="form-text text-muted">The title/position to display below the name</small>
                    </div>

                    <div class="form-group">
                        <label for="signature_image_file">Signature Image</label>
                        <input type="file"
                               class="form-control"
                               id="signature_image_file"
                               name="signature_image_file"
                               accept="image/png,image/jpeg,image/jpg">
                        <small class="form-text text-muted">Upload a signature image (PNG, JPG, JPEG). Recommended size: 200x80 pixels, transparent background preferred.</small>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Save Signature Settings', ['class' => 'btn btn-success btn-lg']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-eye"></i> Current Signature</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($signatureImage->config_value): ?>
                        <div class="mb-3">
                            <?= Html::img($signatureImage->config_value, [
                                'alt' => 'Current Signature',
                                'class' => 'img-fluid border p-2',
                                'style' => 'max-width: 100%; max-height: 150px; background-color: #f8f9fa;'
                            ]) ?>
                        </div>
                        <p class="mb-1"><strong><?= Html::encode($signatureName->config_value) ?></strong></p>
                        <p class="text-muted mb-0"><small><?= Html::encode($signatureTitle->config_value) ?></small></p>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No signature image uploaded yet.
                        </div>
                        <p class="text-muted">Upload a signature image to see the preview here.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Use a transparent PNG for best results</li>
                        <li>Recommended size: 200x80 pixels</li>
                        <li>Keep file size under 500KB</li>
                        <li>Use a clear, professional signature</li>
                        <li>This signature will appear on invoices and documents</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$this->registerCss(<<<CSS
.signature-settings .card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.signature-settings .form-group {
    margin-bottom: 1.5rem;
}
CSS
);
?>

