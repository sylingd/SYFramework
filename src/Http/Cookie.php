<?php
/**
 * Cookie类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2015-2026 ShuangYa
 * @license Apache License 2.0
 */
namespace Sy\Http;

use Sy\App;
use Sy\Exception\Exception;

class Cookie {
	/**
	 * 设置Cookie
	 * @access public
	 * @param array $param
	 * @param string $param[name] 名称
	 * @param string $param[value] 内容
	 * @param int $param[expire] 过期时间，-1为失效，不传递或0为SESSION，其他为当前时间+$expire
	 * @param string $param[path] 若不传递，则从config读取
	 * @param string $param[domain] 若不传递，则从config读取
	 * @param boolean $param[https] 是否仅https传递，默认根据当前URL设置
	 * @param boolean $param[httponly] 是否为httponly
	 */
	public static function set($param) {
		$name = App::$config->get('cookie.prefix', '') . $param['name'];
		//处理过期时间
		if (!isset($param['expire']) || $param['expire'] === 0) {
			$expire = 0;
		} elseif ($param['expire'] === -1) {
			$expire = time() - 3600;
		} else {
			$expire = time() + $param['expire'];
		}
		//其他参数的处理
		!isset($param['path']) && $param['path'] = App::$config->get('cookie.path', '');
		!isset($param['domain']) && $param['domain'] = App::$config->get('cookie.domain', '');
		!isset($param['httponly']) && $param['httponly'] = false;
		//HTTPS
		if (!isset($param['https'])) {
			if ($_SERVER['HTTPS'] === 'on') {
				$param['https'] = true;
			} else {
				$param['https'] = false;
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
		$name = App::$config->get('cookie.prefix') . $name;
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL;
	}
	/**
	 * Cookie是否存在
	 * @access public
	 * @param string $name
	 * @return string
	 */
	public static function exists($name) {
		$name = App::$config->get('cookie.prefix') . $name;
		return isset($_COOKIE[$name]);
	}
}
