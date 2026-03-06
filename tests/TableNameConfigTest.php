<?php

/**
 * @desc 表名配置测试
 * @author Tinywan(ShaoBo Wan)
 * @date 2026/03/06
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Model\RuleModel;
use PHPUnit\Framework\TestCase;

/**
 * 表名配置测试
 */
class TableNameConfigTest extends TestCase
{
    /**
     * 测试当 rules_name 为 null 时使用 rules_table
     *
     * @return void
     */
    public function testTableNameFallbackToRulesTable(): void
    {
        // 模拟配置：rules_name 为 null
        $model = new RuleModel();

        // 使用反射获取 name 属性
        $reflection = new \ReflectionClass($model);
        $nameProperty = $reflection->getProperty('name');
        $nameProperty->setAccessible(true);

        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);

        // 验证当 rules_name 为 null 时，name 应该等于 table
        $name = $nameProperty->getValue($model);
        $table = $tableProperty->getValue($model);

        // name 不应该为 null
        $this->assertNotNull($name, 'Model name should not be null');

        // 如果配置中 rules_name 为 null，name 应该使用 rules_table 的值
        if ($name !== null) {
            $this->assertEquals($table, $name, 'When rules_name is null, name should equal rules_table');
        }
    }

    /**
     * 测试表名不应该是类名的蛇形命名
     *
     * @return void
     */
    public function testTableNameShouldNotBeSnakeCase(): void
    {
        $model = new RuleModel();

        $reflection = new \ReflectionClass($model);
        $nameProperty = $reflection->getProperty('name');
        $nameProperty->setAccessible(true);

        $name = $nameProperty->getValue($model);

        // 表名不应该是 'rule_model'（类名的蛇形命名）
        $this->assertNotEquals('rule_model', $name, 'Table name should not be snake_case of class name');
    }

    /**
     * 测试表名应该来自配置
     *
     * @return void
     */
    public function testTableNameFromConfig(): void
    {
        $model = new RuleModel();

        $reflection = new \ReflectionClass($model);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);

        $table = $tableProperty->getValue($model);

        // 表名应该是配置中的值（通常是 casbin_rule）
        $this->assertNotEmpty($table, 'Table name should not be empty');
        $this->assertIsString($table, 'Table name should be a string');
    }

    /**
     * 测试不同驱动的表名配置
     *
     * @return void
     */
    public function testDifferentDriverTableNames(): void
    {
        // 测试默认驱动
        $defaultModel = new RuleModel([], 'basic');
        $reflection = new \ReflectionClass($defaultModel);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        $defaultTable = $tableProperty->getValue($defaultModel);

        $this->assertNotEmpty($defaultTable);

        // 测试 restful 驱动
        $restfulModel = new RuleModel([], 'restful');
        $restfulTable = $tableProperty->getValue($restfulModel);

        $this->assertNotEmpty($restfulTable);

        // 不同驱动可能使用不同的表
        // 这取决于配置文件
    }
}
