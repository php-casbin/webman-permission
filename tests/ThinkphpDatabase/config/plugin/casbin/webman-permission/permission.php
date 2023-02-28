<?php

return [
    'default' => 'basic',
    // 基础配置
    'basic' => [
        // 策略模型Model设置
        'model' => [
            'config_type'      => 'file',
            'config_file_path' => __DIR__.'/rbac-model.conf',
            'config_text'      => '',
        ],
        // 适配器
        'adapter' => Casbin\WebmanPermission\Adapter\DatabaseAdapter::class, // ThinkORM 适配器
        // 数据库设置
        'database' => [
            'connection'  => '',
            'rules_table' => 'casbin_rule',
            'rules_name'  => null,
        ],
    ],
    'other' => [
        // 策略模型Model设置
        'model' => [
            'config_type'      => 'file',
            'config_file_path' => __DIR__.'/rbac-model.conf',
            'config_text'      => '',
        ],
        // 适配器
        'adapter' => Casbin\WebmanPermission\Adapter\DatabaseAdapter::class, // ThinkORM 适配器
        // 数据库设置
        'database' => [
            'connection'  => '',
            'rules_table' => 'other_casbin_rule',
            'rules_name'  => null,
        ],
    ],
];
