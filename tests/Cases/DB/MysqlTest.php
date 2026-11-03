<?php
namespace SyTest\DB;

use PDO;
use Sy\DB\DBInterface;
use Sy\DB\Mysql;

/**
 * Runs against the MySQL service started by CI (see tests/App/Config.php for
 * connection settings). Connection failures are errors, not skips: the
 * extension guard below is the only skip condition.
 */
class MysqlTest extends DriverTestBase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
			self::markTestSkipped('跳过：缺少 pdo_mysql 扩展');
		}
	}

	protected function createDriver(): DBInterface {
		return new Mysql();
	}

	protected function createTableSql(): string {
		return 'CREATE TABLE pdo_test (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, name VARCHAR(60), num INT, PRIMARY KEY (id))';
	}
}
