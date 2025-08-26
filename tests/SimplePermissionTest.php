<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Permission;
use Casbin\WebmanPermission\Adapter\DatabaseAdapter;
use Casbin\WebmanPermission\Adapter\LaravelDatabaseAdapter;
use Casbin\Exceptions\CasbinException;
use PHPUnit\Framework\TestCase;

class SimplePermissionTest extends TestCase
{
    protected function setUp(): void
    {
        // 模拟配置
        global $config;
        $config = [
            'plugin' => [
                'casbin' => [
                    'webman-permission' => [
                        'permission' => [
                            'default' => 'default',
                            'default' => [
                                'model' => [
                                    'config_type' => 'text',
                                    'config_text' => '
[request_definition]
r = sub, obj, act

[policy_definition]
p = sub, obj, act

[role_definition]
g = _, _

[policy_effect]
e = some(where (p.eft == allow))

[matchers]
m = g(r.sub, p.sub) && r.obj == p.obj && r.act == p.act
                                    ',
                                ],
                                'adapter' => \Casbin\WebmanPermission\Adapter\DatabaseAdapter::class,
                            ],
                            'other' => [
                                'model' => [
                                    'config_type' => 'text',
                                    'config_text' => '
[request_definition]
r = sub, obj, act

[policy_definition]
p = sub, obj, act

[role_definition]
g = _, _

[policy_effect]
e = some(where (p.eft == allow))

[matchers]
m = g(r.sub, p.sub) && r.obj == p.obj && r.act == p.act
                                    ',
                                ],
                                'adapter' => \Casbin\WebmanPermission\Adapter\DatabaseAdapter::class,
                                'adapter_config' => [
                                    'table' => 'other_casbin_rule'
                                ],
                            ],
                            'log' => [
                                'enabled' => false,
                                'logger' => 'casbin',
                                'path' => '/tmp/casbin.log',
                            ],
                        ]
                    ]
                ]
            ]
        ];
        
        Permission::clear();
    }

    public function testBasicPermission()
    {
        $result = Permission::addPolicy('writer', 'articles', 'edit');
        $this->assertTrue($result);
        
        $result = Permission::enforce('writer', 'articles', 'edit');
        $this->assertTrue($result);
        
        $result = Permission::enforce('writer', 'articles', 'delete');
        $this->assertFalse($result);
    }

    public function testRoleManagement()
    {
        $result = Permission::addRoleForUser('alice', 'admin');
        $this->assertTrue($result);
        
        $result = Permission::hasRoleForUser('alice', 'admin');
        $this->assertTrue($result);
        
        $roles = Permission::getRolesForUser('alice');
        $this->assertContains('admin', $roles);
    }

    public function testPermissionForUser()
    {
        $result = Permission::addPermissionForUser('alice', 'data1', 'read');
        $this->assertTrue($result);
        
        $result = Permission::enforce('alice', 'data1', 'read');
        $this->assertTrue($result);
        
        $permissions = Permission::getPermissionsForUser('alice');
        $this->assertContains(['alice', 'data1', 'read'], $permissions);
    }

    public function testBatchOperations()
    {
        $policies = [
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write']
        ];
        
        $result = Permission::addPolicies($policies);
        $this->assertTrue($result);
        
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('bob', 'data2', 'write'));
    }

    public function testPolicyUpdate()
    {
        Permission::addPolicy('writer', 'articles', 'edit');
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'edit'));
        
        $result = Permission::updatePolicies(
            [['writer', 'articles', 'edit']],
            [['writer', 'articles', 'update']]
        );
        $this->assertTrue($result);
        
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'edit'));
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'update'));
    }

    public function testRemoveOperations()
    {
        Permission::addPolicy('writer', 'articles', 'edit');
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'edit'));
        
        $result = Permission::removePolicy('writer', 'articles', 'edit');
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'edit'));
    }

    public function testDriverManagement()
    {
        $driver = Permission::getDefaultDriver();
        $this->assertNotEmpty($driver);
        
        $drivers = Permission::getAllDriver();
        $this->assertIsArray($drivers);
    }

    public function testClear()
    {
        Permission::addPolicy('writer', 'articles', 'edit');
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'edit'));
        
        Permission::clear();
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'edit'));
    }

    protected function tearDown(): void
    {
        Permission::clear();
    }
}