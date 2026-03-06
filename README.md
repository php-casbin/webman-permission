<p align="center">
  <img width="260px" src="./workbunny-logo.png" alt="workbunny">
</p>

<p align="center">
  <strong>🐇 An Authorization Library for Webman Plugin 🐇</strong>
</p>

<h1 align="center">
  🐇 Webman Authorization Plugin Based on Casbin 🐇
</h1>

<p align="center">
  <a href="https://github.com/php-casbin/webman-permission/actions/workflows/default.yml">
    <img src="https://github.com/php-casbin/webman-permission/actions/workflows/default.yml/badge.svg?branch=main" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/casbin/webman-permission">
    <img src="https://poser.pugx.org/casbin/webman-permission/v/stable" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/casbin/webman-permission">
    <img src="https://poser.pugx.org/casbin/webman-permission/downloads" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/casbin/webman-permission">
    <img src="https://poser.pugx.org/casbin/webman-permission/license" alt="License">
  </a>
</p>

> An authorization library that supports access control models like ACL, RBAC, ABAC for Webman plugin.

---

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
  - [Dependency Injection](#dependency-injection)
  - [Database Configuration](#database-configuration)
- [Usage](#usage)
- [Multiple Driver Configuration](#multiple-driver-configuration)
- [Tutorials](#tutorials)
- [Testing](#testing)
- [Contributing](#contributing)
- [Credits](#credits)
- [Troubleshooting](#troubleshooting)

---

## Installation

Install the package via Composer:

```bash
composer require -W casbin/webman-permission
```

---

## Configuration

### Dependency Injection

Modify the `config/container.php` configuration file as follows:

```php
$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(config('dependence', []));
$builder->useAutowiring(true);
return $builder->build();
```

### Database Configuration

By default, the policy storage uses **ThinkORM**.

#### 1. Model Configuration

The default uses ThinkORM. Modify the database configuration in `config/thinkorm.php`.

> **Note:** If using Laravel database, configure as follows:
> - Modify the database configuration in `config/database.php`
> - Change the `adapter` in `config/plugin/casbin/webman-permission/permission.php` to the Laravel adapter

#### 2. Create `casbin_rule` Table

Execute the following SQL to create the policy rules table:

```sql
CREATE TABLE `casbin_rule` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ptype` VARCHAR(128) NOT NULL DEFAULT '',
    `v0` VARCHAR(128) NOT NULL DEFAULT '',
    `v1` VARCHAR(128) NOT NULL DEFAULT '',
    `v2` VARCHAR(128) NOT NULL DEFAULT '',
    `v3` VARCHAR(128) NOT NULL DEFAULT '',
    `v4` VARCHAR(128) NOT NULL DEFAULT '',
    `v5` VARCHAR(128) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `idx_ptype` (`ptype`) USING BTREE,
    KEY `idx_v0` (`v0`) USING BTREE,
    KEY `idx_v1` (`v1`) USING BTREE,
    KEY `idx_v2` (`v2`) USING BTREE,
    KEY `idx_v3` (`v3`) USING BTREE,
    KEY `idx_v4` (`v4`) USING BTREE,
    KEY `idx_v5` (`v5`) USING BTREE
) ENGINE = INNODB CHARSET = utf8mb4 COMMENT = 'Casbin Policy Rules Table';
```

#### 3. Configure Redis

Configure your Redis settings in `config/redis.php`.

#### 4. Restart Webman

```bash
# Restart in foreground
php start.php restart

# Or restart in daemon mode
php start.php restart -d
```

---

## Usage

After successful installation, you can use the library as follows:

### Basic Operations

```php
use Casbin\WebmanPermission\Permission;

// Add permissions to a user
Permission::addPermissionForUser('eve', 'articles', 'read');

// Add a role for a user
Permission::addRoleForUser('eve', 'writer');

// Add permissions to a role
Permission::addPolicy('writer', 'articles', 'edit');
```

### Permission Check

```php
if (\Casbin\WebmanPermission\Permission::enforce('eve', 'articles', 'edit')) {
    echo 'Congratulations! Permission granted.';
} else {
    echo 'Sorry, you do not have access to this resource.';
}
```

---

## Multiple Driver Configuration

You can use multiple driver configurations:

```php
$permission = \Casbin\WebmanPermission\Permission::driver('restful_conf');

// Add permissions to a user
$permission->addPermissionForUser('eve', 'articles', 'read');

// Add a role for a user
$permission->addRoleForUser('eve', 'writer');

// Add permissions to a role
$permission->addPolicy('writer', 'articles', 'edit');

// Check permissions
if ($permission->enforce('eve', 'articles', 'edit')) {
    echo 'Congratulations! Permission granted.';
} else {
    echo 'Sorry, you do not have access to this resource.';
}
```

For more API details, refer to the [Casbin API Documentation](https://casbin.org/docs/en/management-api).

---

## Tutorials

* [Casbin Permission Practice: Getting Started (Chinese)](https://www.bilibili.com/video/BV1A541187M4/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)
* [Casbin Permission Practice: RBAC Authorization Based on Roles (Chinese)](https://www.bilibili.com/video/BV1A541187M4/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)
* [Casbin Permission Practice: RESTful and Middleware Usage (Chinese)](https://www.bilibili.com/video/BV1uk4y117up/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)
* [Casbin Permission Practice: Using Custom Matching Functions (Chinese)](https://www.bilibili.com/video/BV1dq4y1Z78g/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)
* [Webman Practice Tutorial: Using Casbin Permission Control (Chinese)](https://www.bilibili.com/video/BV1X34y1Q7ZH/?vd_source=a9321be9ed112f8d6fdc8ee87640be1b)

---

## Testing

This project includes a comprehensive unit test suite covering the following aspects:

### Test File Structure

```
tests/
├── Adapter.php                    # Basic adapter tests
├── PermissionTest.php            # Permission class tests
├── AdapterTest.php               # Detailed adapter tests
├── EdgeCaseTest.php              # Edge case tests
├── IntegrationTest.php           # Integration tests
├── LaravelDatabase/
│   ├── LaravelDatabaseAdapterTest.php
│   └── TestCase.php
├── ThinkphpDatabase/
│   ├── DatabaseAdapterTest.php
│   └── TestCase.php
└── config/
    └── plugin/
        └── casbin/
            └── webman-permission/
                └── permission.php
```

### Test Coverage

1. **Basic Functionality**
   - Permission add, remove, check
   - Role assignment, removal
   - Policy management

2. **Adapter Tests**
   - Database operations
   - Filter functionality
   - Batch operations
   - Transaction handling

3. **Edge Cases**
   - Null value handling
   - Special characters
   - Large data volumes
   - Performance testing

4. **Integration Tests**
   - Complete RBAC workflow
   - Domain permission control
   - Multi-driver support
   - Complex business scenarios

5. **Error Handling**
   - Exception scenarios
   - Invalid input
   - Concurrent access

### Running Tests

```bash
# Run all tests
php vendor/bin/phpunit tests/

# Run specific test file
php vendor/bin/phpunit tests/PermissionTest.php

# Run specific test method
php vendor/bin/phpunit --filter testAddPermissionForUser tests/PermissionTest.php

# Generate coverage report
php vendor/bin/phpunit --coverage-html coverage tests/
```

### Requirements

- PHP >= 8.1
- PHPUnit >= 9.0
- Database connection
- Redis connection

### Test Environment

The test environment automatically creates the following tables:
- `casbin_rule` - Default policy table
- `other_casbin_rule` - Other driver policy table

### Best Practices

1. **Writing New Tests**
   - Inherit from appropriate test base classes
   - Follow naming conventions
   - Add necessary assertions

2. **Test Data Management**
   - Use `setUp()` and `tearDown()` methods
   - Ensure test data isolation
   - Clean up test data

3. **Test Coverage**
   - Cover normal workflows
   - Test exception scenarios
   - Verify boundary conditions

---

## Contributing

### Adding New Features

1. Write corresponding test cases for new features
2. Ensure test coverage meets requirements
3. Run the complete test suite
4. Check test status before submitting code

### Bug Fixes

1. Write reproduction tests for bugs
2. Verify tests pass after fixing bugs
3. Ensure existing functionality is not affected

---

## Credits

Built on top of [Casbin](https://github.com/php-casbin/php-casbin). For full documentation, visit the [official website](https://casbin.org/).

---

## Advanced Configuration

<details>

<summary>Removing PHP-DI Dependency (Not Recommended)</summary>

1. Uninstall the DI dependency package:
```bash
composer remove php-di/php-di
```

2. Modify the `Casbin\WebmanPermission\Permission` file:

Replace:
```php
if (is_null(static::$_manager)) {
    static::$_manager = new Enforcer($model, Container::get($config['adapter']), false);
}
```

With:
```php
if (is_null(static::$_manager)) {
    if ($config['adapter'] == DatabaseAdapter::class) {
        $_model = new RuleModel();
    } elseif ($config['adapter'] == LaravelDatabaseAdapter::class) {
        $_model = new LaravelRuleModel();
    }
    static::$_manager = new Enforcer($model, new $config['adapter']($_model), false);
}
```

> **Warning:** This approach has high coupling and is not recommended. For more information, visit: https://www.workerman.net/doc/webman/di.html

</details>

---

## Troubleshooting

### Think-ORM 4.0 Compatibility

**Error:** `Object not contained in WeakMap` or `array_search(): Argument #2 ($haystack) must be of type array, null given`

**Solution:** This package fully supports think-orm 4.0+. If you encounter WeakMap errors:

1. Ensure you're using the latest version:
```bash
composer require casbin/webman-permission:^2.4
```

2. For detailed information, see:
   - [Think-ORM 4.0 Fix Guide](./THINK_ORM_4_FIX.md)
   - [Think-ORM Compatibility Guide](./THINK_ORM_COMPATIBILITY.md)

**Supported Versions:**
- ✅ think-orm 2.0.53+
- ✅ think-orm 3.x
- ✅ think-orm 4.0.30+

### Laravel Driver Error

**Error:** `Call to a member function connection() on null`

**Solution:** Check if your local database proxy is working correctly. Using Docker container host addresses like `dnmp-mysql` may cause this issue.

---

## License

[MIT License](LICENSE) 
