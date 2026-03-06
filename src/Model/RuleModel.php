<?php

/**
 * @desc Real Policy Model
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/01/12 10:37
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission\Model;

use think\Model;

/**
 * RuleModel Model
 */
class RuleModel extends Model
{
    /**
     * 设置字段信息
     *
     * @var array
     */
    protected $schema = [
        'id'    => 'int',
        'ptype' => 'string',
        'v0'    => 'string',
        'v1'    => 'string',
        'v2'    => 'string',
        'v3'    => 'string',
        'v4'    => 'string',
        'v5'    => 'string'
    ];

    /** @var string|null $driver */
    protected ?string $driver;

    /**
     * 架构函数
     * @param array $data
     * @param string|null $driver
     */
    public function __construct(array $data = [], ?string $driver = null)
    {
        $this->driver = $driver;

        // 调用父类构造函数，确保 WeakMap 初始化（think-orm 4.0+）
        parent::__construct($data);
    }

    /**
     * 模型初始化
     * @return void
     */
    protected function initialize(): void
    {
        parent::initialize();

        // 使用 setOption 方法设置模型属性（think-orm 4.0 推荐方式）
        $this->setOption('connection', $this->config('database.connection') ?: '');
        $this->setOption('table', $this->config('database.rules_table'));

        // 如果 rules_name 为 null，使用 rules_table 作为表名
        $rulesName = $this->config('database.rules_name');
        $this->setOption('name', $rulesName ?: $this->config('database.rules_table'));
    }

    /**
     * Gets config value by key.
     *
     * @param string|null $key
     * @param null $default
     *
     * @return mixed
     */
    protected function config(string $key = null, $default = null)
    {
        $driver = $this->driver ?? config('plugin.casbin.webman-permission.permission.default');
        return config('plugin.casbin.webman-permission.permission.' . $driver . '.' . $key, $default);
    }
}