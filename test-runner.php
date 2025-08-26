#!/usr/bin/env php
<?php

/**
 * 测试运行脚本
 * 
 * 这个脚本提供了更友好的测试运行界面和报告
 */

use PHPUnit\TextUI\Command;

require_once __DIR__ . '/vendor/autoload.php';

$command = new Command();

// 设置默认参数
$defaultArgs = [
    'tests/',
    '--colors=always',
    '--verbose'
];

// 合并命令行参数
$argv = array_merge(['phpunit'], $defaultArgs, array_slice($argv, 1));

try {
    $command->run($argv);
} catch (Exception $e) {
    echo "测试运行失败: " . $e->getMessage() . "\n";
    exit(1);
}