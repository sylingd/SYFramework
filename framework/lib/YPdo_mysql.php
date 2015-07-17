<?php

/**
 * MySQL数据库类（PDO驱动�?
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
use \sy\lib\YHtml;

class YPdo_mysql {
	private $link = NULL;
	private $connect_info = NULL;
	private $result;
	static $_instance = null;
	static public function _i() {
		if (self::$_instance === null) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	/**
	 * 构造函数，用于自动连接
	 * @access public
	 */
	public function __construct() {
		if (!class_exists('PDO', false)) {
			throw new SYException('不存在PDO�?, '10008');
		}
		if (isset(Sy::$app['mysql'])) {
			$this->setParam(Sy::$app['mysql']);
		}
	}
	/**
	 * 设置Server并连�?
	 * @access public
	 * @param array $param MySQL服务�?
	 */
	public function setParam($param) {
		$this->connect_info = $param;
		$this->link = null;
		$this->connect();
	}
	/**
	 * 连接数据�?
	 * @access private
	 */
	private function connect() {
		$dsn = 'mysql:host=' . $this->connect_info['host'] . ';port=' . $this->
			connect_info['port'] . ';dbname=' . $this->connect_info['name'] . ';charset=' .
			Sy::$app['charset'];
		try {
			$this->link = new PDO($dsn, $this->connect_info['user'], $this->connect_info['password']); //初始化PDO
			$this->link->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
			$this->result = array();
		}
		catch (PDOException $e) {
			throw new SYDBException(YHtml::encode($e->getMessage), 2, $dsn);
		}
	}
	/**
	 * 处理Key
	 * @access private
	 * @param string $sql
	 * @return string
	 */
	private function setQuery($sql) {
		return str_replace('#@__', $this->connect_info['prefix'], $sql);
	}
	/**
	 * 获取所有结�?
	 * @access public
	 * @param string $key 结果Key，查询时传�?
	 * @return array
	 */
	public function GetAll($key) {
		$rs = $this->result[$key];
		$rs->setFetchMode(PDO::FETCH_ASSOC);
		$rs = $rs->fetchAll();
		return $rs;
	}
	/**
	 * 释放结果
	 * @access public
	 * @param string $key 结果Key
	 * @return NULL
	 */
	public function free($key) {
		$this->result[$key] = NULL;
	}
	/**
	 * 获取最后产生的ID
	 * @access public
	 * @return int
	 */
	public function GetLastId() {
		return intval($this->link->lastInsertId());
	}
	/**
	 * 获取结果集中的一个结�?
	 * @access public
	 * @param string $key 结果Key
	 * @return mixed
	 */
	public function GetArray($key) {
		if (!isset($this->result[$key]) || empty($this->result[$key])) {
			return NULL;
		}
		$rs = $this->result[$key];
		$rs->setFetchMode(PDO::FETCH_ASSOC);
		$rs = $rs->fetch();
		return $rs;
	}
	/**
	 * 查询主函�?
	 * @access public
	 * @param string $key 结果Key
	 * @param string $sql SQL语句
	 * @param array $data 参数
	 * @return NULL
	 */
	public function Query($key, $sql, $data = NULL) {
		$prepare_sql = $this->setQuery($sql);
		$st = $this->link->prepare($prepare_sql);
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$st->bindValue($k + 1, $v);
			}
		}
		try {
			$r = $st->execute(); //执行
			if ($r === FALSE) {
				throw new SYDBException(YHtml::encode($st->errorInfo()), 2, $sql);
			}
		}
		catch (PDOException $e) {
			throw new SYDBException(YHtml::encode($e->getMessage()), 2, $sql);
		}
		$this->result[$key] = $st;
	}
	/**
	 * 查询出一个结果，仅支持简单的SQL语句
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数
	 * @return array
	 */
	public function GetOne($sql, $data = NULL) {
		if (!preg_match('/limit ([0-9,]+)$/', strtolower($sql))) {
			$sql .= ' LIMIT 0,1';
		}
		$this->Query('one', $sql, $data);
		$r = $this->GetArray('one');
		$this->free('one');
		return $r;
	}
	/**
	 * 事务支持：开始事�?
	 * @access public
	 */
	public function beginTransaction() {
		$this->link->beginTransaction();
	}
	/**
	 * 事务支持：增加语�?
	 * @access public
	 * @param string $sql
	 */
	public function addOne($sql) {
		$this->link->exec($this->setQuery($aql));
	}
	/**
	 * 事务支持：提交事�?
	 * @access public
	 */
	public function commit() {
		$this->link->commit();
	}
	/**
	 * 事务支持：回�?
	 * @access public
	 */
	public function rollback() {
		$this->link->rollBack();
	}
}
