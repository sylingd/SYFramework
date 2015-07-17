<?php

/**
 * Redis数据库类
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

class YRedis {
	//数据库连�?
	private $link = null;
	private $connect_info = null;
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
		if (!class_exists('Redis', false)) {
			throw new SYException('不存在Redis�?, '10007');
		}
		if (isset(Sy::$app['redis'])) {
			$this->setParam(Sy::$app['redis']);
		}
	}
	/**
	 * 连接Redis
	 * @access private
	 */
	private function connect() {
		$this->link = new Redis;
		$this->link->connect($this->connect_info['host'], $this->connect_info['port']);
	}
	/**
	 * 设置Server并连�?
	 * @access public
	 * @param array $param Redis服务�?
	 */
	public function setParam($param) {
		$this->connect_info = $param;
		$this->link = null;
		$this->connect();
	}
	/**
	 * 处理Key
	 * @access private
	 * @param string $key Key
	 * @return string
	 */
	private function setQuery($key) {
		return $this->connect_info['prefix'] . $key;
	}
	/**
	 * Get
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->link->get($this->setQuery($key));
	}
	/**
	 * 析构函数，用于自动断开连接
	 * @access public
	 */
	public function __destruct() {
		if ($this->link !== null) {
			@$this->link->close();
		}
	}
}
