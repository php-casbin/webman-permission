<?php

/**
 * @desc Laravel Policy Model
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/01/12 10:37
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * RuleModel Model
 *
 * @inheritDoc
 */
class LaravelRuleModel extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];


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
        $connection = $this->config('database.connection') ?: config('database.default');
        $this->setConnection($connection);
        $this->setTable($this->config('database.rules_table'));
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