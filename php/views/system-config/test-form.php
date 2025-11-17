<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Test Config Form';
?>

<div class="test-config-form">
    <h1>Test System Config Form Submission</h1>

    <form method="post" action="<?= \yii\helpers\Url::to(['system-config/bulk-update']) ?>">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <div class="form-group">
            <label>Business Name (ID: 1)</label>
            <input type="text" name="SystemConfig[1]" value="Test Value" class="form-control" />
        </div>

        <button type="submit" class="btn btn-primary">Submit Test</button>
    </form>

    <hr>

    <h3>Debug Info</h3>
    <ul>
        <li>CSRF Param: <?= Yii::$app->request->csrfParam ?></li>
        <li>CSRF Token: <?= substr(Yii::$app->request->csrfToken, 0, 20) ?>...</li>
        <li>Form Action: <?= \yii\helpers\Url::to(['system-config/bulk-update']) ?></li>
    </ul>
</div>

