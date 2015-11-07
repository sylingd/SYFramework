<?php

/**
 * MongoDB支持类
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
use \MongoClient;
use \sy\base\SYException;
use \sy\base\SYDBException;

class YMongo {
	protected $dbtype = 'MongoDB';
	protected $link = [];
	protected $dbInfo = [];
	public $linkDB = [];
	public $dbObject = [];
	public $collectionObject = [];
	public $lastError = [];
	static protected $_instance = NULL;
	static protected $current = 'default';
	static public function i($id = 'default') {
		if (static::$_instance === NULL) {
			static::$_instance = new static;
		}
		static::$current = $id;
		static::$_instance->dbObject[$id] = NULL;
		static::$_instance->collectionObject[$id] = NULL;
		static::$_instance->lastError[$id] = NULL;
		return static::$_instance;
	}
	/**
	 * 构造函数，自动连接
	 * @access public
	 */
	public function __construct() {
		if (!class_exists('MongoClient', FALSE)) {
			throw new SYException('Class "MongoClient" is required', '10022');
		}
		if (isset(Sy::$app['mongo']) && static::$current === 'default') {
			$this->setParam(Sy::$app['mongo']);
		}
	}
	/**
	 * 连接到Redis
	 * @access protected
	 */
	protected function connect() {
		$id = static::$current;
		$dsn = 'mongodb://';
		//多个服务器
		if (is_array($this->dbInfo[$id]['server'])) {
			foreach ($this->dbInfo[$id]['server'] as $server) {
				$dsn .= $server['host'] . ':' . $server['port'] . ',';
			}
			$dsn = rtrim($dsn, ',');
		} else {
			$dsn .= $this->dbInfo[$id]['host'] . ':' . $this->dbInfo[$id]['port'];
		}
		$option = ['connect' => TRUE];
		//密码验证
		if (isset($this->dbInfo[$id]['user']) && !empty($this->dbInfo[$id]['user'])) {
			$option['username'] = $this->dbInfo[$id]['user'];
			$option['password'] = $this->dbInfo[$id]['password'];
		}
		try {
			$this->link[$id] = new MongoClient($dsn, $option);
		} catch (MongoConnectionException $e) {
			throw new SYDBException($e->getMessage(), $this->dbtype, '');
		}
		if (isset($this->dbInfo[$id]['name'])) {
			try {
				$this->linkDB[$id] = $this->link[$id]->selectDB($this->dbInfo[$id]['name']);
			} catch (Exception $e) {
				throw new SYDBException($e->getMessage(), $this->dbtype, 'SELECT DB ' . $this->dbInfo[$id]['name']);
			}
		}
	}
	/**
	 * 设置Server
	 * @access public
	 * @param array $param Redis服务器参数
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
	 * @param string $k
	 * @return string
	 */
	protected function setQuery($k) {
		$id = static::$current;
		return str_replace('#@__', $this->dbInfo[$id]['prefix'], $k);
	}
	/**
	 * 选择一个数据库
	 * @access public
	 * @param string $name 数据库名
	 * @return object(this)
	 */
	public function db($name) {
		$id = static::$current;
		try {
			$this->dbObject[$id] = $this->link[$id]->selectDB($name);
		} catch (Exception $e) {
			throw new SYDBException($e->getMessage(), $this->dbtype, 'SELECT DB ' . $name);
		}
		return $this;
	}
	/**
	 * 查询
	 * @access public
	 * @param string $collection 集合名
	 * @return object(this)
	 */
	public function select($collection) {
		$id = static::$current;
		if (isset($this->dbObject[$id]) && is_object($this->dbObject[$id])) {
			$db = $this->dbObject[$id];
		} elseif (isset($this->linkDB[$id])) {
			$db = $this->linkDB[$id];
		} else {
			throw new SYDBException('You must select a database', $this->dbtype, '');
		}
		$collection = $this->setQuery($collection);
		try {
			$this->collectionObject[$id] = $db->selectCollection($collection);
		} catch (Exception $e) {
			throw new SYDBException($e->getMessage(), $this->dbtype, 'SELECT Collection ' . $collection);
		}
		return $this;
	}
	/**
	 * 执行查询
	 * @access protected
	 * @param string $method 方法名
	 * @param array $param 参数
	 * @return mixed
	 */
	protected function executeCommand($method, $param = []) {
		$id = static::$current;
		try {
			$r = call_user_func_array([$this->collectionObject[$id], $method], $param);
		} catch (Exception $e) {
			$this->lastError[$id] = [
				'message' => $e->getMessage(),
				'method' => $method,
				'param' => $param
			];
			return FALSE;
		}
		return $r;
	}
	/**
	 * 获取最后一次错误的信息
	 * @access public
	 * @return array
	 */
	public function getLastError() {
		$id = static::$current;
		return $this->lastError[$id];
	}
	/**
	 * 魔术方法调用
	 */
	public function __call($method, $args) {
		$id = static::$current;
		if (isset($this->collectionObject[$id]) && is_object($this->collectionObject[$id]) && method_exists($this->collectionObject[$id], $method)) {
			//collection
			return $this->executeCommand($method, $args);
		} elseif ((isset($this->dbObject[$id]) && is_object($this->dbObject[$id]) && method_exists($this->dbObject[$id]))) {
			//db
			if ($method === 'dropCollection') {
				return $this->select($args[0])->drop();
			} else {
				return call_user_func_array([$this->dbObject[$id], $method], $args);
			}
		} elseif (isset($this->linkDB[$id]) && method_exists($this->linkDB[$id])) {
			//默认db
			if ($method === 'dropCollection') {
				return $this->select($args[0])->drop();
			} else {
				return call_user_func_array([$this->linkDB[$id], $method], $args);
			}
		} elseif (method_exists($this->link[$id], $method)) {
			return call_user_func_array([$this->link[$id], $method], $args);
		} else {
			throw new SYDBException("Method '$method' not extsts", $this->dbtype, '');
		}
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
