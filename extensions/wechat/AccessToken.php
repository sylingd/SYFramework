<?php
/**
 * 微信Access_Token的获取与过期检查
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\tool\wechat;
use \Sy;
use \sy\lib\YCookie;

class AccessToken {
	protected static $accessToken = NULL;
	protected static $handle = [];
	/**
	 * 获取微信Access_Token（详细的）
	 * @return array
	 */
	public static function getFullAccessToken() {
		if (self::$accessToken === NULL) {
			self::checkAccessToken();
		}
		return self::$accessToken;
	}
	/**
	 * 获取微信Access_Token（简略的）
	 * @return string
	 */
	public static function getAccessToken() {
		$at = self::getFullAccessToken();
		return $at['access_token'];
	}
	/**
	 * 设置储存和读取句柄
	 * @param string $type read/write
	 * @param mixed $handle 可被call_user_func调用的函数/方法
	 */
	public static function setHandle($type, $handle) {
		if (!is_callable($handle)) {
			return FALSE;
		}
		if ($type === 'read' || $type === 'write') {
			self::$handle[$type] = $handle;
		}
	}
	/**
	 * 从微信服务器获取微信ACCESS_TOKEN
	 * @return array|bool
	 */
	protected static function reloadAccessToken() {
		$url = Common::URL . 'cgi-bin/token?' . http_build_query(['grant_type' => 'client_credential', 'appid' => Common::$APPID, 'secret' => Common::$APPSECRET]);
		$accessToken = Common::FetchURL(['url' => $url]);
		if (!isset($accessToken['access_token'])) {
			return Msg::returnErrMsg(Common::ERROR_GET_ACCESS_TOKEN, '获取ACCESS_TOKEN失败');
		}
		$accessToken['time'] = $_SERVER['REQUEST_TIME'];
		if (isset(self::$handle['write'])) {
			$accessToken = call_user_func(self::$handle['write'], $accessToken);
		}
		return $accessToken;
	}
	/**
	 * 检测微信ACCESS_TOKEN是否过期
	 * -10是预留的网络延迟时间
	 */
	protected static function checkAccessToken() {
		//获取access_token
		if (isset(self::$handle['read'])) {
			$accessToken = call_user_func(self::$handle['read']);
		}
		if (isset($accessToken) && is_array($accessToken) && (time() - $accessToken['time'] < $accessToken['expires_in']-10)) {
			self::$accessToken = $accessToken;
		} else {
			self::$accessToken = self::reloadAccessToken();
		}
	}
}
?>