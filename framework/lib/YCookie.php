<?php

/**
 * Cookie类
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
use \sy\base\SYException;

class YCookie {
	/**
	 * 设置Cookie
	 * @access public
	 * @param array $param
	 * @param string $param[name] 名称
	 * @param string $param[value 内容
	 * @param int $param[expire] 过期时间，-1为失效，0为SESSION，不传递为从config读取，其他为当前时间+$expire
	 * @param string $param[path] 若不传递，则从config读取
	 * @param string $param[domain] 若不传递，则从config读取
	 * @param boolean $param[https] 是否仅https传递，默认根据当前URL设置
	 * @param boolean $param[httponly] 是否为httponly
	 */
	public static function set($param) {
		$name = Sy::$app['cookie']['prefix'] . $param['name'];
		//处理过期时间
		if (!isset($param['expire'])) {
			$expire = time() + Sy::$app['cookie']['expire'];
		} elseif ($param['expire'] === -1) {
			$expire = time() - 3600;
		} elseif ($param['expire'] === 0) {
			$expire = 0;
		} else {
			$expire = time() + $expire;
		}
		//其他参数的处理
		!isset($param['path']) && $param['path'] = Sy::$app['cookie']['path'];
		!isset($param['domain']) && $param['domain'] = Sy::$app['cookie']['domain'];
		!isset($param['httponly']) && $param['httponly'] = FALSE;
		//HTTPS
		if (!isset($param['https'])) {
			if ($_SERVER['HTTPS'] === 'on') {
				$param['https'] = TRUE;
			} else {
				$param['https'] = FALSE;
			}
		}
		//设置
		setcookie($name, $param['value'], $expire, $param['path'], $param['domain'], $param['https'], $param['httponly']);
	}
	/**
	 * 获取Cookie
	 * @access public
	 * @param string $name
	 * @return string
	 */
	public static function get($name) {
		$name = Sy::$app['cookie']['prefix'] . $name;
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL;
	}
}
