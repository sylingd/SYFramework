<?php
/**
 * 自动回复
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */
namespace sy\tool\wechat;

class AutoReply {

	/**
	 * 获取自动回复规则
	 *
	 * @return String 返回结果与字段说明请查看http://mp.weixin.qq.com/wiki/7/7b5789bb1262fb866d01b4b40b0efecb.html
	 */
	public static function getRole($industryId1, $industryId2){
		$queryUrl = Config::URL . 'cgi-bin/get_current_autoreply_info?access_token='.AccessToken::getAccessToken();
		$queryAction = 'POST';
		$template = array();
		$template['industry_id1'] = "$industryId1";
		$template['industry_id2'] = "$industryId2";
		$template = json_encode($template);
		return Curl::callWebServer($queryUrl, $template, $queryAction);
	}
}