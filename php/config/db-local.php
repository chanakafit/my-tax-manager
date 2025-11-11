<?php
// Auto-generated database configuration
return [
    'host' => getenv('DB_HOST') ?: 'mariadb',
    'port' => getenv('DB_PORT') ?: '3306',
    'dbname' => getenv('DB_NAME') ?: 'mybs',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWD') ?: 'mauFJcuf5dhRMQrjj',
    'tablePrefix' => getenv('DB_PREFIX') ?: 'mb_'
];
