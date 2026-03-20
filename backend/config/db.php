<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . (getenv('DB_HOST') ?: '10.20.240.84') . ';port=3306;dbname=' . (getenv('DB_NAME') ?: 'mcp'),
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: 'matchu001',
    'charset' => 'utf8mb4',
    'tablePrefix' => '',
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
