<?php
namespace SyTest\DB;

use PDO;
use Sy\App;
use Sy\Config\Adapter\Arr;
use Sy\DB\DBInterface;
use Sy\DB\Sqlite;
use Sy\Exception\DBException;

/**
 * Uses an in-memory SQLite database, so this test also runs wherever
 * pdo_sqlite is available (not only in CI).
 */
class SqliteTest extends DriverTestBase {
	private $origConfig;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
			self::markTestSkipped('跳过：缺少 pdo_sqlite 扩展');
		}
	}

	public function setUp(): void {
		$this->origConfig = App::$config;
		$this->useConfig('sqlite3');
	}

	public function tearDown(): void {
		if ($this->origConfig !== null) {
			App::$config = $this->origConfig;
		}
	}

	private function useConfig(string $version) {
		// Sqlite reads connection settings from the 'mysql' config key
		App::$config = new Arr([
			'product' => [
				'mysql' => [
					'version' => $version,
					'path' => ':memory:',
				],
				'charset' => 'UTF-8',
			],
		]);
	}

	protected function createDriver(): DBInterface {
		return new Sqlite();
	}

	protected function createTableSql(): string {
		return 'CREATE TABLE pdo_test (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, num INTEGER)';
	}

	public function testLegacySqlite2Rejected() {
		// the sqlite2 DSN is no longer shipped with PHP 8
		$this->useConfig('sqlite2');
		$this->expectException(DBException::class);
		new Sqlite();
	}
}
