<?php

/**
 * @desc Real Policy Model
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/01/12 10:37
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission\Model;

use think\Model;
use think\contract\Arrayable;

/**
 * RuleModel Model
 */
class RuleModel extends Model implements Arrayable
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
        $this->connection = $this->config('database.connection') ?: '';
        $this->table = $this->config('database.rules_table');
        $this->name = $this->config('database.rules_name');
        parent::__construct($data);
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