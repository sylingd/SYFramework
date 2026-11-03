<?php
namespace SyTest;

use Latitude\QueryBuilder\Engine\MySqlEngine;
use Latitude\QueryBuilder\QueryFactory;
use PHPUnit\Framework\TestCase;
use Sy\DB\DBInterface;
use Sy\Exception\Exception;
use Sy\ModelAbstract;

class FakeDriver implements DBInterface {
	public $queries = [];
	public $result = [];

	public function query(string $sql, $data = null) {
		$this->queries[] = [$sql, $data];
		return $this->result;
	}

	public function get(string $sql, $data = null) {
		return current($this->query($sql, $data));
	}

	public static function getBuilder(): QueryFactory {
		return new QueryFactory(new MySqlEngine());
	}
}

class UserModel extends ModelAbstract {
	protected $_table_name = 'user';

	public function processItem(&$item) {
		if (is_array($item)) {
			$item['processed'] = true;
		}
	}
}

class EmptyTableModel extends ModelAbstract {
	protected $_table_name = '';
}

class ModelAbstractTest extends TestCase {
	private $driver;
	private $model;

	public function setUp(): void {
		$this->driver = new FakeDriver();
		$this->model = new UserModel($this->driver);
	}

	public function testEmptyTableNameRejected() {
		$this->expectException(Exception::class);
		new EmptyTableModel($this->driver);
	}

	public function testGetByPrimary() {
		$this->driver->result = [['id' => 1, 'name' => 'foo', 'processed' => true]];
		$res = $this->model->get(1);
		$this->assertEquals(1, $res['id']);
		$this->assertTrue($res['processed']);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('SELECT * FROM `user` WHERE `id` = ? LIMIT 1', $sql);
		$this->assertEquals([1], $params);
	}

	public function testGetByFilterWithCols() {
		$this->driver->result = [['name' => 'foo', 'processed' => true]];
		$res = $this->model->get(['name' => 'foo'], ['name']);
		$this->assertEquals('foo', $res['name']);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('SELECT `name` FROM `user` WHERE `name` = ? LIMIT 1', $sql);
		$this->assertEquals(['foo'], $params);
	}

	public function testGetNoResult() {
		$this->driver->result = [];
		$this->assertNull($this->model->get(999));
	}

	public function testListWithFilter() {
		$this->driver->result = [['id' => 1], ['id' => 2]];
		$res = $this->model->list(['name' => 'foo'], 10, 5);
		$this->assertEquals([['id' => 1], ['id' => 2]], $res);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('SELECT * FROM `user` WHERE `name` = ? LIMIT 10 OFFSET 5', $sql);
		$this->assertEquals(['foo'], $params);
	}

	public function testListWithIdArray() {
		$this->driver->result = [];
		$this->model->list([1, 2, 3]);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('SELECT * FROM `user` WHERE `id` IN (?) LIMIT 3 OFFSET 0', $sql);
		$this->assertEquals([[1, 2, 3]], $params);
	}

	public function testSetWithColsAndFilter() {
		$this->driver->result = ['_affected_rows' => 1];
		$res = $this->model->set(['name' => 'foo', 'password' => 'bar'], ['name'], 1);
		$this->assertEquals(1, $res);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('UPDATE `user` SET `name` = ? WHERE `id` = ?', $sql);
		$this->assertEquals(['foo', 1], $params);
	}

	public function testSetFilterAsArray() {
		$this->driver->result = ['_affected_rows' => 3];
		// $cols filters which keys may be written
		$res = $this->model->set(['name' => 'foo', 'not_allowed' => 'x'], ['name'], ['id' => 1]);
		$this->assertEquals(3, $res);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('UPDATE `user` SET `name` = ? WHERE `id` = ?', $sql);
		$this->assertEquals(['foo', 1], $params);
	}

	public function testSetWithoutCols() {
		// two-argument form: second argument is the filter
		$this->driver->result = ['_affected_rows' => 3];
		$res = $this->model->set(['name' => 'foo'], ['id' => 1]);
		$this->assertEquals(3, $res);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('UPDATE `user` SET `name` = ? WHERE `id` = ?', $sql);
	}

	public function testSetAllRequiresTrueFilter() {
		$this->expectException(\Sy\Exception\DBException::class);
		$this->model->set(['name' => 'foo'], []);
	}

	public function testSetAll() {
		// filter === true updates all rows
		$this->driver->result = ['_affected_rows' => 10];
		$res = $this->model->set(['name' => 'foo'], true);
		$this->assertEquals(10, $res);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('UPDATE `user` SET `name` = ?', $sql);
		$this->assertEquals(['foo'], $params);
	}

	public function testDel() {
		$this->driver->result = ['_affected_rows' => 1];
		$res = $this->model->del(1);
		$this->assertEquals(1, $res);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('DELETE FROM `user` WHERE `id` = ?', $sql);
		$this->assertEquals([1], $params);
	}

	public function testDelRejectsEmptyFilter() {
		$this->expectException(\Sy\Exception\DBException::class);
		$this->model->del([]);
	}

	public function testDelAll() {
		$this->driver->result = ['_affected_rows' => 4];
		$this->assertEquals(4, $this->model->del(true));
		[$sql] = $this->driver->queries[0];
		$this->assertEquals('DELETE FROM `user`', $sql);
	}

	public function testAdd() {
		$this->driver->result = ['_insert_id' => 7, '_affected_rows' => 1];
		$res = $this->model->add(['name' => 'foo', 'extra' => 'x'], ['name']);
		$this->assertEquals(7, $res);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('INSERT INTO `user` (`name`) VALUES (?)', $sql);
		$this->assertEquals(['foo'], $params);
	}

	public function testAddWithoutCols() {
		$this->driver->result = ['_insert_id' => 9, '_affected_rows' => 1];
		$res = $this->model->add(['name' => 'foo']);
		$this->assertEquals(9, $res);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('INSERT INTO `user` (`name`) VALUES (?)', $sql);
	}

	public function testGetBuilder() {
		$this->assertInstanceOf(QueryFactory::class, $this->model->getBuilder());
	}

	public function testExecutePassesSqlAndParams() {
		$query = $this->model->newSelect();
		$query->where(\Latitude\QueryBuilder\field('id')->eq(5));
		$this->model->execute($query);
		[$sql, $params] = $this->driver->queries[0];
		$this->assertEquals('SELECT * FROM `user` WHERE `id` = ?', $sql);
		$this->assertEquals([5], $params);
	}
}
