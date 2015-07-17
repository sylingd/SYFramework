<?php

/**
 * SSO登录组件
 * 
 * @author ShuangYa
 * @package EUser
 * @category Controller
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=euser&type=license
 */

namespace euser\controller;
use \sy\base\Controller;
use \sy\lib\YRedis;
use \sy\lib\YHtml;
use \euser\libs\option;

class CSso extends Controller {
	public function __construct() {
	}
	/**
	 * 登录页面
	 */
	public function actionLogin() {
		$this->load_model('user', 'u');
		//检查是否为iframe调用
		$ajaxUrl = Sy::createUrl('sso/ajaxLogin', 'json');
		if ($_GET['iframe']) {
			$loginStatus = $this->u->getLoginStatus();
			if ($loginStatus() !== FALSE) { //已经登录过
				Sy::setMimeType('html');
				$callback = urldecode($_GET['callback']);
				// TODO: 返回用户详情
				echo '<script>', $callback, '();</script>';
			} else {
				Sy::setMimeType('html');
				Sy::template('sso/login/iframe', ['ajaxUrl' => $ajaxUrl]);
			}
		} else {
			if ($this->getLoginStatus() !== FALSE) { //已经登录过
				Sy::setMimeType('html');
				$callback = urldecode($_GET['callback']);
				// TODO: 返回用户详情
				Sy::template('sso/login/logined', ['ajaxUrl' => $ajaxUrl]);
			} else {
				Sy::setMimeType('html');
				$callback = urldecode($_GET['callback']);
				Sy::template('sso/login/normal', ['ajaxUrl' => $ajaxUrl, 'callback' => $callback]);
			}
		}
	}
	/**
	 * Ajax请求
	 */
}
