#!/usr/bin/env php
<?php

/**
 * 简化的测试运行脚本
 */

echo "=== Webman Permission 测试运行器 ===\n\n";

// 检查PHPUnit是否可用
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "错误: 请先运行 composer install\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

// 设置测试环境
putenv('APP_ENV=testing');

echo "正在运行测试...\n\n";

// 运行简单测试
$command = 'php vendor/bin/phpunit tests/SimplePermissionTest.php --bootstrap tests/bootstrap-test.php --colors=always';

echo "命令: $command\n\n";
echo "========================================\n";

passthru($command);

echo "\n========================================\n";
echo "测试完成！\n";