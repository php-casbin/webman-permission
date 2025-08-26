<?php
/**
 * 测试环境bootstrap文件
 */

// 设置基础路径
define('BASE_PATH', dirname(__DIR__));

// 自动加载
require_once BASE_PATH . '/vendor/autoload.php';

// 设置测试环境
putenv('APP_ENV=testing');

// 加载测试配置
$testConfigPath = __DIR__ . '/config';
if (is_dir($testConfigPath)) {
    $config = [];
    $files = scandir($testConfigPath);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $config[pathinfo($file, PATHINFO_FILENAME)] = require $testConfigPath . '/' . $file;
        }
    }
    
    // 设置配置到全局
    if (!function_exists('config')) {
        function config($key, $default = null) {
            global $config;
            $keys = explode('.', $key);
            $value = $config;
            
            foreach ($keys as $k) {
                if (isset($value[$k])) {
                    $value = $value[$k];
                } else {
                    return $default;
                }
            }
            
            return $value;
        }
    }
}

// 设置基础函数
if (!function_exists('base_path')) {
    function base_path($path = '') {
        return BASE_PATH . ($path ? '/' . $path : '');
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '') {
        return __DIR__ . '/config' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('runtime_path')) {
    function runtime_path($path = '') {
        return __DIR__ . '/runtime' . ($path ? '/' . $path : '');
    }
}

// 创建运行时目录
if (!is_dir(__DIR__ . '/runtime')) {
    mkdir(__DIR__ . '/runtime', 0755, true);
}

// 设置错误处理
set_error_handler(function ($level, $message, $file = '', $line = 0) {
    if (error_reporting() & $level) {
        throw new ErrorException($message, 0, $level, $file, $line);
    }
});

// 设置时区
date_default_timezone_set('Asia/Shanghai');