<?php
$db = require __DIR__ . '/db.php';
// Dedicated test database to prevent unit test fixtures from truncating application data.
$host = getenv('MYSQL_HOST') ?: 'mariadb';
$port = getenv('MYSQL_PORT') ?: '3306';
$testDbName = getenv('MYSQL_TEST_DATABASE') ?: 'mybs_test';

$db['dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $testDbName);

return $db;
