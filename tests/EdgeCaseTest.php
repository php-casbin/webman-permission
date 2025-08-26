<?php

namespace Casbin\WebmanPermission\Tests;

use Casbin\WebmanPermission\Permission;
use Casbin\WebmanPermission\Adapter\DatabaseAdapter;
use Casbin\WebmanPermission\Adapter\LaravelDatabaseAdapter;
use Casbin\Exceptions\CasbinException;
use PHPUnit\Framework\TestCase;

class EdgeCaseTest extends TestCase
{
    protected function setUp(): void
    {
        Permission::clear();
    }

    public function testEmptyStringsInPolicy()
    {
        Permission::addPolicy('', '', '');
        $this->assertTrue(Permission::hasPolicy('', '', ''));
        
        $result = Permission::removePolicy('', '', '');
        $this->assertTrue($result);
    }

    public function testNullValuesInPolicy()
    {
        Permission::addPolicy('alice', null, 'read');
        $this->assertTrue(Permission::hasPolicy('alice', null, 'read'));
        
        $result = Permission::removePolicy('alice', null, 'read');
        $this->assertTrue($result);
    }

    public function testVeryLongStrings()
    {
        $longString = str_repeat('a', 1000);
        Permission::addPolicy($longString, $longString, $longString);
        $this->assertTrue(Permission::hasPolicy($longString, $longString, $longString));
        
        $result = Permission::removePolicy($longString, $longString, $longString);
        $this->assertTrue($result);
    }

    public function testSpecialCharacters()
    {
        $specialChars = [
            'user@domain.com',
            'data#1',
            'action:read',
            'user+test@example.com',
            'data?query=param',
            'action&operation=test',
            'user|domain',
            'data\\path',
            'action"quoted"',
            "user'string",
            'data[0]',
            'action(test)'
        ];
        
        foreach ($specialChars as $char) {
            Permission::addPolicy($char, $char, $char);
            $this->assertTrue(Permission::hasPolicy($char, $char, $char));
            
            $result = Permission::removePolicy($char, $char, $char);
            $this->assertTrue($result);
        }
    }

    public function testUnicodeCharacters()
    {
        $unicodeStrings = [
            '用户名',
            '数据1',
            '操作:读取',
            'αβγδε',
            'αβγδεζηθικλμνξοπρστυφχψω',
            '漢字',
            '😀emoji😊',
            'café',
            'naïve',
            'résumé'
        ];
        
        foreach ($unicodeStrings as $str) {
            Permission::addPolicy($str, $str, $str);
            $this->assertTrue(Permission::hasPolicy($str, $str, $str));
            
            $result = Permission::removePolicy($str, $str, $str);
            $this->assertTrue($result);
        }
    }

    public function testMixedCaseSensitivity()
    {
        Permission::addPolicy('Alice', 'Data1', 'Read');
        Permission::addPolicy('alice', 'data1', 'read');
        
        $this->assertTrue(Permission::hasPolicy('Alice', 'Data1', 'Read'));
        $this->assertTrue(Permission::hasPolicy('alice', 'data1', 'read'));
        
        $this->assertFalse(Permission::hasPolicy('alice', 'Data1', 'Read'));
        $this->assertFalse(Permission::hasPolicy('Alice', 'data1', 'read'));
    }

    public function testWhitespaceHandling()
    {
        Permission::addPolicy(' alice ', ' data1 ', ' read ');
        $this->assertTrue(Permission::hasPolicy(' alice ', ' data1 ', ' read '));
        
        $this->assertFalse(Permission::hasPolicy('alice', 'data1', 'read'));
        $this->assertFalse(Permission::hasPolicy('alice ', 'data1 ', 'read '));
    }

    public function testEmptyPoliciesArray()
    {
        $result = Permission::addPolicies([]);
        $this->assertFalse($result);
        
        $result = Permission::removePolicies([]);
        $this->assertTrue($result);
    }

    public function testSingleCharacterPolicies()
    {
        Permission::addPolicy('a', 'b', 'c');
        $this->assertTrue(Permission::hasPolicy('a', 'b', 'c'));
        
        $result = Permission::removePolicy('a', 'b', 'c');
        $this->assertTrue($result);
    }

    public function testNumericPolicies()
    {
        Permission::addPolicy('123', '456', '789');
        $this->assertTrue(Permission::hasPolicy('123', '456', '789'));
        
        $result = Permission::removePolicy('123', '456', '789');
        $this->assertTrue($result);
    }

    public function testBooleanLikeStrings()
    {
        Permission::addPolicy('true', 'false', 'null');
        $this->assertTrue(Permission::hasPolicy('true', 'false', 'null'));
        
        $result = Permission::removePolicy('true', 'false', 'null');
        $this->assertTrue($result);
    }

    public function testSQLInjectionAttempts()
    {
        $maliciousInputs = [
            "alice'; DROP TABLE casbin_rule; --",
            "alice' OR '1'='1",
            "alice'; SELECT * FROM users; --",
            "alice' UNION SELECT * FROM users; --",
            "alice' AND SLEEP(10); --"
        ];
        
        foreach ($maliciousInputs as $input) {
            Permission::addPolicy($input, 'data1', 'read');
            $this->assertTrue(Permission::hasPolicy($input, 'data1', 'read'));
            
            $result = Permission::removePolicy($input, 'data1', 'read');
            $this->assertTrue($result);
        }
    }

    public function testXSSAttempts()
    {
        $xssInputs = [
            '<script>alert("xss")</script>',
            'javascript:alert("xss")',
            '"><script>alert("xss")</script>',
            '<img src="x" onerror="alert(\'xss\')">',
            '<svg onload="alert(\'xss\')">'
        ];
        
        foreach ($xssInputs as $input) {
            Permission::addPolicy($input, 'data1', 'read');
            $this->assertTrue(Permission::hasPolicy($input, 'data1', 'read'));
            
            $result = Permission::removePolicy($input, 'data1', 'read');
            $this->assertTrue($result);
        }
    }

    public function testConcurrentAccess()
    {
        $policies = [];
        for ($i = 0; $i < 100; $i++) {
            $policies[] = ['user' . $i, 'resource' . $i, 'action' . $i];
        }
        
        Permission::addPolicies($policies);
        
        for ($i = 0; $i < 100; $i++) {
            $this->assertTrue(Permission::enforce('user' . $i, 'resource' . $i, 'action' . $i));
        }
        
        Permission::removePolicies($policies);
        
        for ($i = 0; $i < 100; $i++) {
            $this->assertFalse(Permission::enforce('user' . $i, 'resource' . $i, 'action' . $i));
        }
    }

    public function testMemoryUsageWithLargeDataset()
    {
        $initialMemory = memory_get_usage();
        
        $policies = [];
        for ($i = 0; $i < 1000; $i++) {
            $policies[] = ['user' . $i, 'resource' . $i, 'action' . $i];
        }
        
        Permission::addPolicies($policies);
        
        $peakMemory = memory_get_peak_usage();
        $memoryIncrease = $peakMemory - $initialMemory;
        
        $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease);
        
        Permission::removePolicies($policies);
    }

    public function testPerformanceWithManyEnforcements()
    {
        Permission::addPolicy('user', 'resource', 'action');
        
        $startTime = microtime(true);
        
        for ($i = 0; $i < 1000; $i++) {
            Permission::enforce('user', 'resource', 'action');
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $this->assertLessThan(1.0, $executionTime);
        
        Permission::removePolicy('user', 'resource', 'action');
    }

    public function testPolicyWithAllNullValues()
    {
        Permission::addPolicy(null, null, null);
        $this->assertTrue(Permission::hasPolicy(null, null, null));
        
        $result = Permission::removePolicy(null, null, null);
        $this->assertTrue($result);
    }

    public function testPolicyWithMixedNullAndEmpty()
    {
        Permission::addPolicy('alice', '', null);
        $this->assertTrue(Permission::hasPolicy('alice', '', null));
        
        $result = Permission::removePolicy('alice', '', null);
        $this->assertTrue($result);
    }

    public function testVeryLargePolicySet()
    {
        $policies = [];
        for ($i = 0; $i < 5000; $i++) {
            $policies[] = ['user' . $i, 'resource' . $i, 'action' . $i];
        }
        
        $result = Permission::addPolicies($policies);
        $this->assertTrue($result);
        
        for ($i = 0; $i < 100; $i++) {
            $this->assertTrue(Permission::enforce('user' . $i, 'resource' . $i, 'action' . $i));
        }
        
        $result = Permission::removePolicies($policies);
        $this->assertTrue($result);
    }

    public function testDuplicatePolicyInBatch()
    {
        $policies = [
            ['alice', 'data1', 'read'],
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write']
        ];
        
        $result = Permission::addPolicies($policies);
        $this->assertFalse($result);
        
        $this->assertTrue(Permission::hasPolicy('alice', 'data1', 'read'));
        $this->assertTrue(Permission::hasPolicy('bob', 'data2', 'write'));
    }

    public function testPolicyWithNewlinesAndTabs()
    {
        $newLinePolicy = "alice\ndata1\nread";
        $tabPolicy = "alice\tdata1\tread";
        
        Permission::addPolicy($newLinePolicy, $newLinePolicy, $newLinePolicy);
        Permission::addPolicy($tabPolicy, $tabPolicy, $tabPolicy);
        
        $this->assertTrue(Permission::hasPolicy($newLinePolicy, $newLinePolicy, $newLinePolicy));
        $this->assertTrue(Permission::hasPolicy($tabPolicy, $tabPolicy, $tabPolicy));
        
        Permission::removePolicy($newLinePolicy, $newLinePolicy, $newLinePolicy);
        Permission::removePolicy($tabPolicy, $tabPolicy, $tabPolicy);
    }

    protected function tearDown(): void
    {
        Permission::clear();
    }
}