<?php
/**
 * 用户管理类
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */


namespace sy\tool\wechat;

class UserManage {

	//-----------------------------组--------------管-------------理----------------------

	/**
	 * 创建分组
	 * @param $groupName 组名 UTF-8
	 * @return Array {"group": {"id": 107,"name": "test"}}
	 */
	public static function createGroup($groupName) {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::URL . 'cgi-bin/groups/create?access_token=' . $accessToken;
		$data = json_encode(['group' => ['name' => $groupName]]);
		return Common::FetchURL(['url' => $queryUrl, 'postfields' => $data]);

	}

	/**
	 * 获取分组列表
	 * @return Array {"groups":[{"id": 0,"name": "未分组", "count": 72596}]}
	 */
	public static function getGroupList() {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::URL . 'cgi-bin/groups/get?access_token=' . $accessToken;
		return Common::FetchURL(['url' => $queryUrl]);
	}

	/**
	 * 查询用户所在分组
	 * @param $openId 用户唯一OPENID
	 * @return Array {"groupid": 102}
	 */
	public static function getGroupByOpenId($openId) {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::URL . 'cgi-bin/groups/getid?access_token=' . $accessToken;
		$data = json_encode(['openid' => $openId]);
		return Common::FetchURL(['url' => $queryUrl, 'postfields' => $data]);

	}

	/**
	 * 修改分组名
	 * @param $groupId 要修改的分组ID
	 * @param $groupName 新分组名
	 * @return JSON {"errcode": 0, "errmsg": "ok"}
	 */
	public static function editGroupName($groupId, $groupName) {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::URL . 'cgi-bin/groups/update?access_token=' . $accessToken;
		$data = json_encode(['group' => ['id' => $groupId, 'name' => $groupName]]);
		return Common::FetchURL(['url' => $queryUrl, 'postfields' => $data]);

	}

	/**
	 * 移动用户分组
	 * @param $openid 要移动的用户OpenId
	 * @param $to_groupid 移动到新的组ID
	 * @return Array {"errcode": 0, "errmsg": "ok"}
	 */
	public static function editUserGroup($openid, $to_groupid) {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::URL . 'cgi-bin/groups/members/update?access_token=' . $accessToken;
		$data = json_encode(['openid' => $openid, 'to_groupid' => $to_groupid]);
		return Common::FetchURL(['url' => $queryUrl, 'postfields' => $data]);

	}

	//-----------------------------用-------户-------管--------理----------------------

	/**
	 * 获取用户基本信息
	 * @param $openId 用户唯一OpenId
	 * @return array {
			"subscribe": 1,	//用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息
			"openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
			"nickname": "Band",
			"sex": 1,		  //用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
			"language": "zh_CN",
			"city": "广州",
			"province": "广东",
			"country": "中国",
			"headimgurl":	"http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
			"subscribe_time": 1382694957
		}
	 */
	public static function getUserInfo($openId) {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::URL . 'cgi-bin/user/info?access_token=' . $accessToken . '&openid=' . $openId;
		return Common::FetchURL(['url' => $queryUrl]);
	}
	/**
	 * 获取关注者列表
	 * @param $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
	 * @return JSON {"total":2,"count":2,"data":{"openid":["OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
	 */
	public static function getFansList($next_openid='') {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		if (empty($next_openid)) {
			$queryUrl = Common::URL . 'cgi-bin/user/get?access_token='.$accessToken;
		} else {
			$queryUrl = Common::URL . 'cgi-bin/user/get?access_token='.$accessToken.'&next_openid='.$next_openid;
		}
		return Common::FetchURL(['url' => $queryUrl]);
	}
	/**
	 * 设置备注名 开发者可以通过该接口对指定用户设置备注名，该接口暂时开放给微信认证的服务号。
	 * @param $openId 用户的openId
	 * @param $remark 新的昵称
	 * @return array('errorcode'=>0, 'errmsg'=>'ok') 正常时是0
	 */
	public static function setRemark($openId, $remark) {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::URL . 'cgi-bin/user/info/updateremark?access_token='.$accessToken;
		$data = json_encode(array('openid'=>$openId, 'remark'=>$remark));
		return Common::FetchURL(['url' => $queryUrl, 'postfields' => $data]);
	}

	/**
	 * 获取网络状态
	 * @return String network_type:wifi wifi网络。network_type:edge 非wifi,包含3G/2G。network_type:fail 网络断开连接
	 */
	public static function getNetworkState() {
		echo "WeixinJSBridge.invoke('getNetworkType',{},
		function(e) {
			WeixinJSBridge.log(e.err_msg);
		});";
	}
}