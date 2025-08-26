<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Permission;
use Casbin\WebmanPermission\Adapter\DatabaseAdapter;
use Casbin\WebmanPermission\Adapter\LaravelDatabaseAdapter;
use Casbin\Exceptions\CasbinException;
use Casbin\Persist\Adapters\Filter;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
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

    public function testDriverMethod()
    {
        $enforcer = Permission::driver();
        $this->assertInstanceOf(\Casbin\Enforcer::class, $enforcer);
        
        $otherEnforcer = Permission::driver('other');
        $this->assertInstanceOf(\Casbin\Enforcer::class, $otherEnforcer);
    }

    public function testStaticMethodCall()
    {
        $result = Permission::addPolicy('writer', 'articles', 'edit');
        $this->assertTrue($result);
        
        $result = Permission::enforce('writer', 'articles', 'edit');
        $this->assertTrue($result);
    }

    public function testGetAllDriver()
    {
        Permission::driver();
        Permission::driver('other');
        
        $drivers = Permission::getAllDriver();
        $this->assertIsArray($drivers);
        $this->assertGreaterThanOrEqual(2, count($drivers));
    }

    public function testGetDefaultDriver()
    {
        $driver = Permission::getDefaultDriver();
        $this->assertNotEmpty($driver);
    }

    public function testGetConfig()
    {
        $config = Permission::getConfig('default');
        $this->assertNotEmpty($config);
        
        $allConfig = Permission::getConfig();
        $this->assertNotEmpty($allConfig);
    }

    public function testClear()
    {
        Permission::addPolicy('writer', 'articles', 'edit');
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'edit'));
        
        Permission::clear();
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'edit'));
    }

    public function testAddFunction()
    {
        $result = Permission::addFunction('test_function', function($a, $b) {
            return $a + $b;
        });
        
        $this->assertTrue($result);
    }

    public function testDomainBasedPermissions()
    {
        Permission::addRoleForUserInDomain('alice', 'admin', 'domain1');
        $this->assertTrue(Permission::hasRoleForUser('alice', 'admin', 'domain1'));
        
        $roles = Permission::getRolesForUserInDomain('alice', 'domain1');
        $this->assertContains('admin', $roles);
        
        $users = Permission::getUsersForRoleInDomain('admin', 'domain1');
        $this->assertContains('alice', $users);
    }

    public function testDomainBasedPermissionOperations()
    {
        Permission::addPermissionForUser('alice', 'data1', 'read', 'domain1');
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read', 'domain1'));
        
        $permissions = Permission::getPermissionsForUserInDomain('alice', 'domain1');
        $this->assertContains(['alice', 'data1', 'read'], $permissions);
    }

    public function testDomainRoleDeletion()
    {
        Permission::addRoleForUserInDomain('alice', 'admin', 'domain1');
        $this->assertTrue(Permission::hasRoleForUser('alice', 'admin', 'domain1'));
        
        $result = Permission::deleteRoleForUserInDomain('alice', 'admin', 'domain1');
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasRoleForUser('alice', 'admin', 'domain1'));
    }

    public function testDeleteRolesForUserInDomain()
    {
        Permission::addRoleForUserInDomain('alice', 'admin', 'domain1');
        Permission::addRoleForUserInDomain('alice', 'editor', 'domain1');
        
        $result = Permission::deleteRolesForUserInDomain('alice', 'domain1');
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasRoleForUser('alice', 'admin', 'domain1'));
        $this->assertFalse(Permission::hasRoleForUser('alice', 'editor', 'domain1'));
    }

    public function testGetAllUsersByDomain()
    {
        Permission::addRoleForUserInDomain('alice', 'admin', 'domain1');
        Permission::addRoleForUserInDomain('bob', 'admin', 'domain1');
        
        $users = Permission::getAllUsersByDomain('domain1');
        $this->assertContains('alice', $users);
        $this->assertContains('bob', $users);
    }

    public function testDeleteAllUsersByDomain()
    {
        Permission::addRoleForUserInDomain('alice', 'admin', 'domain1');
        Permission::addRoleForUserInDomain('bob', 'admin', 'domain1');
        
        $result = Permission::deleteAllUsersByDomain('domain1');
        $this->assertTrue($result);
        $this->assertEmpty(Permission::getAllUsersByDomain('domain1'));
    }

    public function testDeleteDomains()
    {
        Permission::addRoleForUserInDomain('alice', 'admin', 'domain1');
        Permission::addRoleForUserInDomain('bob', 'admin', 'domain2');
        
        $result = Permission::deleteDomains('domain1', 'domain2');
        $this->assertTrue($result);
        $this->assertEmpty(Permission::getAllUsersByDomain('domain1'));
        $this->assertEmpty(Permission::getAllUsersByDomain('domain2'));
    }

    public function testGetImplicitUsersForRole()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('admin', 'super_admin');
        
        $users = Permission::getImplicitUsersForRole('super_admin');
        $this->assertContains('alice', $users);
    }

    public function testGetImplicitUsersForPermission()
    {
        Permission::addPermissionForUser('alice', 'data1', 'read');
        Permission::addRoleForUser('bob', 'admin');
        Permission::addPermissionForUser('admin', 'data1', 'read');
        
        $users = Permission::getImplicitUsersForPermission('data1', 'read');
        $this->assertContains('alice', $users);
        $this->assertContains('bob', $users);
    }

    public function testGetImplicitResourcesForUser()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addPermissionForUser('admin', 'data1', 'read');
        Permission::addPermissionForUser('admin', 'data2', 'write');
        
        $resources = Permission::getImplicitResourcesForUser('alice');
        $this->assertContains(['data1', 'read'], $resources);
        $this->assertContains(['data2', 'write'], $resources);
    }

    public function testBatchRoleOperations()
    {
        $result = Permission::addRolesForUser('alice', ['admin', 'editor']);
        $this->assertTrue($result);
        $this->assertTrue(Permission::hasRoleForUser('alice', 'admin'));
        $this->assertTrue(Permission::hasRoleForUser('alice', 'editor'));
    }

    public function testBatchPermissionOperations()
    {
        $result = Permission::addPermissionsForUser('alice', [
            ['data1', 'read'],
            ['data2', 'write']
        ]);
        $this->assertTrue($result);
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('alice', 'data2', 'write'));
    }

    public function testDeletePermissionsForUser()
    {
        Permission::addPermissionForUser('alice', 'data1', 'read');
        Permission::addPermissionForUser('alice', 'data2', 'write');
        
        $result = Permission::deletePermissionsForUser('alice');
        $this->assertTrue($result);
        $this->assertFalse(Permission::enforce('alice', 'data1', 'read'));
        $this->assertFalse(Permission::enforce('alice', 'data2', 'write'));
    }

    public function testDeleteRolesForUser()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('alice', 'editor');
        
        $result = Permission::deleteRolesForUser('alice');
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasRoleForUser('alice', 'admin'));
        $this->assertFalse(Permission::hasRoleForUser('alice', 'editor'));
    }

    public function testGetAllRoles()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('bob', 'editor');
        
        $roles = Permission::getAllRoles();
        $this->assertContains('admin', $roles);
        $this->assertContains('editor', $roles);
    }

    public function testGetPolicy()
    {
        Permission::addPolicy('writer', 'articles', 'edit');
        Permission::addPolicy('reader', 'articles', 'read');
        
        $policy = Permission::getPolicy();
        $this->assertContains(['writer', 'articles', 'edit'], $policy);
        $this->assertContains(['reader', 'articles', 'read'], $policy);
    }

    public function testPermissionWithSpecialCharacters()
    {
        Permission::addPolicy('user@domain.com', 'data#1', 'action:read');
        $this->assertTrue(Permission::enforce('user@domain.com', 'data#1', 'action:read'));
    }

    public function testEmptyPolicyOperations()
    {
        $result = Permission::removePolicy('nonexistent', 'resource', 'action');
        $this->assertTrue($result);
        
        $result = Permission::removePolicies([
            ['nonexistent', 'resource', 'action']
        ]);
        $this->assertTrue($result);
    }

    public function testConfigWithDefaults()
    {
        $config = Permission::getConfig('nonexistent', 'default_value');
        $this->assertEquals('default_value', $config);
    }

    public function testDriverCaching()
    {
        $driver1 = Permission::driver();
        $driver2 = Permission::driver();
        
        $this->assertSame($driver1, $driver2);
    }

    public function testInvalidDriverAccess()
    {
        $this->expectException(CasbinException::class);
        Permission::driver('nonexistent_driver');
    }
}