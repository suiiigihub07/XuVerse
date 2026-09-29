<?php

// Hide diagnostics before reading private configuration or opening the database.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function xuverse_config($key, $fallback = null)
{
    static $config;
    if ($config === null) {
        $file = getenv('XUVERSE_CONFIG_FILE') ?: dirname(__DIR__) . '/config.local.php';
        $config = is_file($file) ? require $file : [];
        if (!is_array($config)) {
            throw new RuntimeException('Invalid XuVerse configuration.');
        }
    }
    foreach (['XUVERSE_' . $key, $key] as $name) {
        $value = getenv($name);
        if ($value !== false) { return $value; }
    }
    return $config[$key] ?? $fallback;
}
