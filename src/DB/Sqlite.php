<?php
/**
 * PDO_SQLite支持类
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

class Sqlite extends PDOAbstract {
	/**
	 * 自动连接
	 * @access protected
	 */
	protected function autoConnect() {
		if (App::$config->has('sqlite')) {
			$this->setParam(App::$config->get('sqlite'));
		}
	}
	/**
	 * 连接到MySQL
	 * @access protected
	 */
	protected function connect() {
		//对老版本的支持
		if ($this->config['version'] === 'sqlite3') {
			$dsn = 'sqlite:';
		} else {
			$dsn = 'sqlite2:';
		}
		$path = str_replace('@app/', APP_PATH, $this->config['path']);
		$dsn .= $path;
		try {
			$this->connection = new \PDO($dsn);
		} catch (\PDOException $e) {
			throw new DBException($e->getMessage());
		}
	}
	/**
	 * 查询并返回一条结果
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
