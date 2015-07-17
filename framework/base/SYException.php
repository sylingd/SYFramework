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
		$r .= '</p><p>于 ' . $this->getFile() . '，第' . $this->getLine() . '行</p>';
		return $r;
	}
	protected function toString_notDebug() {
		$r = '<p><strong>系统内部错误</strong></p>';
		$r .= '<p>请联系管理员获得帮助</p>';
		$r .= '<p>如果您是管理员，请修改config.php，打开调试模式</p>';
		return $r;
	}
}
