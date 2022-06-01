<?php
/**
 * @desc permission.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/01/12 20:20
 */
return [
    'default' => 'basic',
    // 基础配置
    'basic' => [
        // 策略模型Model设置
        'model' => [
            'config_type' => 'file',
            'config_file_path' => config_path() . '/plugin/casbin/webman-permission/rbac-model.conf',
            'config_text' => '',
        ],
        // 适配器
        'adapter' => Casbin\WebmanPermission\Adapter\DatabaseAdapter::class, // ThinkORM 适配器
        // 'adapter' => Casbin\WebmanPermission\Adapter\LaravelDatabaseAdapter::class, // Laravel 适配器
        // 数据库设置
        'database' => [
            'connection' => '',
            'rules_table' => 'casbin_rule',
            'rules_name' => null
        ],
    ],
    // 其他扩展配置
    'abac' => [
        // 策略模型Model设置
        'model' => [
            'config_type' => 'file',
            'config_file_path' => config_path() . '/plugin/casbin/webman-permission/abac-model.conf',
            'config_text' => '',
        ],
        // 适配器
        'adapter' => Casbin\WebmanPermission\Adapter\LaravelDatabaseAdapter::class,
        // 数据库设置
        'database' => [
            // 数据库连接名称，不填为默认配置
            'connection' => '',
            // 策略表名（不含表前缀）
            'rules_table' => 'casbin_rule',
            // 策略表完整名称
            'rules_name' => null
        ],
    ],
];