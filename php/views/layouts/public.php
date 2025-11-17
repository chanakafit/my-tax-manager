<?php

use app\helpers\Params;
use yii\helpers\Html;
use yii\bootstrap5\BootstrapAsset;

/* @var $this \yii\web\View */
/* @var $content string */

BootstrapAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .business-header {
            text-align: center;
            margin-bottom: 30px;
            color: #1a365d;
        }
        .business-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .business-info {
            color: #4a5568;
            font-size: 14px;
        }
        .content-wrapper {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="main-container">
    <div class="business-header">
        <div class="business-name"><?= Html::encode(Params::get('businessName')) ?></div>
        <div class="business-info">
            <?= nl2br(Html::encode(Params::get('businessAddressLine1'))) ?><br>
            <?= nl2br(Html::encode(Params::get('businessAddressLine2'))) ?><br>
            <?= Html::encode(Params::get('businessAddressCity')) ?>
            <?= Html::encode(Params::get('businessAddressPostalCode')) ?><br>
            <?= Html::encode(Params::get('businessAddressProvince')) ?>
        </div>
    </div>

    <div class="content-wrapper">
        <?= $content ?>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
