<?php
/**
 * @desc TestCase.php
 *
 * @author Tinywan(ShaoBo Wan)
 *
 * @date 2022/1/13 11:07
 */

namespace Casbin\WebmanPermission\Tests\ThinkphpDatabase;

use Casbin\WebmanPermission\Permission;
use PHPUnit\Framework\TestCase as BaseTestCase;
use think\facade\Db;
use Webman\Config;
use Workerman\Events\Select;
use Workerman\Worker;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        Config::load(dirname(__DIR__).'/config');
        Config::load(__DIR__.'/config');
        Db::setConfig(config('thinkorm'));
        Worker::$globalEvent = new Select();

        $this->initDb();
        $this->initOtherDb();
        Permission::clear();
    }

    public function initDb()
    {
        $sql = <<<EOF
CREATE TABLE `casbin_rule` (
	`id` BIGINT ( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ptype` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v0` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v1` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v2` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v3` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v4` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v5` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	PRIMARY KEY ( `id` ) USING BTREE,
	KEY `idx_ptype` ( `ptype` ) USING BTREE,
	KEY `idx_v0` ( `v0` ) USING BTREE,
	KEY `idx_v1` ( `v1` ) USING BTREE,
	KEY `idx_v2` ( `v2` ) USING BTREE,
	KEY `idx_v3` ( `v3` ) USING BTREE,
	KEY `idx_v4` ( `v4` ) USING BTREE,
    KEY `idx_v5` ( `v5` ) USING BTREE 
) ENGINE = INNODB CHARSET = utf8mb4 COMMENT = '策略规则表';
EOF;
        Db::execute('drop table if exists casbin_rule');
        Db::execute($sql);
    }

    public function initOtherDb()
    {
        $sql = <<<EOF
CREATE TABLE `other_casbin_rule` (
	`id` BIGINT ( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ptype` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v0` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v1` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v2` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v3` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v4` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	`v5` VARCHAR ( 128 ) NOT NULL DEFAULT '',
	PRIMARY KEY ( `id` ) USING BTREE,
	KEY `idx_ptype` ( `ptype` ) USING BTREE,
	KEY `idx_v0` ( `v0` ) USING BTREE,
	KEY `idx_v1` ( `v1` ) USING BTREE,
	KEY `idx_v2` ( `v2` ) USING BTREE,
	KEY `idx_v3` ( `v3` ) USING BTREE,
	KEY `idx_v4` ( `v4` ) USING BTREE,
    KEY `idx_v5` ( `v5` ) USING BTREE 
) ENGINE = INNODB CHARSET = utf8mb4 COMMENT = '策略规则表';
EOF;
        Db::execute('drop table if exists other_casbin_rule');
        Db::execute($sql);
    }
}
