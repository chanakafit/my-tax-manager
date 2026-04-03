<?php

$appEnv = getenv('APP_ENV') ?: 'dev';
$yiiDebug = getenv('YII_DEBUG');
defined('YII_DEBUG') or define('YII_DEBUG', $yiiDebug === 'true' || ($yiiDebug !== 'false' && $appEnv !== 'prod'));
defined('YII_ENV') or define('YII_ENV', $appEnv);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
