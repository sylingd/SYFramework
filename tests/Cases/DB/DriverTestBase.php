<?php
namespace SyTest\DB;

use Latitude\QueryBuilder\QueryFactory;
use PDO;
use PHPUnit\Framework\TestCase;
use Sy\DB\DBInterface;
use Sy\Exception\DBException;

/**
 * PDO drivers common test: connection, CRUD, field mapping and error paths.
 * Subclasses only provide the driver and dialect-specific DDL.
 */
abstract class DriverTestBase extends TestCase {
	abstract protected function createDriver(): DBInterface;

	abstract protected function createTableSql(): string;

	/** Recreate the test table so every test is order-independent */
	protected function freshTable(DBInterface $driver) {
		$driver->query('DROP TABLE IF EXISTS pdo_test');
		$driver->query($this->createTableSql());
	}

	public function testConnection() {
		$driver = $this->createDriver();
		$this->assertInstanceOf(PDO::class, $driver->getConnection());
	}

	public function testGetBuilder() {
		$driver = $this->createDriver();
		$this->assertInstanceOf(QueryFactory::class, $driver::getBuilder());
	}

	public function testCrud() {
		$driver = $this->createDriver();
		$this->freshTable($driver);
		// insert with positional params
		$res = $driver->query('INSERT INTO pdo_test (name, num) VALUES (?, ?)', ['alice', 20]);
		$this->assertSame(1, $res['_affected_rows']);
		$this->assertGreaterThan(0, intval($res['_insert_id']));
		// insert with named params
		$res = $driver->query('INSERT INTO pdo_test (name, num) VALUES (:name, :num)', ['name' => 'bob', 'num' => 30]);
		$this->assertSame(1, $res['_affected_rows']);
		// unicode round-trip
		$driver->query('INSERT INTO pdo_test (name, num) VALUES (?, ?)', ['小明', 40]);
		// get one row by named param
		$row = $driver->get('SELECT id, name, num FROM pdo_test WHERE name = :name', ['name' => 'alice']);
		$this->assertEquals('alice', $row['name']);
		$this->assertEquals(20, $row['num']);
		// get() appends LIMIT automatically
		$row = $driver->get('SELECT * FROM pdo_test ORDER BY id');
		$this->assertEquals('alice', $row['name']);
		// unicode value read back
		$row = $driver->get('SELECT name FROM pdo_test WHERE name = ?', ['小明']);
		$this->assertSame('小明', $row['name']);
		// fetch list
		$rows = $driver->query('SELECT name FROM pdo_test ORDER BY id');
		$this->assertCount(3, $rows);
		$this->assertEquals(['alice', 'bob', '小明'], array_column($rows, 'name'));
		// update
		$res = $driver->query('UPDATE pdo_test SET num = ? WHERE name = ?', [25, 'alice']);
		$this->assertSame(1, $res['_affected_rows']);
		$row = $driver->get('SELECT num FROM pdo_test WHERE name = ?', ['alice']);
		$this->assertEquals(25, $row['num']);
		// delete
		$res = $driver->query('DELETE FROM pdo_test WHERE name = ?', ['bob']);
		$this->assertSame(1, $res['_affected_rows']);
		$rows = $driver->query('SELECT COUNT(*) AS n FROM pdo_test');
		$this->assertEquals(2, intval($rows[0]['n']));
	}

	public function testQueryError() {
		$driver = $this->createDriver();
		$this->expectException(DBException::class);
		$driver->query('SELECT * FROM ' . uniqid('table_not_exists_'));
	}

	public function testExecuteError() {
		$driver = $this->createDriver();
		$this->freshTable($driver);
		$driver->query('INSERT INTO pdo_test (id, name, num) VALUES (?, ?, ?)', [1, 'x', 1]);
		// duplicate primary key must surface as DBException
		$this->expectException(DBException::class);
		$driver->query('INSERT INTO pdo_test (id, name, num) VALUES (?, ?, ?)', [1, 'y', 2]);
	}
}
