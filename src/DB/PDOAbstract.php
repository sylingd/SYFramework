<?php
/**
 * PDO基本类
 * 注意：此为抽象类，无法被实例化
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link https://www.sylibs.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy\DB;

use \PDOException;
use Sy\App;
use Sy\Exception\Exception;
use Sy\Exception\DBException;

abstract class PDOAbstract {
	protected $connection = null;
	protected $config = null;
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
			throw new SYException('Class "PDO" is required');
		}
		$this->autoConnect();
	}
	/**
	 * 设置Server
	 * @access public
	 * @param array $param MySQL服务器参数
	 * @param string $id
	 */
	public function setParam($param) {
		$this->config = $param;
		$this->connection = NULL;
		$this->connect();
	}
	/**
	 * 获取最后产生的ID
	 * @access public
	 * @param string $id
	 * @return int
	 */
	public function getLastId() {
		return intval($this->connection->lastInsertId());
	}
	/**
	 * 执行查询
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数
	 * @return array
	 */
	public function query($sql, $data = NULL) {
		$st = $this->connection->prepare($sql);
		if ($st === FALSE) {
			$e = $this->connection->errorInfo();
			throw new DBException($e[2]);
		}
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				if (is_numeric($k)) {
					$st->bindValue($k + 1, $v);
				} else {
					$st->bindValue($k, $v);
				}
			}
		}
		try {
			$r = $st->execute();
			if ($r === FALSE) {
				$e = $st->errorInfo();
				throw new DBException($e[2]);
			}
		} catch (\PDOException $e) {
			throw new DBException($e->getMessage());
		}
		$st->setFetchMode(\PDO::FETCH_ASSOC);
		return $st->fetchAll();
	}
	public function getConnection() {
		return $this->connection;
	}
}