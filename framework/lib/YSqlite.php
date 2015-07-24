<?php

/**
 * PDO_SQLite支持类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Library
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\lib;
use Sy;
use \PDO;
use \sy\base\YPdo;
use \sy\lib\YHtml;
use \sy\base\SYException;
use \sy\base\SYDBException;

class YSqlite extends YPdo {
	protected $dbtype = 'SQLite';
	/**
	 * 自动连接
	 * @access protected
	 */
	protected function autoConnect() {
		if (isset(Sy::$app['sqlite'])) {
			$this->setParam(Sy::$app['sqlite']);
		}
	}
	/**
	 * 连接到MySQL
	 * @access private
	 */
	private function connect() {
		//对老版本的支持
		if ($this->connect_info['version'] === 'sqlite3') {
			$dsn = 'sqlite:';
		} else {
			$dsn = 'sqlite2:';
		}
		$path = str_replace('@app/', Sy::$appDir, $this->connect_info['path']);
		$dsn .= $path;
		try {
			$this->link = new PDO($dsn);
			$this->result = array();
		}
		catch (PDOException $e) {
			throw new SYDBException(YHtml::encode($e->getMessage), $this->dbtype, $dsn);
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
		if (!preg_match('/limit ([0-9,]+)$/', strtolower($sql))) {
			$sql .= ' LIMIT 0,1';
		}
		$this->query('one', $sql, $data);
		$r = $this->getArray('one');
		$this->free('one');
		return $r;
	}
}
