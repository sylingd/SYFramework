<?php

/**
 * Form处理类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Library
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\lib;
use Sy;

class YForm {
	public static $validString = NULL;
	/**
	 * 格式验证
	 * @param string $str 待验证字符串
	 * @param string $type 验证类型
	 * @return boolean
	 */
	public static function valid($str, $type) {
		if (static::$validString === NULL) {
			static::$validString = require (SY_ROOT . 'data/validString.php');
		}
		if (isset(static::$validString[$type])) {
			$m = static::$validString[$type];
			if (is_string($m)) {
				return preg_match($m, $str) ? TRUE : FALSE;
			} else {
				return $m($str);
			}
		} else {
			return TRUE;
		}
	}
}
