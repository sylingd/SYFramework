<?php
/**
 * 错误提示类
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\tool\wechat;
use \Sy;

class Msg {
	/**
	 * 返回错误信息 ...
	 * @param int $code 错误码
	 * @param string $errorMsg 错误信息
	 * @return Ambigous <multitype:unknown , multitype:, boolean>
	 */
	public static function returnErrMsg($code, $errorMsg = NULL) {
		if (Sy::$debug) {
			echo '微信扩展发生错误', "\n";
			echo '错误码：', $code, "\n";
			if (!empty($errorMsg)) {
				echo $errorMsg;
			}
			exit;
		} else {
			echo '系统内部错误';
			exit;
		}
	}
}
?>
