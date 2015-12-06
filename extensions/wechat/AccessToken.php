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
use \sy\lib\YFetchURL;

class AccessToken {

	/**
	 * 获取微信Access_Token
	 */
	public static function getAccessToken(){
		//检测本地是否已经拥有access_token，并且检测access_token是否过期
		$accessToken = self::_checkAccessToken();
		if($accessToken === false){
			$accessToken = self::_getAccessToken();
		}
		return $accessToken['access_token'];
	}

	/**
	 * 从微信服务器获取微信ACCESS_TOKEN
	 * @return Ambigous|bool
	 */
	private static function _getAccessToken(){
		$url = Config::URL . 'cgi-bin/token?' . http_build_query(['grant_type' => 'client_credential', 'appid' => Config::APPID, 'secret' => Config::APPSECRET]);
		$accessToken = json_decode(YFetchURL::i(['url' => $url])->exec(), 1);
		if(!isset($accessToken['access_token'])){
			return Msg::returnErrMsg(Config::ERROR_GET_ACCESS_TOKEN, '获取ACCESS_TOKEN失败');
		}
		$accessToken['time'] = time();
		$accessTokenJson = json_encode($accessToken);
		//存入数据库
		/**
		 * 这里通常我会把access_token存起来，然后用的时候读取，判断是否过期，如果过期就重新调用此方法获取，存取操作请自行完成
		 *
		 * 请将变量$accessTokenJson给存起来，这个变量是一个字符串
		 */
		// $f = fopen('access_token', 'w+');
		// fwrite($f, $accessTokenJson);
		// fclose($f);
		return $accessToken;
	}

	/**
	 * 检测微信ACCESS_TOKEN是否过期
	 *			  -10是预留的网络延迟时间
	 * @return bool
	 */
	private static function _checkAccessToken(){
		//获取access_token。是上面的获取方法获取到后存起来的。
		// $accessToken = YourDatabase::get('access_token');
		$data = file_get_contents('access_token');
		$accessToken['value'] = $data;
		if(!empty($accessToken['value'])){
			$accessToken = json_decode($accessToken['value'], true);
			if(time() - $accessToken['time'] < $accessToken['expires_in']-10){
				return $accessToken;
			}
		}
		return false;
	}
}
?>