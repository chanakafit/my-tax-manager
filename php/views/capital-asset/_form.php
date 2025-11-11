<?php
use yii\helpers\Html;
use app\widgets\BActiveForm as ActiveForm;
use kartik\date\DatePicker;
?>

<div class="capital-asset-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Asset Details</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'asset_name')->textInput(['maxlength' => true]) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'asset_type')->dropDownList([
                                'business' => 'Business',
                                'personal' => 'Personal',
                            ], ['prompt' => 'Select Type']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'asset_category')->dropDownList([
                                'immovable' => 'Immovable Property',
                                'movable' => 'Movable Property',
                            ], ['prompt' => 'Select Category']) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

                    <?= $form->field($model, 'purchase_date')->widget(DatePicker::class, [
                        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ]
                    ]) ?>

                    <?= $form->field($model, 'purchase_cost')->textInput(['type' => 'number', 'step' => '0.01']) ?>

                    <?= $form->field($model, 'initial_tax_year')->dropDownList(
                        array_combine(range(date('Y')-3, date('Y')), range(date('Y')-3, date('Y'))),
                        ['prompt' => 'Select Tax Year']
                    ) ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'uploadedFile')->fileInput(['class' => 'form-control']) ?>

                    <?= $form->field($model, 'notes')->textarea(['rows' => 4]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-4">
        <div class="d-flex justify-content-end">
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
