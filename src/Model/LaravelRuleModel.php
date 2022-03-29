<?php

/**
 * @desc Laravel Policy Model
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/01/12 10:37
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission\Model;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;

/**
 * RuleModel Model
 */
class LaravelRuleModel extends Model
{
    /**
     * a cache store.
     *
     * @var Repository
     */
    protected Repository $store;

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];

    /**
     * the guard for lauthz.
     *
     * @var string
     */
    protected string $guard;

    /**
     * 架构函数
     * @access public
     * @param array $data 数据
     */
    public function __construct(array $data = [])
    {
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
        $driver = config('plugin.casbin.webman-permission.permission.default');
        return config('plugin.casbin.webman-permission.permission.' . $driver . '.' . $key, $default);
    }

    /**
     * Gets rules from caches.
     *
     * @return mixed
     */
    public function getAllFromCache()
    {
        $get = function () {
            return $this->select('ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5')->get()->toArray();
        };
        if (!$this->config('cache.enabled', false)) {
            return $get();
        }

        return $this->store->remember($this->config('cache.key'), $this->config('cache.ttl'), $get);
    }
}
