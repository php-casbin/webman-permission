# webman casbin plugin

[![Latest Stable Version](http://poser.pugx.org/casbin/webman-permission/v)](https://packagist.org/packages/casbin/webman-permission) [![Total Downloads](http://poser.pugx.org/casbin/webman-permission/downloads)](https://packagist.org/packages/casbin/webman-permission) [![Latest Unstable Version](http://poser.pugx.org/casbin/webman-permission/v/unstable)](https://packagist.org/packages/casbin/webman-permission) [![License](http://poser.pugx.org/casbin/webman-permission/license)](https://packagist.org/packages/casbin/webman-permission) [![PHP Version Require](http://poser.pugx.org/casbin/webman-permission/require/php)](https://packagist.org/packages/casbin/webman-permission)

An authorization library that supports access control models like ACL, RBAC, ABAC for webman plugin

## 依赖

- [ThinkORM](https://www.workerman.net/doc/webman/db/others.html)（默认）
- [PHP-DI](https://github.com/PHP-DI/PHP-DI)
- [illuminate/database](https://www.workerman.net/doc/webman/db/tutorial.html)（可选）

## 安装

```sh
composer require -W casbin/webman-permission
```

## 使用

### 1. 依赖注入配置

修改配置`config/container.php`，其最终内容如下：

```php
$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(config('dependence', []));
$builder->useAutowiring(true);
return $builder->build();
```

### 2. 数据库配置

> 默认策略存储是使用的ThinkORM。
> 如使用 laravel的数据库 [illuminate/database](https://github.com/illuminate/database)，请按照官方文档按照相应的依赖包：https://www.workerman.net/doc/webman/db/tutorial.html

#### 🚀 (1) 模型配置

📒📒📒 **使用ThinkORM（默认）** 📒📒📒
  - 修改数据库 `thinkorm.php` 配置

📕📕📕 **使用laravel数据库（可选）** 📕📕📕
  - 修改数据库 `database.php` 配置
  - 修改数据库 `permission.php` 的`adapter`适配器为laravel适配器

#### 🔰 (2) 创建 `casbin_rule` 数据表
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

#### 📚 (3) 配置 `config/redis` 配置

## 重启webman

```
php start.php restart
```
或者
```
php start.php restart -d
```

## 用法

### 快速开始

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
if (Permission::enforce("eve", "articles", "edit")) {
    echo '恭喜你！通过权限认证';
} else {
    echo '对不起，您没有该资源访问权限';
}
```

更多 `API` 参考 [Casbin API](https://casbin.org/docs/en/management-api) 。

## 感谢

[Casbin](https://github.com/php-casbin/php-casbin)，你可以查看全部文档在其 [官网](https://casbin.org/) 上。
