<?php
use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;
use kartik\date\DatePicker;

$this->title = 'Dispose Asset: ' . $model->asset_name;
$this->params['breadcrumbs'][] = ['label' => 'Capital Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->asset_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Dispose';
?>

<div class="capital-asset-dispose">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'disposal_date')->widget(DatePicker::class, [
                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>

            <?= $form->field($model, 'disposal_value')->textInput(['type' => 'number', 'step' => '0.01']) ?>

            <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

            <div class="alert alert-info">
                <strong>Current Written Down Value:</strong> <?= Yii::$app->formatter->asCurrency($model->current_written_down_value) ?>
            </div>

            <div class="form-group">
                <div class="d-flex justify-content-end">
                    <?= Html::a('Cancel', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary me-2']) ?>
                    <?= Html::submitButton('Dispose Asset', ['class' => 'btn btn-danger']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
