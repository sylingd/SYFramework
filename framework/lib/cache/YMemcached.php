<?php

/**
 * Memcached支持类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Library
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\lib\cache;
use \Memcached;
use \Sy;

class YMemcached {
	protected $dbtype = 'Memcached';
	protected $link = NULL;
	static protected $_instance = NULL;
	static public function i() {
		if (static::$_instance === NULL) {
			static::$_instance = new static;
		}
		return static::$_instance;
	}
	public function __construct() {
		if (!class_exists('Memcached', FALSE)) {
			throw new SYException('Class "Memcached" is required', '10025');
		}
		if (isset(Sy::$app['memcached'])) {
			$this->setParam(Sy::$app['memcached']);
		}
	}
	/**
	 * 自动连接
	 * @access public
	 */
	public function connect() {
		if ($this->link === NULL) {
			$this->link = new Memcached;
		}
		//多个服务器
		if (is_array($this->info['server'])) {
			$this->link->addServers($server['host'], $server['port']);
		} else {
			$this->link->addServer($this->info['host'], $this->info['port']);
		}
	}
	/**
	 * 设置Server
	 * @access public
	 * @param array $param MongoDB服务器参数
	 */
	public function setParam($param) {
		$this->info = $param;
		$this->connect();
	}
	/**
	 * 处理Key
	 * @access protected
	 * @param string $k
	 * @return string
	 */
	protected function setQuery($k) {
		return str_replace('#@__', $this->info['prefix'], $k);
	}
	/**
	 * 魔术方法
	 */
	public function __call($method, $args) {
		if (!method_exists($this->link, $method)) {
			throw new SYDException("Method '$method' not extsts", $this->dbtype);
		}
		if (in_array($method, ['add', 'append', 'decrement', 'delete', 'get', 'prepend', 'increment', 'replace', 'set', 'touch'], TRUE)) {
			$args[0] = $this->setQuery($args[0]);
		} elseif (in_array($method, ['cas'], TRUE)) {
			$args[1] = $this->setQuery($args[1]);
		} elseif (in_array($method, ['deleteMulti', 'getDelayed', 'getMulti'], TRUE)) {
			foreach ($args[0] as $k => $v) {
				$args[0][$k] = $this->setQuery($args[0][$k]);
			}
		} elseif ($method === 'setMulti') {
			foreach ($args[0] as $k => $v) {
				unset($args[0][$k])
				$args[0][$this->setQuery($k)] = $v;
			}
		}
		return call_user_func_array([$this->link, $method], $args);
	}
}