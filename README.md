<h1 align="center">
    Webman Authorization Plugin
</h1>

[![Default](https://github.com/php-casbin/webman-permission/actions/workflows/default.yml/badge.svg)](https://github.com/php-casbin/webman-permission/actions/workflows/default.yml)
[![Latest Stable Version](https://poser.pugx.org/casbin/webman-permission/v/stable)](https://packagist.org/packages/casbin/webman-permission)
[![Total Downloads](https://poser.pugx.org/casbin/webman-permission/downloads)](https://packagist.org/packages/casbin/webman-permission)
[![License](https://poser.pugx.org/casbin/webman-permission/license)](https://packagist.org/packages/casbin/webman-permission)

An authorization library that supports access control models like ACL, RBAC, ABAC for webman plugin

# 安装

```sh
composer require -W casbin/webman-permission
```

# 使用

## 依赖注入配置

修改配置`config/container.php`，其最终内容如下：

```php
$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(config('dependence', []));
$builder->useAutowiring(true);
return $builder->build();
```

## 数据库配置

默认策略存储是使用的ThinkORM。

### 1、模型配置

默认使用ThinkORM。修改数据库 `thinkorm.php` 配置

> 如使用laravel数据库，配置参考如下
  - 修改数据库 `database.php` 配置
  - 修改数据库 `permission.php` 的`adapter`适配器为laravel适配器

### 2、创建 `casbin_rule` 数据表
```sql
CREATE TABLE `casbin_rule` (
	`id` BIGINT ( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ptype` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v0` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v1` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v2` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v3` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v4` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v5` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	PRIMARY KEY ( `id` ) USING BTREE,
	KEY `idx_ptype` ( `ptype` ) USING BTREE,
	KEY `idx_v0` ( `v0` ) USING BTREE,
	KEY `idx_v1` ( `v1` ) USING BTREE,
	KEY `idx_v2` ( `v2` ) USING BTREE,
	KEY `idx_v3` ( `v3` ) USING BTREE,
	KEY `idx_v4` ( `v4` ) USING BTREE,
    KEY `idx_v5` ( `v5` ) USING BTREE 
) ENGINE = INNODB CHARSET = utf8mb4 COMMENT = '策略规则表';
```
### 3、配置 `config/redis` 配置

### 4、重启webman

```
php start.php restart
```
或者
```
php start.php restart -d
```

# 使用

安装成功后，可以这样使用:

```php
use Casbin\WebmanPermission\Permission;

// adds permissions to a user
Permission::addPermissionForUser('eve', 'articles', 'read');
// adds a role for a user.
Permission::addRoleForUser('eve', 'writer');
// adds permissions to a rule
Permission::addPolicy('writer', 'articles','edit');
```

你可以检查一个用户是否拥有某个权限:

```php
if (\Casbin\WebmanPermission\Permission::enforce('eve', 'articles', 'edit')) {
    echo '恭喜你！通过权限认证';
} else {
    echo '对不起，您没有该资源访问权限';
}
```

# 多套驱动配置

```php
$permission = \Casbin\WebmanPermission\Permission::driver('restful_conf');
// adds permissions to a user
$permission->addPermissionForUser('eve', 'articles', 'read');
// adds a role for a user.
$permission->addRoleForUser('eve', 'writer');
// adds permissions to a rule
$permission->addPolicy('writer', 'articles','edit');

if ($permission->enforce('eve', 'articles', 'edit')) {
    echo '恭喜你！通过权限认证';
} else {
    echo '对不起，您没有该资源访问权限';
}
```

更多 `API` 参考 [Casbin API](https://casbin.org/docs/en/management-api) 。

# 教程
* [Casbin权限实战：入门分享(中文)](https://www.bilibili.com/video/BV1A541187M4/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)
* [Casbin权限实战：基于角色的RBAC授权](https://www.bilibili.com/video/BV1A541187M4/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)
* [Casbin权限实战：RESTful及中间件使用](https://www.bilibili.com/video/BV1uk4y117up/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)
* [Casbin权限实战：如何使用自定义匹配函数](https://www.bilibili.com/video/BV1dq4y1Z78g/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)
* [Webman实战教程：如何使用casbin权限控制](https://www.bilibili.com/video/BV1X34y1Q7ZH/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)

# 感谢

[Casbin](https://github.com/php-casbin/php-casbin)，你可以查看全部文档在其 [官网](https://casbin.org/) 上。

<details>
	
<summary> 解除 https://github.com/PHP-DI/PHP-DI依赖的解决方案（不推荐）</summary>

1、卸载DI依赖包：`composer remove php-di/php-di`

2、修改：`Casbin\WebmanPermission\Permission` 文件

```php
if (is_null(static::$_manager)) {
    static::$_manager = new Enforcer($model, Container::get($config['adapter']),false);
}
```
替换为
```php
if (is_null(static::$_manager)) {
    if ($config['adapter'] == DatabaseAdapter::class) {
        $_model = new RuleModel();
    } elseif ($config['adapter'] == LaravelDatabaseAdapter::class) {
        $_model = new LaravelRuleModel();
    }
    static::$_manager = new Enforcer($model,  new $config['adapter']($_model), false);
}
```
耦合太高，不建议这么搞，更多了解：https://www.workerman.net/doc/webman/di.html
</details>
