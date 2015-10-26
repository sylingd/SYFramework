<?php

/**
 * Redis支持类
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
use \Redis;
use \sy\base\SYException;
use \sy\base\SYDBException;

class YRedis {
	private $link = NULL;
	private $dbInfo = NULL;
	private $transaction = NULL;
	static $_instance = NULL;
	static public function _i() {
		if (self::$_instance === NULL) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	/**
	 * 构造函数，自动连接
	 * @access public
	 */
	public function __construct() {
		if (!class_exists('Redis', FALSE)) {
			throw new SYException('Class "Redis" is required', '10022');
		}
		if (isset(Sy::$app['redis'])) {
			$this->setParam(Sy::$app['redis']);
		}
	}
	/**
	 * 连接到Redis
	 * @access private
	 */
	private function connect() {
		$this->link = new Redis;
		$this->link->connect($this->dbInfo['host'], $this->dbInfo['port']);
		if (!empty($this->dbInfo['password'])) {
			$this->link->auth($this->dbInfo['password']);
		}
	}
	/**
	 * 设置Server
	 * @access public
	 * @param array $param Redis服务器参数
	 */
	public function setParam($param) {
		$this->dbInfo = $param;
		$this->link = NULL;
		$this->connect();
	}
	/**
	 * 处理Key
	 * @access private
	 * @param string $sql
	 * @return string
	 */
	private function setQuery($key) {
		if (substr($key, 0, 5) === '@root/') {
			return substr($key, 5);
		} else {
			return $this->dbInfo['prefix'] . $key;
		}
	}
	/**
	 * 使用魔术方法，调用phpredis的方法
	 */
	public function __call($name, $args) {
		if (!method_exists($this->link, $name)) {
			throw new Exception("Method '$name' not exists");
		}
		$name_lower = strtolower($name);
		if (in_array($name_lower, ['mget', 'getmultiple', 'sdiff', 'sinter', 'sunion'], TRUE)) {
			//均为Key的，如mGet
			foreach ($args as $k => $v) {
				$args[$k] = $this->setQuery($v);
			}
		} elseif (!in_array($name_lower, ['mset', 'msetnx', 'migrate', 'sdiffstore', 'sinterstore', 'smove', 'rename', 'renamekey', 'renamenx'], TRUE)) { //不属于特殊处理的
			$args[0] = $this->setQuery($args[0]);
		} else { //特殊处理
			switch ($name_lower) {
				case 'mset':
				case 'msetnx':
					$keys = $args[0];
					$new_keys = [];
					foreach ($keys as $k => $v) {
						$new_k = $this->setQuery($k);
						$new_keys[$new_k] = $v;
					}
					unset($keys);
					$args[0] = $new_keys;
					break;
				case 'migrate':
					$args[2] = $this->setQuery($args[2]);
					break;
				case 'sdiffstore':
				case 'sinterstore':
				case 'sunionstore':
					foreach ($args as $k => $v) {
						if ($k === 0) {
							continue;
						}
						$args[$k] = $this->setQuery($v);
					}
					break;
				case 'rename':
				case 'renamekey':
				case 'renamenx':
				case 'smove':
					$args[0] = $this->setQuery($args[0]);
					$args[1] = $this->setQuery($args[1]);
					break;
			}
		}
		//对事务的支持
		if ($this->transaction === NULL) {
			$r = call_user_func_array([$this->link, $name], $args);
			return $r;
		} else {
			$this->transaction = call_user_func_array([$this->transaction, $name], $args);
		}
	}
	/**
	 * 事务：开始
	 * @access public
	 */
	public function beginTransaction() {
		$this->transaction = $this->link->mulit();
	}
	/**
	 * 事务：提交
	 * @access public
	 */
	public function commit() {
		$r = $this->transaction->exec();
		$this->transaction = NULL;
		return $r;
	}
	/**
	 * 析构函数，自动关闭
	 * @access public
	 */
	public function __destruct() {
		if ($this->link !== NULL) {
			@$this->link->close();
		}
	}
}
