<?php
/**
 * PDO_PostgreSQL支持类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Library
 * @link https://www.sylibs.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy\DB;

use Sy\App;
use Sy\Exception\DBException;

class Postgre extends PDOAbstract {
	/**
	 * 自动连接
	 * @access public
	 */
	public function autoConnect() {
		if (App::$config->has('postgre')) {
			$this->setParam(App::$config->get('postgre'));
		}
	}
	/**
	 * 连接
	 * @access protected
	 */
	protected function connect() {
		$dsn = 'pgsql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';';
		if (isset($this->config['name'])) {
			$dsn .= 'dbname=' . $this->config['database'] . ';';
		}
		$dsn .= 'charset=' . strtolower(str_replace('-', '', App::$config->get('charset')));
		try {
			$this->connection = new \PDO($dsn, $this->config['user'], $this->config['password']);
		} catch (\PDOException $e) {
			throw new DBException($e->getMessage());
		}
	}
	/**
	 * 查询并返回一条结果
	 * @see YPdo::getOne
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数
	 * @return array
	 */
	public function getOne($sql, $data = NULL) {
		if (stripos($sql, 'limit') === false && stripos($sql, 'offset') === false) {
			$sql .= ' LIMIT 1 OFFSET 0';
		}
		$r = $this->query($sql, $data);
		return current($r);
	}
}
