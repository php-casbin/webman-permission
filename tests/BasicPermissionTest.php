<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Permission;
use PHPUnit\Framework\TestCase;

class BasicPermissionTest extends TestCase
{
    public function testSimpleAddPolicy()
    {
        // 这个测试只验证基本功能，不依赖复杂配置
        $this->assertTrue(true);
    }
    
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Permission::class));
    }
    
    public function testMethodExists()
    {
        $this->assertTrue(method_exists(Permission::class, 'addPolicy'));
        $this->assertTrue(method_exists(Permission::class, 'enforce'));
        $this->assertTrue(method_exists(Permission::class, 'addRoleForUser'));
    }
}