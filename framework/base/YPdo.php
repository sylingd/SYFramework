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
use Sy;
use \PDO;
use \sy\base\SYException;
use \sy\lib\YHtml;

abstract class YPdo {
	protected $link = NULL;
	protected $connect_info = NULL;
	protected $result;
	static $_instance = NULL;
	static public function _i() {
		if (static::$_instance === NULL) {
			static::$_instance = new static;
		}
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
	 */
	abstract protected function connect();
	/**
	 * 抽象函数：获取一个结果
	 * @access public
	 * @param string $sql
	 * @param array $data
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
		$this->autoConnect();
	}
	/**
	 * 设置Server
	 * @access public
	 * @param array $param MySQL服务器参数
	 */
	public function setParam($param) {
		$this->connect_info = $param;
		$this->link = NULL;
		$this->connect();
	}
	/**
	 * 处理Key
	 * @access protected
	 * @param string $sql
	 * @return string
	 */
	protected function setQuery($sql) {
		return str_replace('#@__', $this->connect_info['prefix'], $sql);
	}
	/**
	 * 获取所有结果
	 * @access public
	 * @param string $key
	 * @return array
	 */
	public function getAll($key) {
		$rs = $this->result[$key];
		$rs->setFetchMode(PDO::FETCH_ASSOC);
		$rs = $rs->fetchAll();
		return $rs;
	}
	/**
	 * 释放结果
	 * @access public
	 * @param string $key
	 */
	public function free($key) {
		$this->result[$key] = NULL;
	}
	/**
	 * 获取最后产生的ID
	 * @access public
	 * @return int
	 */
	public function getLastId() {
		return intval($this->link->lastInsertId());
	}
	/**
	 * 获取一个结果
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function getArray($key) {
		if (!isset($this->result[$key]) || empty($this->result[$key])) {
			return NULL;
		}
		$rs = $this->result[$key];
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
	 */
	public function query($key, $sql, $data = NULL) {
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
		}
		catch (PDOException $e) {
			throw new SYDBException(YHtml::encode($e->getMessage()), $this->dbtype, $sql);
		}
		$this->result[$key] = $st;
	}
}