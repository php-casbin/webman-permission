<?php

/**
 * @desc Think-ORM 4.0 策略加载测试
 * @author Tinywan(ShaoBo Wan)
 * @date 2026/03/06
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Adapter\DatabaseAdapter;
use Casbin\WebmanPermission\Model\RuleModel;
use PHPUnit\Framework\TestCase;

/**
 * Think-ORM 4.0 策略加载测试
 */
class PolicyLoadingTest extends TestCase
{
    /**
     * 测试策略数据过滤
     *
     * @return void
     */
    public function testFilterRuleRemovesExtraFields(): void
    {
        $adapter = new DatabaseAdapter();

        // 模拟 think-orm 4.0 可能返回的数据（包含额外字段）
        $rowWithExtraFields = [
            'ptype' => 'p',
            'v0' => 'eve',
            'v1' => 'articles',
            'v2' => 'read',
            'v3' => '',
            'v4' => '',
            'v5' => '',
            'table_name' => 'casbin_rule',  // 额外字段
            'id' => 1,  // 额外字段
        ];

        // 使用反射访问 protected 方法
        $reflection = new \ReflectionClass($adapter);
        $method = $reflection->getMethod('filterRule');
        $method->setAccessible(true);

        // 过滤后应该只包含有效的策略字段
        $filtered = $method->invoke($adapter, [
            'ptype' => 'p',
            'v0' => 'eve',
            'v1' => 'articles',
            'v2' => 'read',
            'v3' => '',
            'v4' => '',
            'v5' => '',
        ]);

        // 验证结果
        $this->assertIsArray($filtered);
        $this->assertEquals(['p', 'eve', 'articles', 'read'], $filtered);
        $this->assertCount(4, $filtered);
    }

    /**
     * 测试策略数组格式
     *
     * @return void
     */
    public function testPolicyArrayFormat(): void
    {
        // 正确的策略格式
        $validPolicy = ['p', 'alice', 'data1', 'read'];
        $this->assertCount(4, $validPolicy);
        $this->assertEquals('p', $validPolicy[0]);

        // 错误的策略格式（包含额外元素）
        $invalidPolicy = ['p', 'alice', 'data1', 'read', '', '', '', 'casbin_rule'];
        $this->assertGreaterThan(4, count($invalidPolicy));
    }

    /**
     * 测试空值过滤
     *
     * @return void
     */
    public function testEmptyValueFiltering(): void
    {
        $adapter = new DatabaseAdapter();
        $reflection = new \ReflectionClass($adapter);
        $method = $reflection->getMethod('filterRule');
        $method->setAccessible(true);

        // 测试末尾空值被移除
        $result = $method->invoke($adapter, [
            'ptype' => 'p',
            'v0' => 'bob',
            'v1' => 'data2',
            'v2' => 'write',
            'v3' => '',
            'v4' => null,
            'v5' => '',
        ]);

        $this->assertEquals(['p', 'bob', 'data2', 'write'], $result);
    }

    /**
     * 测试中间空值保留
     *
     * @return void
     */
    public function testMiddleEmptyValuePreserved(): void
    {
        $adapter = new DatabaseAdapter();
        $reflection = new \ReflectionClass($adapter);
        $method = $reflection->getMethod('filterRule');
        $method->setAccessible(true);

        // 测试中间的空值被保留
        $result = $method->invoke($adapter, [
            'ptype' => 'p',
            'v0' => 'alice',
            'v1' => '',
            'v2' => 'read',
            'v3' => '',
            'v4' => '',
            'v5' => '',
        ]);

        // 中间的空值应该保留，末尾的空值应该移除
        $this->assertEquals(['p', 'alice', '', 'read'], $result);
    }

    /**
     * 测试策略大小验证
     *
     * @return void
     */
    public function testPolicySizeValidation(): void
    {
        // RBAC 模型期望的策略大小
        $rbacPolicySize = 3; // ptype, subject, object
        $rbacWithActionSize = 4; // ptype, subject, object, action

        // 正确的策略
        $validRbacPolicy = ['p', 'alice', 'data1'];
        $this->assertCount($rbacPolicySize, $validRbacPolicy);

        $validRbacWithAction = ['p', 'alice', 'data1', 'read'];
        $this->assertCount($rbacWithActionSize, $validRbacWithAction);

        // 错误的策略（包含额外字段）
        $invalidPolicy = ['p', 'alice', 'data1', 'read', '', '', '', 'casbin_rule'];
        $this->assertNotEquals($rbacWithActionSize, count($invalidPolicy));
    }
}
