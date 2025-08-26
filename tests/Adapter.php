<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Permission;

trait Adapter
{
    public function testAddOtherPolicy()
    {
        var_dump(config('plugin.casbin.webman-permission.permission'));
        $this->assertTrue(Permission::driver('other')->addPolicy('writer', 'articles', 'edit'));
        $this->assertTrue(Permission::driver('other')->addPolicies([
            ['writer', 'articles', 'list'],
            ['writer', 'articles', 'delete'],
        ]));

        $this->assertFalse(Permission::driver('other')->addPolicies([
            ['writer', 'articles', 'list'],
            ['writer', 'articles', 'delete'],
        ]));

        $this->assertTrue(Permission::driver('other')->enforce('writer', 'articles', 'edit'));
        $this->assertTrue(Permission::driver('other')->enforce('writer', 'articles', 'delete'));
        $this->assertFalse(Permission::driver('other')->enforce('writer', 'articles', 'other'));

        $this->assertTrue(Permission::driver('other')->hasPolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::driver('other')->hasPolicy('writer', 'articles', 'other'));

        $this->assertTrue(Permission::driver('other')->removePolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::driver('other')->hasPolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::driver('other')->enforce('writer', 'articles', 'edit'));
    }

    public function testAddOtherRoleForUser()
    {
        $this->assertFalse(Permission::driver('other')->hasRoleForUser('eve', 'data2'));
        Permission::driver('other')->addRoleForUser('eve', 'data2');
        $this->assertTrue(in_array('data2', Permission::driver('other')->getAllRoles()));
        $this->assertTrue(Permission::driver('other')->hasRoleForUser('eve', 'data2'));
    }

    public function testAddPermissionForUser()
    {
        $this->assertFalse(Permission::enforce('eve', 'data1', 'read'));
        Permission::addPermissionForUser('eve', 'data1', 'read');
        $this->assertTrue(Permission::enforce('eve', 'data1', 'read'));
    }

    public function testAddPolicy()
    {
        $this->assertTrue(Permission::addPolicy('writer', 'articles', 'edit'));
        $this->assertTrue(Permission::addPolicies([
            ['writer', 'articles', 'list'],
            ['writer', 'articles', 'delete'],
        ]));

        $this->assertFalse(Permission::addPolicies([
            ['writer', 'articles', 'list'],
            ['writer', 'articles', 'delete'],
        ]));

        $this->assertTrue(Permission::enforce('writer', 'articles', 'edit'));
        $this->assertTrue(Permission::enforce('writer', 'articles', 'delete'));
        $this->assertFalse(Permission::enforce('writer', 'articles', 'other'));

        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'other'));

        $this->assertTrue(Permission::removePolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::enforce('writer', 'articles', 'edit'));
    }

    public function testAddRoleForUser()
    {
        $this->assertFalse(Permission::hasRoleForUser('eve', 'data2'));
        Permission::addRoleForUser('eve', 'data2');
        $this->assertTrue(in_array('data2', Permission::getAllRoles()));
        $this->assertTrue(Permission::hasRoleForUser('eve', 'data2'));
    }

    public function testOtherAddPermissionForUser()
    {
        $this->assertFalse(Permission::driver('other')->enforce('eve', 'data1', 'read'));
        Permission::driver('other')->addPermissionForUser('eve', 'data1', 'read');
        $this->assertTrue(Permission::driver('other')->enforce('eve', 'data1', 'read'));
    }

    public function testUpdatePolicy()
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

    public function testRemovePolicies()
    {
        Permission::addPolicies([
            ['writer', 'articles', 'list'],
            ['writer', 'articles', 'delete']
        ]);
        
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'list'));
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'delete'));
        
        $result = Permission::removePolicies([
            ['writer', 'articles', 'list'],
            ['writer', 'articles', 'delete']
        ]);
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'list'));
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'delete'));
    }

    public function testGetRolesForUser()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('alice', 'editor');
        
        $roles = Permission::getRolesForUser('alice');
        $this->assertContains('admin', $roles);
        $this->assertContains('editor', $roles);
    }

    public function testGetUsersForRole()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('bob', 'admin');
        
        $users = Permission::getUsersForRole('admin');
        $this->assertContains('alice', $users);
        $this->assertContains('bob', $users);
    }

    public function testDeleteRoleForUser()
    {
        Permission::addRoleForUser('alice', 'admin');
        $this->assertTrue(Permission::hasRoleForUser('alice', 'admin'));
        
        $result = Permission::deleteRoleForUser('alice', 'admin');
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasRoleForUser('alice', 'admin'));
    }

    public function testDeletePermissionForUser()
    {
        Permission::addPermissionForUser('alice', 'data1', 'read');
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read'));
        
        $result = Permission::deletePermissionForUser('alice', 'data1', 'read');
        $this->assertTrue($result);
        $this->assertFalse(Permission::enforce('alice', 'data1', 'read'));
    }

    public function testGetPermissionsForUser()
    {
        Permission::addPermissionForUser('alice', 'data1', 'read');
        Permission::addPermissionForUser('alice', 'data2', 'write');
        
        $permissions = Permission::getPermissionsForUser('alice');
        $this->assertContains(['alice', 'data1', 'read'], $permissions);
        $this->assertContains(['alice', 'data2', 'write'], $permissions);
    }

    public function testHasPermissionForUser()
    {
        Permission::addPermissionForUser('alice', 'data1', 'read');
        $this->assertTrue(Permission::hasPermissionForUser('alice', 'data1', 'read'));
        $this->assertFalse(Permission::hasPermissionForUser('alice', 'data1', 'write'));
    }

    public function testGetImplicitRolesForUser()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('admin', 'super_admin');
        
        $roles = Permission::getImplicitRolesForUser('alice');
        $this->assertContains('admin', $roles);
        $this->assertContains('super_admin', $roles);
    }

    public function testGetImplicitPermissionsForUser()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addPermissionForUser('admin', 'data1', 'read');
        
        $permissions = Permission::getImplicitPermissionsForUser('alice');
        $this->assertContains(['admin', 'data1', 'read'], $permissions);
    }

    public function testDeleteUser()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addPermissionForUser('alice', 'data1', 'read');
        
        $result = Permission::deleteUser('alice');
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasRoleForUser('alice', 'admin'));
        $this->assertFalse(Permission::enforce('alice', 'data1', 'read'));
    }

    public function testDeleteRole()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('bob', 'admin');
        
        $result = Permission::deleteRole('admin');
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasRoleForUser('alice', 'admin'));
        $this->assertFalse(Permission::hasRoleForUser('bob', 'admin'));
    }

    public function testDriverManager()
    {
        $defaultDriver = Permission::getDefaultDriver();
        $this->assertNotEmpty($defaultDriver);
        
        $allDrivers = Permission::getAllDriver();
        $this->assertIsArray($allDrivers);
    }

    public function testEnforceWithInvalidData()
    {
        $this->assertFalse(Permission::enforce('', '', ''));
        $this->assertFalse(Permission::enforce('nonexistent', 'resource', 'action'));
    }

    public function testDuplicatePolicyHandling()
    {
        $result1 = Permission::addPolicy('writer', 'articles', 'edit');
        $this->assertTrue($result1);
        
        $result2 = Permission::addPolicy('writer', 'articles', 'edit');
        $this->assertFalse($result2);
    }

    public function testPolicyWithEmptyValues()
    {
        Permission::addPolicy('writer', 'articles', '');
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', ''));
        
        $result = Permission::removePolicy('writer', 'articles', '');
        $this->assertTrue($result);
    }

    public function testLargePolicySet()
    {
        $policies = [];
        for ($i = 0; $i < 100; $i++) {
            $policies[] = ['user' . $i, 'resource' . $i, 'action' . $i];
        }
        
        $result = Permission::addPolicies($policies);
        $this->assertTrue($result);
        
        for ($i = 0; $i < 100; $i++) {
            $this->assertTrue(Permission::enforce('user' . $i, 'resource' . $i, 'action' . $i));
        }
    }

    public function testRemoveFilteredPolicy()
    {
        Permission::addPolicies([
            ['alice', 'data1', 'read'],
            ['alice', 'data2', 'read'],
            ['bob', 'data1', 'read']
        ]);
        
        $result = Permission::removeFilteredPolicy(1, 'p', 0, 'alice');
        $this->assertTrue($result);
        
        $this->assertFalse(Permission::enforce('alice', 'data1', 'read'));
        $this->assertFalse(Permission::enforce('alice', 'data2', 'read'));
        $this->assertTrue(Permission::enforce('bob', 'data1', 'read'));
    }

    public function testConfigAccess()
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
}
