<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Permission;
use Casbin\WebmanPermission\Adapter\DatabaseAdapter;
use Casbin\WebmanPermission\Adapter\LaravelDatabaseAdapter;
use Casbin\WebmanPermission\Watcher\RedisWatcher;
use Casbin\Exceptions\CasbinException;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        Permission::clear();
    }

    public function testCompleteRBACWorkflow()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('bob', 'user');
        Permission::addRoleForUser('charlie', 'guest');
        
        Permission::addPermissionForUser('admin', 'data1', 'read');
        Permission::addPermissionForUser('admin', 'data1', 'write');
        Permission::addPermissionForUser('admin', 'data1', 'delete');
        
        Permission::addPermissionForUser('user', 'data1', 'read');
        Permission::addPermissionForUser('user', 'data2', 'read');
        
        Permission::addPermissionForUser('guest', 'data2', 'read');
        
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('alice', 'data1', 'write'));
        $this->assertTrue(Permission::enforce('alice', 'data1', 'delete'));
        
        $this->assertTrue(Permission::enforce('bob', 'data1', 'read'));
        $this->assertFalse(Permission::enforce('bob', 'data1', 'write'));
        $this->assertTrue(Permission::enforce('bob', 'data2', 'read'));
        
        $this->assertFalse(Permission::enforce('charlie', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('charlie', 'data2', 'read'));
        
        $adminRoles = Permission::getRolesForUser('alice');
        $this->assertContains('admin', $adminRoles);
        
        $adminUsers = Permission::getUsersForRole('admin');
        $this->assertContains('alice', $adminUsers);
        
        $alicePermissions = Permission::getPermissionsForUser('alice');
        $this->assertContains(['admin', 'data1', 'read'], $alicePermissions);
        $this->assertContains(['admin', 'data1', 'write'], $alicePermissions);
    }

    public function testHierarchicalRBAC()
    {
        Permission::addRoleForUser('alice', 'user');
        Permission::addRoleForUser('user', 'admin');
        Permission::addRoleForUser('admin', 'super_admin');
        
        Permission::addPermissionForUser('super_admin', '*', '*');
        
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('alice', 'data2', 'write'));
        
        $implicitRoles = Permission::getImplicitRolesForUser('alice');
        $this->assertContains('user', $implicitRoles);
        $this->assertContains('admin', $implicitRoles);
        $this->assertContains('super_admin', $implicitRoles);
        
        $implicitPermissions = Permission::getImplicitPermissionsForUser('alice');
        $this->assertContains(['super_admin', '*', '*'], $implicitPermissions);
    }

    public function testDomainBasedRBAC()
    {
        Permission::addRoleForUserInDomain('alice', 'admin', 'domain1');
        Permission::addRoleForUserInDomain('alice', 'user', 'domain2');
        Permission::addRoleForUserInDomain('bob', 'admin', 'domain2');
        
        Permission::addPermissionForUser('admin', 'data1', 'read', 'domain1');
        Permission::addPermissionForUser('admin', 'data2', 'write', 'domain2');
        
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read', 'domain1'));
        $this->assertFalse(Permission::enforce('alice', 'data1', 'read', 'domain2'));
        
        $this->assertFalse(Permission::enforce('alice', 'data2', 'write', 'domain1'));
        $this->assertTrue(Permission::enforce('alice', 'data2', 'write', 'domain2'));
        
        $this->assertTrue(Permission::enforce('bob', 'data2', 'write', 'domain2'));
        $this->assertFalse(Permission::enforce('bob', 'data1', 'read', 'domain1'));
        
        $domain1Users = Permission::getAllUsersByDomain('domain1');
        $this->assertContains('alice', $domain1Users);
        
        $domain2Users = Permission::getAllUsersByDomain('domain2');
        $this->assertContains('alice', $domain2Users);
        $this->assertContains('bob', $domain2Users);
    }

    public function testResourceBasedAccessControl()
    {
        Permission::addPolicy('writer', 'article_1', 'edit');
        Permission::addPolicy('writer', 'article_2', 'edit');
        Permission::addPolicy('reader', 'article_1', 'read');
        Permission::addPolicy('reader', 'article_2', 'read');
        
        Permission::addRoleForUser('alice', 'writer');
        Permission::addRoleForUser('bob', 'reader');
        
        $this->assertTrue(Permission::enforce('alice', 'article_1', 'edit'));
        $this->assertTrue(Permission::enforce('alice', 'article_2', 'edit'));
        $this->assertTrue(Permission::enforce('alice', 'article_1', 'read'));
        
        $this->assertTrue(Permission::enforce('bob', 'article_1', 'read'));
        $this->assertTrue(Permission::enforce('bob', 'article_2', 'read'));
        $this->assertFalse(Permission::enforce('bob', 'article_1', 'edit'));
    }

    public function testDynamicPermissionAssignment()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('bob', 'user');
        
        $this->assertFalse(Permission::enforce('alice', 'new_resource', 'read'));
        $this->assertFalse(Permission::enforce('bob', 'new_resource', 'read'));
        
        Permission::addPermissionForUser('admin', 'new_resource', 'read');
        
        $this->assertTrue(Permission::enforce('alice', 'new_resource', 'read'));
        $this->assertFalse(Permission::enforce('bob', 'new_resource', 'read'));
        
        Permission::deletePermissionForUser('admin', 'new_resource', 'read');
        
        $this->assertFalse(Permission::enforce('alice', 'new_resource', 'read'));
        $this->assertFalse(Permission::enforce('bob', 'new_resource', 'read'));
    }

    public function testBatchOperations()
    {
        $users = ['alice', 'bob', 'charlie', 'david'];
        $roles = ['admin', 'editor', 'writer', 'reader'];
        
        foreach ($users as $index => $user) {
            Permission::addRoleForUser($user, $roles[$index]);
        }
        
        $policies = [
            ['admin', '*', '*'],
            ['editor', 'articles', 'edit'],
            ['writer', 'articles', 'write'],
            ['reader', 'articles', 'read']
        ];
        
        Permission::addPolicies($policies);
        
        $this->assertTrue(Permission::enforce('alice', 'articles', 'edit'));
        $this->assertTrue(Permission::enforce('bob', 'articles', 'edit'));
        $this->assertTrue(Permission::enforce('charlie', 'articles', 'write'));
        $this->assertTrue(Permission::enforce('david', 'articles', 'read'));
        
        Permission::removePolicies($policies);
        
        $this->assertFalse(Permission::enforce('alice', 'articles', 'edit'));
        $this->assertFalse(Permission::enforce('bob', 'articles', 'edit'));
        $this->assertFalse(Permission::enforce('charlie', 'articles', 'write'));
        $this->assertFalse(Permission::enforce('david', 'articles', 'read'));
    }

    public function testUserLifecycle()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('alice', 'editor');
        Permission::addPermissionForUser('alice', 'data1', 'read');
        Permission::addPermissionForUser('alice', 'data2', 'write');
        
        $this->assertTrue(Permission::hasRoleForUser('alice', 'admin'));
        $this->assertTrue(Permission::hasRoleForUser('alice', 'editor'));
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('alice', 'data2', 'write'));
        
        $result = Permission::deleteUser('alice');
        $this->assertTrue($result);
        
        $this->assertFalse(Permission::hasRoleForUser('alice', 'admin'));
        $this->assertFalse(Permission::hasRoleForUser('alice', 'editor'));
        $this->assertFalse(Permission::enforce('alice', 'data1', 'read'));
        $this->assertFalse(Permission::enforce('alice', 'data2', 'write'));
    }

    public function testRoleLifecycle()
    {
        Permission::addRoleForUser('alice', 'admin');
        Permission::addRoleForUser('bob', 'admin');
        Permission::addRoleForUser('charlie', 'admin');
        
        Permission::addPermissionForUser('admin', 'data1', 'read');
        Permission::addPermissionForUser('admin', 'data2', 'write');
        
        $this->assertTrue(Permission::hasRoleForUser('alice', 'admin'));
        $this->assertTrue(Permission::hasRoleForUser('bob', 'admin'));
        $this->assertTrue(Permission::hasRoleForUser('charlie', 'admin'));
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('bob', 'data2', 'write'));
        
        $result = Permission::deleteRole('admin');
        $this->assertTrue($result);
        
        $this->assertFalse(Permission::hasRoleForUser('alice', 'admin'));
        $this->assertFalse(Permission::hasRoleForUser('bob', 'admin'));
        $this->assertFalse(Permission::hasRoleForUser('charlie', 'admin'));
        $this->assertFalse(Permission::enforce('alice', 'data1', 'read'));
        $this->assertFalse(Permission::enforce('bob', 'data2', 'write'));
    }

    public function testMultiDriverIntegration()
    {
        Permission::driver('default')->addRoleForUser('alice', 'admin');
        Permission::driver('other')->addRoleForUser('bob', 'admin');
        
        $this->assertTrue(Permission::driver('default')->hasRoleForUser('alice', 'admin'));
        $this->assertFalse(Permission::driver('default')->hasRoleForUser('bob', 'admin'));
        
        $this->assertFalse(Permission::driver('other')->hasRoleForUser('alice', 'admin'));
        $this->assertTrue(Permission::driver('other')->hasRoleForUser('bob', 'admin'));
        
        Permission::driver('default')->addPermissionForUser('admin', 'data1', 'read');
        Permission::driver('other')->addPermissionForUser('admin', 'data2', 'read');
        
        $this->assertTrue(Permission::driver('default')->enforce('alice', 'data1', 'read'));
        $this->assertFalse(Permission::driver('default')->enforce('alice', 'data2', 'read'));
        
        $this->assertFalse(Permission::driver('other')->enforce('bob', 'data1', 'read'));
        $this->assertTrue(Permission::driver('other')->enforce('bob', 'data2', 'read'));
    }

    public function testPolicyUpdateWorkflow()
    {
        Permission::addPolicy('writer', 'articles', 'edit');
        Permission::addPolicy('writer', 'articles', 'delete');
        
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'edit'));
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'delete'));
        
        $result = Permission::updatePolicies(
            [['writer', 'articles', 'edit'], ['writer', 'articles', 'delete']],
            [['writer', 'articles', 'update'], ['writer', 'articles', 'remove']]
        );
        
        $this->assertTrue($result);
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::hasPolicy('writer', 'articles', 'delete'));
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'update'));
        $this->assertTrue(Permission::hasPolicy('writer', 'articles', 'remove'));
    }

    public function testFilteredPolicyManagement()
    {
        Permission::addPolicies([
            ['alice', 'data1', 'read'],
            ['alice', 'data2', 'read'],
            ['alice', 'data3', 'write'],
            ['bob', 'data1', 'read'],
            ['bob', 'data2', 'write']
        ]);
        
        $result = Permission::removeFilteredPolicy(1, 'p', 0, 'alice');
        $this->assertTrue($result);
        
        $this->assertFalse(Permission::enforce('alice', 'data1', 'read'));
        $this->assertFalse(Permission::enforce('alice', 'data2', 'read'));
        $this->assertFalse(Permission::enforce('alice', 'data3', 'write'));
        $this->assertTrue(Permission::enforce('bob', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('bob', 'data2', 'write'));
        
        $result = Permission::removeFilteredPolicy(1, 'p', 2, 'read');
        $this->assertTrue($result);
        
        $this->assertFalse(Permission::enforce('bob', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('bob', 'data2', 'write'));
    }

    public function testPermissionInheritance()
    {
        Permission::addRoleForUser('alice', 'user');
        Permission::addRoleForUser('user', 'admin');
        Permission::addRoleForUser('admin', 'super_admin');
        
        Permission::addPermissionForUser('user', 'data1', 'read');
        Permission::addPermissionForUser('admin', 'data2', 'write');
        Permission::addPermissionForUser('super_admin', 'data3', 'delete');
        
        $this->assertTrue(Permission::enforce('alice', 'data1', 'read'));
        $this->assertTrue(Permission::enforce('alice', 'data2', 'write'));
        $this->assertTrue(Permission::enforce('alice', 'data3', 'delete'));
        
        $implicitUsers = Permission::getImplicitUsersForPermission('data1', 'read');
        $this->assertContains('alice', $implicitUsers);
        
        $implicitUsers = Permission::getImplicitUsersForPermission('data2', 'write');
        $this->assertContains('alice', $implicitUsers);
        
        $implicitUsers = Permission::getImplicitUsersForPermission('data3', 'delete');
        $this->assertContains('alice', $implicitUsers);
    }

    public function testComplexDomainScenario()
    {
        $domains = ['sales', 'marketing', 'hr'];
        $users = ['alice', 'bob', 'charlie'];
        
        foreach ($domains as $domain) {
            foreach ($users as $user) {
                Permission::addRoleForUserInDomain($user, 'manager', $domain);
                Permission::addPermissionForUser('manager', 'reports', 'read', $domain);
            }
        }
        
        $this->assertTrue(Permission::enforce('alice', 'reports', 'read', 'sales'));
        $this->assertTrue(Permission::enforce('bob', 'reports', 'read', 'marketing'));
        $this->assertTrue(Permission::enforce('charlie', 'reports', 'read', 'hr'));
        
        $this->assertFalse(Permission::enforce('alice', 'reports', 'read', 'marketing'));
        $this->assertFalse(Permission::enforce('bob', 'reports', 'read', 'hr'));
        $this->assertFalse(Permission::enforce('charlie', 'reports', 'read', 'sales'));
        
        $result = Permission::deleteAllUsersByDomain('sales');
        $this->assertTrue($result);
        
        $this->assertFalse(Permission::enforce('alice', 'reports', 'read', 'sales'));
        $this->assertTrue(Permission::enforce('bob', 'reports', 'read', 'marketing'));
        $this->assertTrue(Permission::enforce('charlie', 'reports', 'read', 'hr'));
    }

    protected function tearDown(): void
    {
        Permission::clear();
    }
}