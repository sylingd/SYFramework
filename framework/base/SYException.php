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

//普通异常类
class SYException extends \Exception {
	public function __toString() {
		if (!SY::$app['debug'] && !defined('SY_DEBUG')) {
			return $this->toString_notDebug();
		}
		$r = '<p><strong>SY Framework</strong></p>';
		$r .= '<p>[' . $this->getCode() . ']' . $this->getMessage();
		$r .= '</p><p>in ' . $this->getFile() . ' on line ' . $this->getLine() . '</p>';
		return $r;
	}
	protected function toString_notDebug() {
		$r = '<p><strong>SY Framework</strong></p>';
		$r .= '<p>Please contact to the Admin for helps</p>';
		$r .= '<p>If you are Admin,please enable Debug Mode in config.php</p>';
		return $r;
	}
}
