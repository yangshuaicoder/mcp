<?php

$env = getenv('APP_ENV') ?: 'prod';

if ($env === 'test') {
    $dsn      = 'mysql:host=127.0.0.1;port=3306;dbname=mcp_test';
    $username = 'root';
    $password = 'TestMysql@2026';
} else {
    $dsn      = 'mysql:host=10.20.240.84;port=3306;dbname=mcp';
    $username = 'root';
    $password = 'matchu001';
}

return [
    'class'               => 'yii\db\Connection',
    'dsn'                 => $dsn,
    'username'            => $username,
    'password'            => $password,
    'charset'             => 'utf8mb4',
    'tablePrefix'         => '',
    'enableSchemaCache'   => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
    'schemaCache'         => 'cache',
];
