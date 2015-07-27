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
	}
	public function __toString() {
		if (!Sy::$debug) {
			return $this->toString_notDebug();
		}
		$r = '<p><strong>SY Framework</strong></p>';
		$r .= '<p>Error occur in ' . $this->dbtype . '</p>';
		$r .= '<p>Error info: ' . $this->getMessage() . '</p>';
		$r .= '<p>Execute: ' . $this->execute . '</p>';
		$r .= '<p>in ' . $this->getFile() . ' on line ' . $this->getLine() . '</p>';
		return $r;
	}
}