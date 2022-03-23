<?php
/**
 * @desc TestCase.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/1/13 11:07
 */

namespace Tinywan\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Tinywan\Casbin\Model\RuleModel;

class TestCase extends BaseTestCase
{
    public function start()
    {

    }

    public function initTable()
    {
        RuleModel::where("1 = 1")->delete();
        RuleModel::create(['ptype' => 'p', 'v0' => 'alice', 'v1' => 'data1', 'v2' => 'read']);
        RuleModel::create(['ptype' => 'p', 'v0' => 'bob', 'v1' => 'data2', 'v2' => 'write']);
        RuleModel::create(['ptype' => 'p', 'v0' => 'data2_admin', 'v1' => 'data2', 'v2' => 'read']);
        RuleModel::create(['ptype' => 'p', 'v0' => 'data2_admin', 'v1' => 'data2', 'v2' => 'write']);
        RuleModel::create(['ptype' => 'g', 'v0' => 'alice', 'v1' => 'data2_admin']);
    }

    protected function _setUp()
    {
        $this->initTable();
        $this->start();
    }

}