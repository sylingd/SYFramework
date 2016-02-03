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

namespace sy\lib\db;
use \Sy;
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
	 * @access protected
	 */
	protected function connect() {
		$id = $this->current;
		$config = $this->dbInfo[$id];
		$dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';';
		if (isset($config['name'])) {
			$dsn .= 'dbname=' . $config['name'] . ';';
		}
		$dsn .= 'charset=' . strtolower(str_replace('-', '', Sy::$app['charset']));
		try {
			$this->link[$id] = new PDO($dsn, $config['user'], $config['password']);
			$this->link[$id]->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
			$this->result[$id] = [];
		} catch (PDOException $e) {
			throw new SYDBException(YHtml::encode($e->getMessage), $this->dbtype, $dsn);
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
		if (!preg_match('/limit ([0-9,]+)$/', strtolower($sql))) {
			$sql .= ' LIMIT 0,1';
		}
		return $this->query($sql, $data);
	}
	/**
	 * 事务：开始
	 * @access public
	 */
	public function beginTransaction() {
		$id = $this->current;
		$this->link[$id]->beginTransaction();
	}
	/**
	 * 事务：添加一句
	 * @access public
	 * @param string $sql
	 */
	public function addOne($sql) {
		$id = $this->current;
		$this->link[$id]->exec($this->setQuery($sql));
	}
	/**
	 * 事务：提交
	 * @access public
	 */
	public function commit() {
		$id = $this->current;
		$this->link[$id]->commit();
	}
	/**
	 * 事务：回滚
	 * @access public
	 */
	public function rollback() {
		$id = $this->current;
		$this->link[$id]->rollBack();
	}
}
