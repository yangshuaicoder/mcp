<?php

/**
 * 数据库配置加载器
 * 根据服务器环境变量 APP_ENV 加载对应环境的配置文件
 * APP_ENV=dev  -> config/dev/db.php
 * APP_ENV=prod -> config/prod/db.php（默认）
 */

$env = getenv('APP_ENV') ?: 'prod';
$envDir = __DIR__ . '/' . $env;

if (!is_dir($envDir)) {
    throw new \RuntimeException("配置目录不存在: config/{$env}/，请检查 APP_ENV 环境变量");
}

return require $envDir . '/db.php';
