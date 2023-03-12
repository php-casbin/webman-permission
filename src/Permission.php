<?php

/**
 * @desc 描述
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/1/10 21:33
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission;


use Casbin\Enforcer;
use Casbin\Exceptions\CasbinException;
use Casbin\Model\Model;
use support\Container;
use Casbin\WebmanPermission\Watcher\RedisWatcher;

/**
 * @see \Casbin\Enforcer
 * @mixin Enforcer
 * @method static enforce(mixed ...$rvals) 权限检查，输入参数通常是(sub, obj, act)
 * @method static bool addPolicy(mixed ...$params) 当前策略添加授权规则
 * @method static bool addPolicies(mixed ...$params) 当前策略添加授权规则
 * @method static bool hasPolicy(mixed ...$params) 确定是否存在授权规则
 * @method static bool removePolicy(mixed ...$params) 当前策略移除授权规则
 * @method static getAllRoles() 获取所有角色
 * @method static getPolicy() 获取所有的角色的授权规则
 * @method static getRolesForUser(string $name, string ...$domain) 获取用户具有的角色
 * @method static getUsersForRole(string $name, string ...$domain) 获取具有角色的用户
 * @method static hasRoleForUser(string $name, string $role, string ...$domain) 确定用户是否具有角色
 * @method static addRoleForUser(string $user, string $role, string ...$domain) 给用户添加角色
 * @method static addPermissionForUser(string $user, string ...$permission) 赋予权限给某个用户或角色
 * @method static deleteRoleForUser(string $user, string $role, string $domain) 删除用户的角色
 * @method static deleteRolesForUser(string $user, string ...$domain) 删除某个用户的所有角色
 * @method static deleteRole(string $role) 删除单个角色
 * @method static deletePermission(string ...$permission) 删除权限
 * @method static deletePermissionForUser(string $name, string $permission) 删除用户或角色的权限。如果用户或角色没有权限则返回 false(不会受影响)。
 * @method static deletePermissionsForUser(string $name) 删除用户或角色的权限。如果用户或角色没有任何权限（也就是不受影响），则返回false。
 * @method static getPermissionsForUser(string $name) 获取用户或角色的所有权限
 * @method static hasPermissionForUser(string $user, string ...$permission) 决定某个用户是否拥有某个权限
 * @method static getImplicitRolesForUser(string $name, string ...$domain) 获取用户具有的隐式角色
 * @method static getImplicitPermissionsForUser(string $username, string ...$domain) 获取用户具有的隐式权限
 * @method static addFunction(string $name, \Closure $func) 添加一个自定义函数
 */
class Permission
{
    /** @var Enforcer[] $_manager */
    protected static array $_manager = [];

    /**
     * @param  string|null  $driver
     * @return Enforcer
     * @throws CasbinException
     * @author Lyt8384
     */
    public static function driver(?string $driver = null): Enforcer
    {
        $driver = $driver ?? self::getDefaultDriver();
        $config = self::getConfig($driver);

        if (isset(static::$_manager[$driver])) {
            return static::$_manager[$driver];
        }

        $model = new Model();
        if ('file' == $config['model']['config_type']) {
            $model->loadModel($config['model']['config_file_path']);
        } elseif ('text' == $config['model']['config_type']) {
            $model->loadModel($config['model']['config_text']);
        }
        static::$_manager[$driver] = new Enforcer($model, Container::make($config['adapter'], [$driver]), false);

        $watcher = new RedisWatcher(config('redis.default'), $driver);
        static::$_manager[$driver]->setWatcher($watcher);
        $watcher->setUpdateCallback(function () use ($driver) {
            static::$_manager[$driver]->loadPolicy();
        });
        return static::$_manager[$driver];
    }

    /**
     * @desc: 获取所有驱动
     * @return Enforcer[]
     * @author Tinywan(ShaoBo Wan)
     */
    public static function getAllDriver(): array
    {
        return static::$_manager;
    }

    /**
     * @desc: 默认驱动
     * @return mixed
     * @author Tinywan(ShaoBo Wan)
     */
    public static function getDefaultDriver()
    {
        return self::getConfig('default');
    }

    /**
     * @desc: 获取驱动配置
     * @param string|null $name 名称
     * @param null $default 默认值
     * @return mixed
     * @author Tinywan(ShaoBo Wan)
     */
    public static function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return config('plugin.casbin.webman-permission.permission.' . $name, $default);
        }
        return config('plugin.casbin.webman-permission.permission.default');
    }

    /**
     * @desc: 静态调用
     * @param string $method
     * @param $arguments
     * @return mixed
     * @throws CasbinException
     * @author Tinywan(ShaoBo Wan)
     */
    public static function __callStatic(string $method, $arguments)
    {
        return self::driver()->{$method}(...$arguments);
    }
}
