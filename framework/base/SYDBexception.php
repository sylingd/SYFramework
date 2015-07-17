<?php

/**
 * 异常类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\base;
use Sy;
use \sy\base\SYException;

//数据库相关异常
class SYDBException extends SYException {
	protected $dbtype;
	protected $dbname;
	protected $execute;
	public function __construct($message, $dbtype, $execute) {
		$this->message = $message;
		$this->dbtype = $dbtype;
		$this->execute = $execute;
		switch ($dbtype) {
			case 1:
				$this->dbname = 'Redis';
				break;
			case 2:
				$this->dbname = 'MySQL';
				break;
			default:
				$this->dbname = 'Unknow';
				break;
		}
	}
	public function __toString() {
		if (!SY::$app['debug'] && !defined('SY_DEBUG')) {
			return $this->toString_notDebug();
		}
		$r = '<p><strong>SY Framework</strong></p>';
		$r .= '<p>错误发生于' . $this->dbname . '</p>';
		$r .= '<p>Execute:' . $this->execute . '</p>';
		$r .= '<p>于 ' . $this->getFile() . '，第' . $this->getLine() . '行</p>';
		return $r;
	}
}