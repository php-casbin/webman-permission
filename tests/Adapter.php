<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Permission;

trait Adapter
{
    public function testAddOtherPolicy()
    {
        // var_dump(config('plugin.casbin.webman-permission.permission'));
        $this->assertTrue(Permission::client('other')->addPolicy('writer', 'articles', 'edit'));
        $this->assertTrue(Permission::client('other')->addPolicies([
            ['writer', 'articles', 'list'],
            ['writer', 'articles', 'delete'],
        ]));

        $this->assertFalse(Permission::client('other')->addPolicies([
            ['writer', 'articles', 'list'],
            ['writer', 'articles', 'delete'],
        ]));

        $this->assertTrue(Permission::client('other')->enforce('writer', 'articles', 'edit'));
        $this->assertTrue(Permission::client('other')->enforce('writer', 'articles', 'delete'));
        $this->assertFalse(Permission::client('other')->enforce('writer', 'articles', 'other'));

        $this->assertTrue(Permission::client('other')->hasPolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::client('other')->hasPolicy('writer', 'articles', 'other'));

        $this->assertTrue(Permission::client('other')->removePolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::client('other')->hasPolicy('writer', 'articles', 'edit'));
        $this->assertFalse(Permission::client('other')->enforce('writer', 'articles', 'edit'));
    }

    public function testAddOtherRoleForUser()
    {
        $this->assertFalse(Permission::client('other')->hasRoleForUser('eve', 'data2'));
        Permission::client('other')->addRoleForUser('eve', 'data2');
        $this->assertTrue(in_array('data2', Permission::client('other')->getAllRoles()));
        $this->assertTrue(Permission::client('other')->hasRoleForUser('eve', 'data2'));
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
        $this->assertFalse(Permission::client('other')->enforce('eve', 'data1', 'read'));
        Permission::client('other')->addPermissionForUser('eve', 'data1', 'read');
        $this->assertTrue(Permission::client('other')->enforce('eve', 'data1', 'read'));
    }
}
