<?php

/**
 * 安全相关类
 * 包括内容：XSS，CSRF，密码安全
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
use \sy\lib\YCookie;
use \sy\base\SYException;

class YSecurity {
	protected static $csrf_config = ['tokenName' => '_csrf_token', 'cookieName' => '_csrf_token'];
	protected static $csrf_hash = NULL;
	/**
	 * csrf验证
	 * @param boolean $show_error 验证不通过时，是否直接报错
	 * @return boolean
	 */
	public static function csrfVerify($show_error = TRUE) {
		//仅POST需要验证csrf
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
			static::csrfSetCookie();
			return TRUE;
		}
		if (!isset($_POST[static::$csrf_config['tokenName']]) || YCookie::get(static::$csrf_config['cookieName']) === NULL || ($_POST[static::$csrf_config['tokenName']] !== YCookie::get(static::$csrf_config['cookieName']))) {
			if ($show_error) {
				Sy::httpStatus('403', TRUE);
			} else {
				return FALSE;
			}
		}
		unset($_POST[static::$csrf_config['tokenName']]);
		//每次有POST提交都重新生成csrf_hash
		unset($_COOKIE[static::$csrf_config['cookieName']]);
		static::$csrf_hash = NULL;
		static::csrfCreateHash();
		static::csrfSetCookie();
		return TRUE;
	}
	/**
	 * 生成/获取csrf_hash
	 * @return string
	 */
	protected static function csrfCreateHash() {
		if (static::$csrf_hash === NULL) {
			$cookie_hash = YCookie::get(static::$csrf_config['cookieName']);
			if ($cookie_hash !== NULL && preg_match('/^[0-9a-f]{32}$/iS', $cookie_hash)) {
				return static::$csrf_hash = $cookie_hash;
			}
			static::$csrf_hash = md5(uniqid(mt_rand(), TRUE));
		}
		return static::$csrf_hash;
	}
	/**
	 * 设置Cookie
	 * @access public
	 */
	public static function csrfSetCookie() {
		YCookie::set(['name' => static::$csrf_config['cookieName'], 'value' => static::csrfCreateHash(), 'httponly' => TRUE]);
	}
	/**
	 * 进行可逆加密
	 * @access public
	 */
}
