<?php

/**
 * MySQLi支持类
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
use \mysqli;
use \sy\lib\YHtml;
use \sy\base\SYException;
use \sy\base\SYDBException;

class YMysqli {
	protected $dbtype = 'MySQL';
	protected $link = [];
	protected $dbInfo = [];
	protected $result = [];
	static protected $_instance = NULL;
	static protected $current = 'default';
	static public function i($id = 'default') {
		if (static::$_instance === NULL) {
			static::$_instance = new static;
		}
		$this->current = $id;
		return static::$_instance;
	}
	/**
	 * 构造函数，自动连接
	 * @access public
	 */
	public function __construct() {
		if (!class_exists('mysqli', FALSE)) {
			throw new SYException('Class "MySQLi" is required', '10020');
		}
		if (isset(Sy::$app['mysql']) && $this->current === 'default') {
			$this->setParam(Sy::$app['mysql']);
		}
	}
	/**
	 * 设置Server
	 * @access public
	 * @param array $param MySQL服务器参数
	 */
	public function setParam($param) {
		$id = $this->current;
		$this->dbInfo[$id] = $param;
		$this->link[$id] = NULL;
		$this->connect();
	}
	/**
	 * 连接到MySQL
	 * @access protected
	 */
	protected function connect() {
		$id = $this->current;
		$config = $this->dbInfo[$id];
		$this->link[$id] = new mysqli($config['host'], $config['user'], $config['password'], $config['name'], $config['port']);
		if ($this->link[$id]->connect_error) {
			throw new SYDBException(YHtml::encode($this->link[$id]->connect_error), $this->dbtype, 'NULL');
		}
		$this->link[$id]->set_charset(strtolower(str_replace('-', '', Sy::$app['charset'])));
	}
	/**
	 * 处理Key
	 * @access protected
	 * @param string $sql
	 * @return string
	 */
	protected function setQuery($sql) {
		$id = $this->current;
		return str_replace('#@__', $this->dbInfo[$id]['prefix'], $sql);
	}
	/**
	 * 获取所有结果
	 * @access public
	 * @param string $key
	 * @return array
	 */
	public function getAll($key) {
		$id = $this->current;
		$rs = $this->result[$id][$key];
		$rs = $rs->fetch_all(MYSQLI_ASSOC);
		return $rs;
	}
	/**
	 * 释放结果
	 * @access public
	 * @param string $key
	 */
	public function free($key) {
		$id = $this->current;
		$this->result[$id][$key]->free();
		$this->result[$id][$key] = NULL;
	}
	/**
	 * 获取最后产生的ID
	 * @access public
	 * @return int
	 */
	public function getLastId() {
		$id = $this->current;
		return intval($this->link[$id]->insert_id);
	}
	/**
	 * 获取一个结果
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function getArray($key) {
		$id = $this->current;
		if (!isset($this->result[$id][$key]) || empty($this->result[$id][$key])) {
			return NULL;
		}
		$rs = $this->result[$id][$key];
		$rs = $rs->fetch_array(MYSQLI_ASSOC);
		return $rs;
	}
	/**
	 * 执行查询
	 * @access public
	 * @param string $key
	 * @param string $sql SQL语句
	 * @param array $data 参数
	 */
	public function query($key, $sql, $data = NULL) {
		$id = $this->current;
		$sql = $this->setQuery($sql);
		if (is_array($data)) {
			foreach ($data as $v) {
				$v = str_replace("'", "\\'", $v);
				$sql = str_replace('?', "'$v'", $sql, 1);
			}
		}
		$r = $this->link[$id]->query($sql);
		//执行失败
		if ($r !== TRUE) {
			throw new SYDBException(YHtml::encode($this->link[$id]->error), $this->dbtype, YHtml::encode($sql));
		}
		if ($key !== NULL) {
			$this->result[$id][$key] = $r;
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
	/**
	 * 事务：开始
	 * @access public
	 */
	public function beginTransaction() {
		$id = $this->current;
		$this->link[$id]->autocommit(FALSE);
	}
	/**
	 * 事务：添加一句
	 * @access public
	 * @param string $sql
	 */
	public function addOne($sql) {
		$id = $this->current;
		$this->link[$id]->query($this->setQuery($sql, $id));
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
		$this->link[$id]->rollback();
	}
	/**
	 * 析构函数，自动关闭
	 * @access public
	 */
	public function __destruct() {
		foreach ($this->link as $link) {
			if (method_exists($link, 'close')) {
				@$link->close();
			}
		}
	}
}