<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SystemConfig[] $configs */
/** @var array $categories */
/** @var string|null $selectedCategory */

$this->title = 'System Configuration';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="system-config-bulk-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-sync"></i> Clear Cache', ['clear-cache'], [
                'class' => 'btn btn-warning',
                'data' => [
                    'confirm' => 'Are you sure you want to clear the configuration cache?',
                    'method' => 'post',
                ],
            ]) ?>
            <?= Html::a('<i class="fas fa-list"></i> View All', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="form-inline">
                <label for="system-config-category" class="mr-2"><strong>Filter by Category:</strong></label>
                <select id="system-config-category" name="category" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= Html::encode($category) ?>" <?= $selectedCategory === $category ? 'selected' : '' ?>>
                            <?= Html::encode(ucwords(str_replace('_', ' ', $category))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if (empty($configs)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No configurations found.
        </div>
    <?php else: ?>
        <?php $form = ActiveForm::begin(); ?>

        <?php
        $currentCategory = null;
        foreach ($configs as $config):
            // Group by category
            if ($currentCategory !== $config->category):
                if ($currentCategory !== null):
                    // Close previous category's table and wrapper divs properly
                    echo '</tbody></table></div></div></div>'; // Close previous card (table + wrappers)
                endif;
                $currentCategory = $config->category;
                ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cog"></i> <?= Html::encode(ucwords(str_replace('_', ' ', $currentCategory))) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 30%">Configuration</th>
                                        <th style="width: 50%">Value</th>
                                        <th style="width: 10%">Type</th>
                                        <th style="width: 10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
            <?php endif; ?>

                                    <tr>
                                        <td>
                                            <strong><?= Html::encode($config->config_key) ?></strong>
                                            <?php if ($config->description): ?>
                                                <br><small class="text-muted"><?= Html::encode($config->description) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($config->is_editable): ?>
                                                <?php if ($config->config_type === 'boolean'): ?>
                                                    <?= Html::checkbox("SystemConfig[{$config->id}]", (bool)$config->config_value, [
                                                        'value' => '1',
                                                        'uncheck' => '0',
                                                        'class' => 'form-check-input',
                                                    ]) ?>
                                                <?php elseif ($config->config_type === 'json' || $config->config_type === 'array'): ?>
                                                    <?= Html::textarea("SystemConfig[{$config->id}]", $config->config_value, [
                                                        'class' => 'form-control',
                                                        'rows' => 3,
                                                    ]) ?>
                                                <?php else: ?>
                                                    <?= Html::textInput("SystemConfig[{$config->id}]", $config->config_value, [
                                                        'class' => 'form-control',
                                                        'type' => $config->config_type === 'integer' ? 'number' : 'text',
                                                    ]) ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <code><?= Html::encode($config->config_value) ?></code>
                                                <br><small class="text-muted">Not editable</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?= Html::encode($config->config_type) ?></span>
                                        </td>
                                        <td>
                                            <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $config->id], [
                                                'class' => 'btn btn-sm btn-info',
                                                'title' => 'View',
                                            ]) ?>
                                        </td>
                                    </tr>

        <?php endforeach; ?>

        <?php if ($currentCategory !== null): ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
        <?php endif; ?>

        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-save"></i> Save All Changes', ['class' => 'btn btn-success btn-lg']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    <?php endif; ?>

</div>

<?php
$this->registerCss(<<<CSS
.table td {
    vertical-align: middle;
}
CSS
);
?>
