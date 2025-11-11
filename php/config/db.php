<?php

$dbLocal = require(__DIR__ . '/db-local.php');

return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        $dbLocal['host'],
        $dbLocal['port'],
        $dbLocal['dbname']
    ),
    'username' => $dbLocal['username'],
    'password' => $dbLocal['password'],
    'charset' => 'utf8',
    'tablePrefix' => $dbLocal['tablePrefix']
];
