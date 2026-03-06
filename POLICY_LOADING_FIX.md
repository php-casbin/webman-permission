# Think-ORM 4.0 策略加载修复说明

## 新问题：策略大小错误

### 错误信息
```
Casbin\Exceptions\CasbinException: invalid policy size: expected 3, got 8,
pvals: ["eve","articles","read","","","","","casbin_rule"]
```

### 原因
think-orm 4.0 的 `toArray()` 方法可能返回额外字段（如表名），导致策略数组包含多余元素。

### 解决方案
在 `DatabaseAdapter.php` 中明确指定返回字段：

**修改前：**
```php
$rows = $this->model->select()->hidden(['id'])->toArray();
```

**修改后：**
```php
$rows = $this->model->field(['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'])->select()->toArray();
foreach ($rows as $row) {
    $filteredRow = [
        'ptype' => $row['ptype'] ?? '',
        'v0' => $row['v0'] ?? '',
        'v1' => $row['v1'] ?? '',
        'v2' => $row['v2'] ?? '',
        'v3' => $row['v3'] ?? '',
        'v4' => $row['v4'] ?? '',
        'v5' => $row['v5'] ?? '',
    ];
    $this->loadPolicyArray($this->filterRule($filteredRow), $model);
}
```

## 已修复的文件

1. ✅ `src/Model/RuleModel.php` - WeakMap 初始化顺序
2. ✅ `src/Adapter/DatabaseAdapter.php` - 策略加载字段过滤
   - `loadPolicy()` 方法
   - `loadFilteredPolicy()` 方法

## 测试

运行测试验证修复：
```bash
php vendor/bin/phpunit tests/PolicyLoadingTest.php
```

---
**更新时间：** 2026-03-06
