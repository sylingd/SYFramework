<?php
/**
 * 模块示例
 * 
 * @author ShuangYa
 * @package Demo
 * @category Model
 * @link http://www.sylingd.com/
 */

use \sy\lib\YHtml;

class MTest {
	static $_instance = null;
	static public function _i() {
		if (self::$_instance === null) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	public function foo($str) {
		return YHtml::encode(YHtml::css($str));
	}
}