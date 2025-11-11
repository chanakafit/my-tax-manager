<?php
// Database configuration template
// Copy this file to db-local.php and fill in your credentials
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'dbname' => getenv('DB_NAME') ?: 'your_database_name',
    'username' => getenv('DB_USER') ?: 'your_db_user',
    'password' => getenv('DB_PASSWD') ?: 'your_db_password',
    'tablePrefix' => getenv('DB_PREFIX') ?: 'mb_'
];

