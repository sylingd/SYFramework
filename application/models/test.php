<?php
/**
 * 模块示例
 * 
 * @author ShuangYa
 * @package Demo
 * @category Model
 * @link http://www.sylingd.com/
 */

namespace demo\model;

use \sy\lib\YHtml;

class Test {
	static $_instance = NULL;
	static public function i() {
		if (self::$_instance === NULL) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	public function foo($str) {
		return YHtml::encode(YHtml::css($str));
	}
}