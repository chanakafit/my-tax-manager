<?php

$dbLocal = file_exists(__DIR__ . '/db-local.php')
    ? require(__DIR__ . '/db-local.php')
    : [
        'host'        => getenv('MYSQL_HOST') ?: 'localhost',
        'port'        => getenv('MYSQL_PORT') ?: '3306',
        'dbname'      => getenv('MYSQL_DATABASE') ?: 'mybs',
        'username'    => getenv('MYSQL_USER') ?: 'root',
        'password'    => getenv('MYSQL_PASSWORD') ?: '',
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
