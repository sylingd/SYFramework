<?php

/**
 * i18n基本类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=frameworkr&type=license
 */

namespace sy\base;
use Sy;
use \sy\base\SYException;

class i18n {
	//所有语言
	protected static $language = NULL;
	//当前语言
	protected static $now_language = NULL;
	/**
	 * 设置当前语言
	 * @access public
	 * @param string $l 语言
	 */
	public function setLanguage($i) {
		if (static::$now_language === $i) {
			return;
		}
		$path = Sy::$appDir . 'i18n/' . $i . '.php';
		if (!is_file($path)) {
			throw new SYException('The language file not exists', '10011');
		}
		static::$language = require ($path);
	}
	/**
	 * 获得文字
	 * @access public
	 * @param string $key
	 * @param array $params 自动替换的参数
	 * @return string
	 */
	public static function get($key, $params = NULL) {
		if (static::$language === NULL) {
			static::setLanguage(Sy::$app['language']);
		}
		if (!isset(static::$language[$key])) {
			return '';
		}
		$r = static::$language[$key];
		$params = (array )$params;
		foreach ($params as $k => $v) {
			$r = str_replace('{{' . $k . '}}', $v, $r);
		}
		return $r;
	}
}
