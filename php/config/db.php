<?php

$dbLocal = file_exists(__DIR__ . '/db-local.php')
    ? require(__DIR__ . '/db-local.php')
    : [
        'host'        => getenv('DB_HOST') ?: 'localhost',
        'port'        => getenv('DB_PORT') ?: '3306',
        'dbname'      => getenv('DB_NAME') ?: 'mybs',
        'username'    => getenv('DB_USER') ?: 'root',
        'password'    => getenv('DB_PASSWD') ?: '',
        'tablePrefix' => getenv('DB_PREFIX') ?: '',
    ];

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
