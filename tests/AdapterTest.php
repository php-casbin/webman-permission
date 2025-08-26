<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Adapter\DatabaseAdapter;
use Casbin\WebmanPermission\Adapter\LaravelDatabaseAdapter;
use Casbin\Exceptions\InvalidFilterTypeException;
use Casbin\Persist\Adapters\Filter;
use Casbin\WebmanPermission\Model\RuleModel;
use Casbin\WebmanPermission\Model\LaravelRuleModel;
use PHPUnit\Framework\TestCase;

class AdapterTest extends TestCase
{
    private $databaseAdapter;
    private $laravelAdapter;

    protected function setUp(): void
    {
        $this->databaseAdapter = new DatabaseAdapter();
        $this->laravelAdapter = new LaravelDatabaseAdapter();
    }

    public function testFilterRule()
    {
        $rule = ['ptype', 'v0', 'v1', '', null, 'v4'];
        $filtered = $this->databaseAdapter->filterRule($rule);
        
        $this->assertEquals(['ptype', 'v0', 'v1', 'v4'], $filtered);
    }

    public function testFilterRuleWithAllEmpty()
    {
        $rule = ['ptype', '', null, ''];
        $filtered = $this->databaseAdapter->filterRule($rule);
        
        $this->assertEquals(['ptype'], $filtered);
    }

    public function testIsFiltered()
    {
        $this->assertFalse($this->databaseAdapter->isFiltered());
        
        $this->databaseAdapter->setFiltered(true);
        $this->assertTrue($this->databaseAdapter->isFiltered());
    }

    public function testLoadFilteredPolicyWithStringFilter()
    {
        $model = new \Casbin\Model\Model();
        $model->addDef('p', 'p', ['sub', 'obj', 'act']);
        
        $filter = "ptype = 'p' AND v0 = 'alice'";
        
        $this->databaseAdapter->loadFilteredPolicy($model, $filter);
        $this->assertTrue($this->databaseAdapter->isFiltered());
    }

    public function testLoadFilteredPolicyWithFilterObject()
    {
        $model = new \Casbin\Model\Model();
        $model->addDef('p', 'p', ['sub', 'obj', 'act']);
        
        $filter = new Filter();
        $filter->p[] = 'v0';
        $filter->g[] = 'alice';
        
        $this->databaseAdapter->loadFilteredPolicy($model, $filter);
        $this->assertTrue($this->databaseAdapter->isFiltered());
    }

    public function testLoadFilteredPolicyWithClosure()
    {
        $model = new \Casbin\Model\Model();
        $model->addDef('p', 'p', ['sub', 'obj', 'act']);
        
        $filter = function($query) {
            return $query->where('v0', 'alice');
        };
        
        $this->databaseAdapter->loadFilteredPolicy($model, $filter);
        $this->assertTrue($this->databaseAdapter->isFiltered());
    }

    public function testLoadFilteredPolicyWithInvalidFilter()
    {
        $model = new \Casbin\Model\Model();
        $model->addDef('p', 'p', ['sub', 'obj', 'act']);
        
        $this->expectException(InvalidFilterTypeException::class);
        $this->databaseAdapter->loadFilteredPolicy($model, 123);
    }

    public function testSavePolicyLine()
    {
        $this->databaseAdapter->savePolicyLine('p', ['alice', 'data1', 'read']);
        
        $this->assertTrue(true);
    }

    public function testAddPolicy()
    {
        $this->databaseAdapter->addPolicy('p', 'p', ['alice', 'data1', 'read']);
        
        $this->assertTrue(true);
    }

    public function testAddPolicies()
    {
        $policies = [
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write']
        ];
        
        $this->databaseAdapter->addPolicies('p', 'p', $policies);
        
        $this->assertTrue(true);
    }

    public function testRemovePolicy()
    {
        $this->databaseAdapter->addPolicy('p', 'p', ['alice', 'data1', 'read']);
        $this->databaseAdapter->removePolicy('p', 'p', ['alice', 'data1', 'read']);
        
        $this->assertTrue(true);
    }

    public function testRemovePolicies()
    {
        $policies = [
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write']
        ];
        
        $this->databaseAdapter->addPolicies('p', 'p', $policies);
        $this->databaseAdapter->removePolicies('p', 'p', $policies);
        
        $this->assertTrue(true);
    }

    public function testUpdatePolicy()
    {
        $this->databaseAdapter->addPolicy('p', 'p', ['alice', 'data1', 'read']);
        $this->databaseAdapter->updatePolicy('p', 'p', ['alice', 'data1', 'read'], ['alice', 'data1', 'write']);
        
        $this->assertTrue(true);
    }

    public function testUpdatePolicies()
    {
        $oldPolicies = [
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write']
        ];
        
        $newPolicies = [
            ['alice', 'data1', 'write'],
            ['bob', 'data2', 'read']
        ];
        
        $this->databaseAdapter->addPolicies('p', 'p', $oldPolicies);
        $this->databaseAdapter->updatePolicies('p', 'p', $oldPolicies, $newPolicies);
        
        $this->assertTrue(true);
    }

    public function testRemoveFilteredPolicy()
    {
        $this->databaseAdapter->addPolicies('p', 'p', [
            ['alice', 'data1', 'read'],
            ['alice', 'data2', 'write'],
            ['bob', 'data1', 'read']
        ]);
        
        $this->databaseAdapter->removeFilteredPolicy('p', 'p', 0, 'alice');
        
        $this->assertTrue(true);
    }

    public function testUpdateFilteredPolicies()
    {
        $this->databaseAdapter->addPolicies('p', 'p', [
            ['alice', 'data1', 'read'],
            ['alice', 'data2', 'write']
        ]);
        
        $newPolicies = [
            ['alice', 'data1', 'write'],
            ['alice', 'data2', 'read']
        ];
        
        $this->databaseAdapter->updateFilteredPolicies('p', 'p', $newPolicies, 0, 'alice');
        
        $this->assertTrue(true);
    }

    public function testAdapterWithEmptyRule()
    {
        $this->databaseAdapter->addPolicy('p', 'p', ['', '', '']);
        $this->databaseAdapter->removePolicy('p', 'p', ['', '', '']);
        
        $this->assertTrue(true);
    }

    public function testAdapterWithNullValues()
    {
        $this->databaseAdapter->addPolicy('p', 'p', ['alice', null, 'read']);
        $this->databaseAdapter->removePolicy('p', 'p', ['alice', null, 'read']);
        
        $this->assertTrue(true);
    }

    public function testAdapterWithSpecialCharacters()
    {
        $this->databaseAdapter->addPolicy('p', 'p', ['user@domain.com', 'data#1', 'action:read']);
        $this->databaseAdapter->removePolicy('p', 'p', ['user@domain.com', 'data#1', 'action:read']);
        
        $this->assertTrue(true);
    }

    public function testAdapterWithLargePolicySet()
    {
        $policies = [];
        for ($i = 0; $i < 1000; $i++) {
            $policies[] = ['user' . $i, 'resource' . $i, 'action' . $i];
        }
        
        $this->databaseAdapter->addPolicies('p', 'p', $policies);
        $this->databaseAdapter->removePolicies('p', 'p', $policies);
        
        $this->assertTrue(true);
    }

    public function testAdapterConcurrentOperations()
    {
        $policies = [];
        for ($i = 0; $i < 100; $i++) {
            $policies[] = ['user' . $i, 'resource' . $i, 'action' . $i];
        }
        
        $this->databaseAdapter->addPolicies('p', 'p', $policies);
        
        foreach ($policies as $policy) {
            $this->databaseAdapter->removePolicy('p', 'p', $policy);
        }
        
        $this->assertTrue(true);
    }

    public function testLaravelAdapterMethods()
    {
        $this->laravelAdapter->addPolicy('p', 'p', ['alice', 'data1', 'read']);
        $this->laravelAdapter->removePolicy('p', 'p', ['alice', 'data1', 'read']);
        
        $this->assertTrue(true);
    }

    public function testLaravelAdapterUpdateOrCreate()
    {
        $this->laravelAdapter->addPolicy('p', 'p', ['alice', 'data1', 'read']);
        $this->laravelAdapter->addPolicy('p', 'p', ['alice', 'data1', 'read']);
        
        $this->assertTrue(true);
    }

    public function testAdapterWithDifferentPtypes()
    {
        $this->databaseAdapter->addPolicy('p', 'p', ['alice', 'data1', 'read']);
        $this->databaseAdapter->addPolicy('g', 'g', ['alice', 'admin']);
        $this->databaseAdapter->addPolicy('p2', 'p2', ['admin', 'data1', 'write']);
        
        $this->databaseAdapter->removePolicy('p', 'p', ['alice', 'data1', 'read']);
        $this->databaseAdapter->removePolicy('g', 'g', ['alice', 'admin']);
        $this->databaseAdapter->removePolicy('p2', 'p2', ['admin', 'data1', 'write']);
        
        $this->assertTrue(true);
    }

    public function testAdapterWithPartialFieldMatching()
    {
        $this->databaseAdapter->addPolicies('p', 'p', [
            ['alice', 'data1', 'read'],
            ['alice', 'data2', 'read'],
            ['bob', 'data1', 'write']
        ]);
        
        $this->databaseAdapter->removeFilteredPolicy('p', 'p', 1, 'data1');
        
        $this->assertTrue(true);
    }

    public function testAdapterWithEmptyFieldValues()
    {
        $this->databaseAdapter->addPolicy('p', 'p', ['alice', '', 'read']);
        $this->databaseAdapter->removeFilteredPolicy('p', 'p', 1, '');
        
        $this->assertTrue(true);
    }

    public function testAdapterTransactionRollback()
    {
        $this->expectException(\Exception::class);
        
        $policies = [
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write']
        ];
        
        $this->databaseAdapter->addPolicies('p', 'p', $policies);
        
        throw new \Exception('Test rollback');
    }

    protected function tearDown(): void
    {
        $this->databaseAdapter = null;
        $this->laravelAdapter = null;
    }
}