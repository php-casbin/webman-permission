<?php

/**
 * @desc Think-ORM 4.0 兼容性测试
 * @author Tinywan(ShaoBo Wan)
 * @date 2026/03/06
 */

declare(strict_types=1);

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Model\RuleModel;
use PHPUnit\Framework\TestCase;

/**
 * Think-ORM 4.0 WeakMap 兼容性测试
 */
class ThinkOrm4CompatibilityTest extends TestCase
{
    /**
     * 测试 RuleModel 实例化不会触发 WeakMap 错误
     *
     * @return void
     */
    public function testRuleModelInstantiation(): void
    {
        // 测试空数据实例化
        $model1 = new RuleModel();
        $this->assertInstanceOf(RuleModel::class, $model1);

        // 测试带数据实例化
        $model2 = new RuleModel([
            'ptype' => 'p',
            'v0' => 'alice',
            'v1' => 'data1',
            'v2' => 'read'
        ]);
        $this->assertInstanceOf(RuleModel::class, $model2);

        // 测试带 driver 参数实例化
        $model3 = new RuleModel([], 'default');
        $this->assertInstanceOf(RuleModel::class, $model3);
    }

    /**
     * 测试属性访问不会触发 WeakMap 错误
     *
     * @return void
     */
    public function testPropertyAccess(): void
    {
        $model = new RuleModel([
            'ptype' => 'p',
            'v0' => 'bob',
            'v1' => 'data2',
            'v2' => 'write'
        ]);

        // 测试读取属性
        $this->assertEquals('p', $model->ptype);
        $this->assertEquals('bob', $model->v0);
        $this->assertEquals('data2', $model->v1);
        $this->assertEquals('write', $model->v2);

        // 测试设置属性
        $model->v3 = 'allow';
        $this->assertEquals('allow', $model->v3);
    }

    /**
     * 测试 schema 定义正确
     *
     * @return void
     */
    public function testSchemaDefinition(): void
    {
        $model = new RuleModel();
        $schema = $model->getSchema();

        $expectedSchema = [
            'id'    => 'int',
            'ptype' => 'string',
            'v0'    => 'string',
            'v1'    => 'string',
            'v2'    => 'string',
            'v3'    => 'string',
            'v4'    => 'string',
            'v5'    => 'string'
        ];

        $this->assertEquals($expectedSchema, $schema);
    }

    /**
     * 测试批量赋值
     *
     * @return void
     */
    public function testMassAssignment(): void
    {
        $data = [
            'ptype' => 'g',
            'v0' => 'alice',
            'v1' => 'admin'
        ];

        $model = new RuleModel($data);

        $this->assertEquals('g', $model->ptype);
        $this->assertEquals('alice', $model->v0);
        $this->assertEquals('admin', $model->v1);
    }
}
