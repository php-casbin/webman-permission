<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Permission;

trait Adapter
{
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
}
