<?php

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

// 加载服务器环境配置（不进 git，存放于 /data/apps/mcp/config/.env）
$envFile = dirname(__DIR__, 3) . '/config/.env';
if (file_exists($envFile)) {
    foreach (parse_ini_file($envFile) as $key => $value) {
        putenv("$key=$value");
    }
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
