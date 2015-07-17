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

//普通异常类
class SYException extends Exception {
	public function __toString() {
		if (SY::$app['debug']) {
			$r = '<p><strong>SY Framework</strong></p>';
			$r .= '<p>[' . $this->getCode() . ']' . $this->getMessage();
			$r .= '</p><p>于 ' . $this->getFile() . '，第' . $this->getLine() . '行</p>';
		} else {
			$r = $this->toString_notDebug();
		}
		return $r;
	}
	protected function toString_notDebug() {
		$r = '<p><strong>系统内部错误</strong></p>';
		$r .= '<p>请联系管理员获得帮助</p>';
		$r .= '<p>如果您是管理员，请修改config.php，打开调试模式</p>';
		return $r;
	}
}

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
		if (SY::$app['debug']) {
			$r = '<p><strong>SY Framework</strong></p>';
			$r .= '<p>错误发生于' . $this->dbname . '</p>';
			$r .= '<p>Execute:' . $this->execute . '</p>';
			$r .= '<p>于 ' . $this->getFile() . '，第' . $this->getLine() . '行</p>';
		} else {
			$r = $this->toString_notDebug();
		}
		return $r;
	}
}
