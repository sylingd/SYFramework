<?php
namespace SyTest\DB;

use PDO;
use Sy\App;
use Sy\Config\Adapter\Arr;
use Sy\DB\DBInterface;
use Sy\DB\Postgre;

/**
 * Runs against the postgres service container defined in CI
 * (root/root, db test, 127.0.0.1:5432).
 */
class PostgreTest extends DriverTestBase {
	private $origConfig;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if (!in_array('pgsql', PDO::getAvailableDrivers(), true)) {
			self::markTestSkipped('跳过：缺少 pdo_pgsql 扩展');
		}
	}

	public function setUp(): void {
		$this->origConfig = App::$config;
		// Postgre reads connection settings from the 'mysql' config key
		App::$config = new Arr([
			'product' => [
				'mysql' => [
					'host' => '127.0.0.1',
					'port' => 5432,
					'user' => 'root',
					'password' => 'root',
					'database' => 'test',
				],
				'charset' => 'UTF-8',
			],
		]);
	}

	public function tearDown(): void {
		if ($this->origConfig !== null) {
			App::$config = $this->origConfig;
		}
	}

	protected function createDriver(): DBInterface {
		return new Postgre();
	}

	protected function createTableSql(): string {
		return 'CREATE TABLE pdo_test (id BIGSERIAL PRIMARY KEY, name VARCHAR(60), num INTEGER)';
	}

	public function testClientEncoding() {
		// connect() must apply the configured charset via SET NAMES
		$driver = $this->createDriver();
		$row = $driver->get("SELECT current_setting('client_encoding') AS enc");
		$this->assertEquals('UTF8', strtoupper($row['enc']));
	}

	public function testGetAppendsPostgresLimit() {
		$driver = $this->createDriver();
		$this->freshTable($driver);
		$driver->query('INSERT INTO pdo_test (name, num) VALUES (?, ?)', ['alice', 1]);
		$driver->query('INSERT INTO pdo_test (name, num) VALUES (?, ?)', ['bob', 2]);
		// Postgre::get appends "LIMIT 1 OFFSET 0" (distinct from MySQL syntax)
		$row = $driver->get('SELECT * FROM pdo_test ORDER BY id');
		$this->assertEquals('alice', $row['name']);
		// an explicit OFFSET prevents the automatic suffix
		$row = $driver->get('SELECT * FROM pdo_test ORDER BY id OFFSET 1');
		$this->assertEquals('bob', $row['name']);
	}
}
