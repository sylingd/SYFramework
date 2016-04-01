<?php

/**
 * Mongo支持类
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
//Mongo主类
use \MongoClient;
//Mongo异常
use \MongoException;
use \MongoResultException;
use \MongoCursorException;
use \MongoCursorTimeoutException;
use \MongoConnectionException;
use \MongoGridFSException;
use \MongoDuplicateKeyException;
use \MongoProtocolException;
use \MongoExecutionTimeoutException;
use \MongoWriteConcernException;
//其他异常
use \Exception;
use \sy\base\SYException;
use \sy\base\SYDException;

class YMongo {
	protected $dbtype = 'MongoDB';
	protected $link = [];
	protected $dbInfo = [];
	public $linkDB = [];
	public $dbObject = [];
	public $collectionObject = [];
	public $lastError = [];
	protected $current;
	static protected $_instance = NULL;
	static public function i($id = 'default') {
		if (static::$_instance === NULL) {
			static::$_instance = new static($id);
		} else {
			static::$_instance->setCurrent($id);
		}
		return static::$_instance;
	}
	/**
	 * 设置当前Server
	 * @param string $current
	 */
	public function setCurrent($current) {
		$this->current = $current;
		$this->dbObject[$id] = NULL;
		$this->collectionObject[$id] = NULL;
	}
	/**
	 * 构造函数，自动连接
	 * @access public
	 */
	public function __construct($current) {
		if (!extension_loaded('mongo')) {
			throw new SYException('Extension "mongo" is required', '10024');
		}
		$this->setCurrent($current);
		if (isset(Sy::$app['mongo']) && $current === 'default') {
			$this->setParam(Sy::$app['mongo']);
		}
	}
	/**
	 * 连接到MongoDB
	 * @access protected
	 */
	protected function connect() {
		$id = $this->current;
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
			throw new SYDException($e->getMessage(), $this->dbtype, '');
		}
		if (isset($this->dbInfo[$id]['name'])) {
			try {
				$this->linkDB[$id] = $this->link[$id]->selectDB($this->dbInfo[$id]['name']);
			} catch (Exception $e) {
				throw new SYDException($e->getMessage(), $this->dbtype, 'SELECT DB ' . $this->dbInfo[$id]['name']);
			}
		}
	}
	/**
	 * 设置Server
	 * @access public
	 * @param array $param MongoDB服务器参数
	 */
	public function setParam($param) {
		$id = $this->current;
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
		$id = $this->current;
		return str_replace('#@__', $this->dbInfo[$id]['prefix'], $k);
	}
	/**
	 * 选择一个数据库
	 * @access public
	 * @param string $name 数据库名
	 * @return object(this)
	 */
	public function db($name) {
		$id = $this->current;
		try {
			$this->dbObject[$id] = $this->link[$id]->selectDB($name);
		} catch (Exception $e) {
			throw new SYDException($e->getMessage(), $this->dbtype, 'SELECT DB ' . $name);
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
		$id = $this->current;
		if (isset($this->dbObject[$id]) && is_object($this->dbObject[$id])) {
			$db = $this->dbObject[$id];
		} elseif (isset($this->linkDB[$id])) {
			$db = $this->linkDB[$id];
		} else {
			throw new SYDException('You must select a database', $this->dbtype, '');
		}
		$collection = $this->setQuery($collection);
		try {
			$this->collectionObject[$id] = $db->selectCollection($collection);
		} catch (Exception $e) {
			throw new SYDException($e->getMessage(), $this->dbtype, 'SELECT Collection ' . $collection);
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
		$id = $this->current;
		try {
			$r = call_user_func_array([$this->collectionObject[$id], $method], $param);
		} catch (Exception $e) {
			$this->setError($e->getMessage(), 'executeCommand - ' . $method, $param);
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
		$id = $this->current;
		return $this->lastError[$id];
	}
	protected function setError($message, $method, $param) {
		$id = $this->current;
		$this->lastError[$id] = [
			'message' => $message,
			'method' => $method,
			'param' => $param
		];
	}
	/**
	 * 执行读操作
	 * @access public
	 * @param array $filter
	 * @return array
	 */
	public function get($filter, $option) {
		$id = $this->current;
		try {
			if (!isset($option['projection'])) {
				$option['projection'] = [];
			}
			$cursor = $this->collectionObject[$id]->find($filter, $option['projection']);
			$cursor->maxTimeMS($this->option[$id]['timeout']);
			if (isset($option['limit'])) {
				$cursor->limit($option['limit']);
			}
			if (isset($option['sort'])) {
				$cursor->sort($option['sort']);
			}
			if (isset($option['skip'])) {
				$cursor->skip($option['skip']);
			}
		} catch (\Exception $e) {
			$this->setError($e->getMessage(), 'GET', [$filter, $option]);
			return FALSE;
		}
		return $cursor;
	}
	public function getOne($filter, $option = []) {
		$id = $this->current;
		try {
			if (!isset($option['projection'])) {
				$option['projection'] = [];
			}
			$result = $this->collectionObject[$id]->findOne($filter, $option['projection'], ['maxTimeMS' => $this->option[$id]['timeout']]);
		} catch (\Exception $e) {
			$this->setError($e->getMessage(), 'GET', [$filter, $option]);
			return FALSE;
		}
		return $result;
	}
	/**
	 * 删除记录
	 * @access public
	 * @param array $filter
	 * @return object
	 */
	public function delete($filter, $justOne = FALSE) {
		$id = $this->current;
		try {
			$this->collectionObject[$id]->remove($filter, [
				'justOne' => $justOne,
				'safe' => $this->option[$id]['safe'],
				'timeout' => $this->option[$id]['timeout']
			]);
		} catch (\Exception $e) {
			$this->setError($e->getMessage(), 'DELETE', $filter);
			return FALSE;
		}
		return $result;
	}
	/**
	 * 更新记录
	 * @access public
	 * @param array $filter 查询条件
	 * @param array $to 设置为
	 * @param boolean $justOne 是否仅更新一条记录
	 * @param boolean $autoInsert 在没有匹配记录时，是否自动插入
	 * @return object
	 */
	public function update($filter, $to, $justOne = FALSE, $autoInsert = FALSE) {
		$id = $this->current;
		try {
			$this->collectionObject[$id]->update($filter, ['$set' => $to], [
				'multiple' => !$justOne,
				'safe' => $this->option[$id]['safe'],
				'upsert' => $autoInsert
			]);
		} catch (\Exception $e) {
			$this->setError($e->getMessage(), 'UPDATE', [$filter, $to]);
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * 增加记录
	 * @access public
	 * @param array $data
	 * @return array
	 */
	public function insert($data) {
		$id = $this->current;
		try {
			$oid = $this->collectionObject[$id]->insert($data, [
				'safe' => $this->option[$id]['safe'],
				'timeout' => $this->option[$id]['timeout']
			]);
			if (isset($data['_id'])) {
				$oid = $data['_id'];
			}
		} catch (\Exception $e) {
			$this->setError($e->getMessage(), 'INSERT', $data);
			return FALSE;
		}
		return [strval($oid), $result];
	}
	/**
	 * 魔术方法调用
	 */
	public function __call($method, $args) {
		$id = $this->current;
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
			throw new SYDException("Method '$method' not extsts", $this->dbtype, '');
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
	//静态方法：实例化ID
	public static function MongoID($id) {
		if (!\MongoId::isValid($id)) {
			return FALSE;
		}
		return new \MongoId($id);
	}
}
