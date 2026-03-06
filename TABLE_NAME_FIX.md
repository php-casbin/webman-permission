# 表名配置问题修复

## 问题描述

### 错误信息
```
think\db\exception\PDOException: SQLSTATE[42S02]: Base table or view not found:
1146 Table 'ai.rule_model' doesn't exist
```

### 问题原因
当配置文件中 `rules_name` 设置为 `null` 时，think-orm 会使用类名的蛇形命名作为表名：
- 类名：`RuleModel`
- 自动生成的表名：`rule_model` ❌

但实际的表名应该是配置中的 `rules_table`（如 `casbin_rule`）。

## 根本原因

在 `RuleModel.php` 的构造函数中：

```php
// 错误的代码
$this->name = $this->config('database.rules_name'); // 当为 null 时，think-orm 使用类名
```

当 `rules_name` 为 `null` 时，think-orm 会回退到使用类名的蛇形命名。

## 解决方案

修改 `src/Model/RuleModel.php` 构造函数：

```php
// 修复后的代码
$rulesName = $this->config('database.rules_name');
$this->name = $rulesName ?: $this->config('database.rules_table');
```

现在当 `rules_name` 为 `null` 时，会使用 `rules_table` 的值作为表名。

## 配置说明

### 推荐配置（简化）

```php
'database' => [
    'connection' => '',
    'rules_table' => 'casbin_rule',
    'rules_name' => null  // 将自动使用 rules_table 的值
],
```

### 明确配置（可选）

```php
'database' => [
    'connection' => '',
    'rules_table' => 'casbin_rule',
    'rules_name' => 'casbin_rule'  // 明确指定表名
],
```

### 自定义表名

```php
'database' => [
    'connection' => '',
    'rules_table' => 'my_custom_table',
    'rules_name' => 'my_custom_table'  // 或设为 null 自动使用 rules_table
],
```

## think-orm 表名规则

### name vs table 的区别

- **`$table`**: 完整的表名（可包含前缀）
- **`$name`**: 不含前缀的表名

当 `$name` 为 `null` 时，think-orm 的行为：
1. 尝试从类名推断：`RuleModel` → `rule_model`
2. 如果设置了 `$table`，应该使用 `$table` 的值

### 修复前后对比

| 配置 | 修复前 | 修复后 |
|------|--------|--------|
| `rules_name: null` | 使用 `rule_model` ❌ | 使用 `casbin_rule` ✅ |
| `rules_name: 'casbin_rule'` | 使用 `casbin_rule` ✅ | 使用 `casbin_rule` ✅ |
| `rules_name: 'custom_table'` | 使用 `custom_table` ✅ | 使用 `custom_table` ✅ |

## 测试验证

运行测试：
```bash
php vendor/bin/phpunit tests/TableNameConfigTest.php
```

## 影响范围

### 受影响的场景
- 配置文件中 `rules_name` 设置为 `null`
- 使用默认配置的项目

### 不受影响的场景
- 明确设置了 `rules_name` 的项目
- 使用 Laravel 适配器的项目

## 相关文件

- ✅ `src/Model/RuleModel.php` - 修复表名回退逻辑
- ✅ `tests/TableNameConfigTest.php` - 新增测试

---
**修复日期：** 2026-03-06
**影响版本：** v2.4.0, v2.4.1
**修复版本：** v2.4.2
