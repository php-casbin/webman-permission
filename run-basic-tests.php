#!/usr/bin/env php
<?php

echo "=== 基础测试运行器 ===\n\n";

// 检查文件是否存在
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "错误: vendor/autoload.php 不存在\n";
    echo "请运行: composer install\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

echo "✅ 自动加载成功\n";

// 测试基础类是否存在
echo "🔍 检查类文件...\n";

if (class_exists(\Casbin\WebmanPermission\Permission::class)) {
    echo "✅ Permission 类存在\n";
} else {
    echo "❌ Permission 类不存在\n";
    exit(1);
}

if (class_exists(\PHPUnit\Framework\TestCase::class)) {
    echo "✅ PHPUnit TestCase 存在\n";
} else {
    echo "❌ PHPUnit TestCase 不存在\n";
    exit(1);
}

echo "\n🚀 运行基础测试...\n";

// 运行基础测试
$command = escapeshellcmd(__DIR__ . '/vendor/bin/phpunit') . ' ' . escapeshellarg(__DIR__ . '/tests/BasicPermissionTest.php') . ' --colors=always';

echo "执行命令: $command\n\n";

$output = shell_exec($command);
echo $output;

echo "\n=== 测试完成 ===\n";