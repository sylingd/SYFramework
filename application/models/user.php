<?php

/**
 * 用户模块
 * 
 * @author ShuangYa
 * @package EUser
 * @category Model
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=euser&type=license
 */

use \sy\lib\YRedis;

class MUser {
	/**
	 * 获取登录状�?
	 * @access public
	 * @return mixed
	 */
	public function getLoginStatus() {
		static $login = NULL;
		if ($login !== NULL) {
			return $login;
		}
		$cookie_pre = option::_i()->get('cookie_pre');
		if (isset($_COOKIE[$cookie_pre . 'auth'])) {
			$auth = $_COOKIE[$cookie_pre . 'auth'];
			$auth = unserialize(YRedis::_i()->get('auth_' . $auth));
			$login = $auth;
		} else {
			$login = FALSE;
		}
		return $login;
	}
}