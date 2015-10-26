<?php

/**
 * PDO基本类
 * 注意：此为抽象类，无法被实例化
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=frameworkr&type=license
 */

namespace sy\base;
use \Sy;
use \PDO;
use \sy\base\SYException;
use \sy\lib\YHtml;

abstract class YPdo {
	protected $link = [];
	protected $dbInfo = [];
	protected $result = [];
	static protected $_instance = NULL;
	static protected $current = 'default';
	static public function i($id = 'default') {
		if (static::$_instance === NULL) {
			static::$_instance = new static;
		}
		static::$current = $id;
		return static::$_instance;
	}
	/**
	 * 抽象函数：自动连接
	 * @access protected
	 */
	abstract protected function autoConnect();
	/**
	 * 抽象函数：连接
	 * @access protected
	 * @param string $id
	 */
	abstract protected function connect();
	/**
	 * 抽象函数：获取一个结果
	 * @access public
	 * @param string $sql
	 * @param array $data
	 * @param string $id 连接ID
	 * @return array
	 */
	abstract public function getOne($sql, $data);
	/**
	 * 构造函数，自动连接
	 * @access public
	 */
	public function __construct() {
		if (!class_exists('PDO', FALSE)) {
			throw new SYException('Class "PDO" is required', '10021');
		}
		if (static::$current === 'default') {
			$this->autoConnect();
		}
	}
	/**
	 * 设置Server
	 * @access public
	 * @param array $param MySQL服务器参数
	 * @param string $id
	 */
	public function setParam($param) {
		$id = static::$current;
		$this->dbInfo[$id] = $param;
		$this->link[$id] = NULL;
		$this->connect();
	}
	/**
	 * 处理Key
	 * @access protected
	 * @param string $sql
	 * @return string
	 */
	protected function setQuery($sql) {
		$id = static::$current;
		return str_replace('#@__', $this->dbInfo[$id]['prefix'], $sql);
	}
	/**
	 * 获取所有结果
	 * @access public
	 * @param string $key
	 * @param string $id
	 * @return array
	 */
	public function getAll($key) {
		$id = static::$current;
		$rs = $this->result[$id][$key];
		$rs->setFetchMode(PDO::FETCH_ASSOC);
		$rs = $rs->fetchAll();
		return $rs;
	}
	/**
	 * 释放结果
	 * @access public
	 * @param string $key
	 * @param string $id
	 */
	public function free($key) {
		$id = static::$current;
		$this->result[$id][$key] = NULL;
	}
	/**
	 * 获取最后产生的ID
	 * @access public
	 * @param string $id
	 * @return int
	 */
	public function getLastId() {
		$id = static::$current;
		return intval($this->link[$id]->lastInsertId());
	}
	/**
	 * 获取一个结果
	 * @access public
	 * @param string $key
	 * @param string $id
	 * @return mixed
	 */
	public function getArray($key) {
		$id = static::$current;
		if (!isset($this->result[$id][$key]) || empty($this->result[$id][$key])) {
			return NULL;
		}
		$rs = $this->result[$id][$key];
		$rs->setFetchMode(PDO::FETCH_ASSOC);
		$rs = $rs->fetch();
		return $rs;
	}
	/**
	 * 执行查询
	 * @access public
	 * @param string $key
	 * @param string $sql SQL语句
	 * @param array $data 参数
	 * @param string $id
	 */
	public function query($key, $sql, $data = NULL) {
		$id = static::$current;
		$prepare_sql = $this->setQuery($sql);
		$st = $this->link->prepare($prepare_sql);
		if ($st === FALSE) {
			throw new SYDBException('Failed to prepare SQL', $this->dbtype, $sql);
		}
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$st->bindValue($k + 1, $v);
			}
		}
		try {
			$r = $st->execute();
			if ($r === FALSE) {
				throw new SYDBException(YHtml::encode($st->errorInfo()), $this->dbtype, $sql);
			}
		} catch (PDOException $e) {
			throw new SYDBException(YHtml::encode($e->getMessage()), $this->dbtype, $sql);
		}
		if (!empty($key)) {
			$this->result[$id][$key] = $st;
		}
	}
}