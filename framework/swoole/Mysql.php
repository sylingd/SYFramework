<?php

/**
 * 异步MySQL支持类（需要Swoole和MySQLi）
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Library
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015-2016 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\lib\async;
use \Sy;
use \mysqli;
use \sy\lib\YHtml;
use \sy\base\SYException;
use \sy\base\SYDException;

class YMysql {
	protected $dbtype = 'MySQL';
	protected $link = [];
	protected $dbInfo = [];
	protected $result = [];
	protected static $_instance = NULL;
	protected static $current = 'default';
	public static function i($id = 'default') {
		if (static::$_instance === NULL) {
			static::$_instance = new static;
		}
		static::$_instance->current = $id;
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
		if (!extension_loaded('swoole')) {
			throw new SYException('Extension "Swoole" is required', '10027');
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
			throw new SYDException(YHtml::encode($this->link[$id]->connect_error), $this->dbtype, 'NULL');
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
	 * 执行查询
	 * @access public
	 * @param string $key
	 * @param string $sql SQL语句
	 * @param array $data 参数
	 * @param callable $callback 回调参数
	 */
	public function query($sql, $data = NULL, $callback = NULL) {
		$id = $this->current;
		$sql = $this->setQuery($sql);
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$v = addslashes($v);
				$k = is_int($k) ? '?' : ':' . $k;
				$sql = str_replace($k, "'$v'", $sql, 1);
			}
		}
		if ($callback === NULL) {
			$callback = function($db, $r) {};
		}
		swoole_mysql_query($this->link[$id], $sql, $callback);
	}
	/**
	 * 析构函数，自动关闭
	 * @access public
	 */
	public function __destruct() {
		foreach ($this->link as $link) {
			if (is_object($link) && method_exists($link, 'close')) {
				@$link->close();
			}
		}
	}
}
