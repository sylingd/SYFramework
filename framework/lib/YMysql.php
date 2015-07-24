<?php

/**
 * PDO_MySQL支持类
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

class YMysql extends YPdo {
	protected $dbtype = 'MySQL';
	/**
	 * 自动连接
	 * @access public
	 */
	public function autoConnect() {
		if (isset(Sy::$app['mysql'])) {
			$this->setParam(Sy::$app['mysql']);
		}
	}
	/**
	 * 连接到MySQL
	 * @access private
	 */
	private function connect() {
		$dsn = 'mysql:host=' . $this->connect_info['host'] . ';port=' . $this->connect_info['port'] . ';dbname=' . $this->connect_info['name'] . ';charset=' . strtolower(str_replace('-', '', Sy::$app['charset']));
		try {
			$this->link = new PDO($dsn, $this->connect_info['user'], $this->connect_info['password']);
			$this->link->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
			$this->result = array();
		}
		catch (PDOException $e) {
			throw new SYDBException(YHtml::encode($e->getMessage), 2, $dsn);
		}
	}
	/**
	 * 查询并返回一条结果
	 * @see YPod::getOne
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
	/**
	 * 事务：开始
	 * @access public
	 */
	public function beginTransaction() {
		$this->link->beginTransaction();
	}
	/**
	 * 事务：添加一句
	 * @access public
	 * @param string $sql
	 */
	public function addOne($sql) {
		$this->link->exec($this->setQuery($aql));
	}
	/**
	 * 事务：提交
	 * @access public
	 */
	public function commit() {
		$this->link->commit();
	}
	/**
	 * 事务：回滚
	 * @access public
	 */
	public function rollback() {
		$this->link->rollBack();
	}
}
