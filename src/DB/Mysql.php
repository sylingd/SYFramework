<?php
/**
 * PDO_MySQL支持类
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

class Mysql extends PDOAbstract {
	/**
	 * 自动连接
	 * @access public
	 */
	public function autoConnect() {
		if (App::$config->has('mysql')) {
			$this->setParam(App::$config->get('mysql'));
		}
	}
	/**
	 * 连接到MySQL
	 * @access protected
	 */
	protected function connect() {
		$dsn = 'mysql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';';
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
	 * 
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数
	 * @return array
	 */
	public function getOne($sql, $data = NULL) {
		if (stripos($sql, 'limit') === false) {
			$sql .= ' LIMIT 0,1';
		}
		$r = $this->query($sql, $data);
		return current($r);
	}
}
