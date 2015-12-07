# 微信扩展

* 源项目：[LaneWeChat](https://git.oschina.net/Lane/LaneWeChat)

* 开源协议：同框架

## 使用实例

### 主动给用户发送信息

	//需要发给谁？
	$tousername = "用户和公众号兑换的OpenId";
	$mediaId = "通过上传多媒体文件，得到的id。";
	//发送文本内容
	\sy\tool\wechat\ResponseInitiative::text($tousername, '文本消息内容');
	//发送图片
	\sy\tool\wechat\ResponseInitiative::image($tousername, $mediaId);
	//发送语音
	\sy\tool\wechat\ResponseInitiative::voice($tousername, $mediaId);
	//发送视频
	\sy\tool\wechat\ResponseInitiative::video($tousername, $mediaId, '视频描述', '视频标题');
	//发送地理位置
	\sy\tool\wechat\ResponseInitiative::music($tousername, '音乐标题', '音乐描述', '音乐链接', '高质量音乐链接，WIFI环境优先使用该链接播放音乐', '缩略图的媒体id，通过上传多媒体文件，得到的id');
	//发送图文消息
	//创建图文消息内容
	$tuwenList[] = array('title'=>'标题1', 'description'=>'描述1', 'pic_url'=>'图片URL1', 'url'=>'点击跳转URL1');
	$tuwenList[] = array('title'=>'标题2', 'description'=>'描述2', 'pic_url'=>'图片URL2', 'url'=>'点击跳转URL2');
	//构建图文消息格式
	$itemList = [];
	foreach ($tuwenList as $tuwen) {
		$itemList[] = \sy\tool\wechat\ResponseInitiative::newsItem($tuwen['title'], $tuwen['description'], $tuwen['pic_url'], $tuwen['url']);
	}
	\sy\tool\wechat\ResponseInitiative::news($tousername, $itemList);

### 被动发送消息（回复消息）

	//需要发给谁？
	$fromusername = "谁发给你的？（用户的openId）";
	$tousername = "你的公众号Id";
	$mediaId = "通过上传多媒体文件，得到的id。";
	//发送文本
	\sy\tool\wechat\ResponsePassive::text($fromusername, $tousername, '文本消息内容');
	//发送图片
	\sy\tool\wechat\ResponsePassive::image($fromusername, $tousername, $mediaId);
	//发送语音
	\sy\tool\wechat\ResponsePassive::voice($fromusername, $tousername, $mediaId);
	//发送视频
	\sy\tool\wechat\ResponsePassive::video($fromusername, $tousername, $mediaId, '视频标题', '视频描述');
	//发送音乐
	\sy\tool\wechat\ResponsePassive::music($fromusername, $tousername, '音乐标题', '音乐描述', '音乐链接', '高质量音乐链接，WIFI环境优先使用该链接播放音乐', '缩略图的媒体id，通过上传多媒体文件，得到的id');
	//发送图文
	//创建图文消息内容
	$tuwenList[] = array('title'=>'标题1', 'description'=>'描述1', 'pic_url'=>'图片URL1', 'url'=>'点击跳转URL1');
	$tuwenList[] = array('title'=>'标题2', 'description'=>'描述2', 'pic_url'=>'图片URL2', 'url'=>'点击跳转URL2');
	//构建图文消息格式
	$itemList = [];
	foreach($tuwenList as $tuwen) {
		$itemList[] = \sy\tool\wechat\ResponsePassive::newsItem($tuwen['title'], $tuwen['description'], $tuwen['pic_url'], $tuwen['url']);
	}
	\sy\tool\wechat\ResponsePassive::news($fromusername, $tousername, $itemList);
	//将消息转发到多客服
	\sy\tool\wechat\ResponsePassive::forwardToCustomService($fromusername, $tousername);

### 用户管理

	$openId = '用户和微信公众号的唯一ID';
	//----分组管理----
	//创建分组
	\sy\tool\wechat\UserManage::createGroup('分组名');
	//获取分组列表
	\sy\tool\wechat\UserManage::getGroupList();
	//查询用户所在分组
	\sy\tool\wechat\UserManage::getGroupByOpenId($openId);
	//修改分组名
	\sy\tool\wechat\UserManage::editGroupName('分组Id', '新的组名');
	//移动用户分组
	\sy\tool\wechat\UserManage::editUserGroup($openId, '新的分组ID');
	//---用户管理----
	//获取用户基本信息
	\sy\tool\wechat\UserManage::getUserInfo($openId);
	//获取关注者列表
	\sy\tool\wechat\UserManage::getFansList($next_openId='');
	//修改粉丝的备注
	\sy\tool\wechat\UserManage::setRemark($openId, '新昵称');
	//获取网络状态
	\sy\tool\wechat\UserManage::getNetworkState();

### 网页授权

	/**
	 * Description: 获取CODE
	 * @param $scope snsapi_base不弹出授权页面，只能获得OpenId;snsapi_userinfo弹出授权页面，可以获得所有信息
	 * 将会跳转到redirect_uri/?code=CODE&state=STATE 通过GET方式获取code和state
	 */
	$redirect_uri = '获取CODE时，发送请求和参数给微信服务器，微信服务器会处理后将跳转到本参数指定的URL页面';
	\sy\tool\wechat\OAuth::getCode($redirect_uri, $state=1, $scope='snsapi_base');
	/**
	 * Description: 通过code换取网页授权access_token
	 * 首先请注意，这里通过code换取的网页授权access_token,与基础支持中的access_token不同。
	 * 公众号可通过下述接口来获取网页授权access_token。
	 * 如果网页授权的作用域为snsapi_base，则本步骤中获取到网页授权access_token的同时，也获取到了openid，snsapi_base式的网页授权流程即到此为止。
	 * @param $code getCode()获取的code参数
	 */
	$code = $_GET['code'];
	\sy\tool\wechat\OAuth::getAccessTokenAndOpenId($code);

	//上传多媒体
	\sy\tool\wechat\Media::upload($filename, $type);
	//下载多媒体
	\sy\tool\wechat\Media::download($mediaId);


### 自定义菜单

	//设置菜单
	$menuList = array(
		array('id'=>'1', 'pid'=>'',  'name'=>'常规', 'type'=>'', 'code'=>'key_1'),
		array('id'=>'2', 'pid'=>'1',  'name'=>'点击', 'type'=>'click', 'code'=>'key_2'),
		array('id'=>'3', 'pid'=>'1',  'name'=>'浏览', 'type'=>'view', 'code'=>'http://www.lanecn.com'),
		array('id'=>'4', 'pid'=>'',  'name'=>'扫码', 'type'=>'', 'code'=>'key_4'),
		array('id'=>'5', 'pid'=>'4', 'name'=>'扫码带提示', 'type'=>'scancode_waitmsg', 'code'=>'key_5'),
		array('id'=>'6', 'pid'=>'4', 'name'=>'扫码推事件', 'type'=>'scancode_push', 'code'=>'key_6'),
		array('id'=>'7', 'pid'=>'',  'name'=>'发图', 'type'=>'', 'code'=>'key_7'),
		array('id'=>'8', 'pid'=>'7', 'name'=>'系统拍照发图', 'type'=>'pic_sysphoto', 'code'=>'key_8'),
		array('id'=>'9', 'pid'=>'7', 'name'=>'拍照或者相册发图', 'type'=>'pic_photo_or_album', 'code'=>'key_9'),
		array('id'=>'10', 'pid'=>'7', 'name'=>'微信相册发图', 'type'=>'pic_weixin', 'code'=>'key_10'),
		array('id'=>'11', 'pid'=>'1', 'name'=>'发送位置', 'type'=>'location_select', 'code'=>'key_11'),
	);
	\sy\tool\wechat\Menu::setMenu($menuList);
	//获取菜单
	\sy\tool\wechat\Menu::getMenu();
	//删除菜单
	\sy\tool\wechat\Menu::delMenu();


### 应用：给粉丝群发消息

	//群发消息
	//获取粉丝列表
	$fansList = \sy\tool\wechat\UserManage::getFansList();
	//上传图片
	$menuId = \sy\tool\wechat\Media::upload('/var/www/baidu_jgylogo3.jpg', 'image');
	if (empty($menuId['media_id'])) {
		die('error');
	}
	//上传图文消息
	$list = [];
	$list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'作者', 'title'=>'标题', 'content_source_url'=>'www.lanecn.com', 'digest'=>'摘要', 'show_cover_pic'=>'1');
	$list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'作者', 'title'=>'标题', 'content_source_url'=>'www.lanecn.com', 'digest'=>'摘要', 'show_cover_pic'=>'0');
	$list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'作者', 'title'=>'标题', 'content_source_url'=>'www.lanecn.com', 'digest'=>'摘要', 'show_cover_pic'=>'0');
	$mediaId = \sy\tool\wechat\AdvancedBroadcast::uploadNews($list);
	//给粉丝列表的用户群发图文消息
	$result = \sy\tool\wechat\AdvancedBroadcast::sentNewsByOpenId($fansList['data']['openid'], $mediaId);

### 加密解密示例

	// 第三方发送消息给公众平台
	$encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
	$token = "pamtest";
	$timeStamp = "1409304348";
	$nonce = "xxxxxx";
	$appId = "wxb11529c136998cb6";
	$text = "<xml><ToUserName><![CDATA[oia2Tj我是中文jewbmiOUlr6X-1crbLOvLw]]></ToUserName><FromUserName><![CDATA[gh_7f083739789a]]></FromUserName><CreateTime>1407743423</CreateTime><MsgType><![CDATA[video]]></MsgType><Video><MediaId><![CDATA[eYJ1MbwPRJtOvIEabaxHs7TX2D-HV71s79GUxqdUkjm6Gs2Ed1KF3ulAOA9H1xG0]]></MediaId><Title><![CDATA[testCallBackReplyVideo]]></Title><Description><![CDATA[testCallBackReplyVideo]]></Description></Video></xml>";
	$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
	$encryptMsg = '';
	$errCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
	if ($errCode == 0) {
		print("加密后: " . $encryptMsg . "\n");
	} else {
		print($errCode . "\n");
	}

	$xml_tree = new DOMDocument();
	$xml_tree->loadXML($encryptMsg);
	$array_e = $xml_tree->getElementsByTagName('Encrypt');
	$array_s = $xml_tree->getElementsByTagName('MsgSignature');
	$encrypt = $array_e->item(0)->nodeValue;
	$msg_sign = $array_s->item(0)->nodeValue;

	$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
	$from_xml = sprintf($format, $encrypt);

	// 第三方收到公众号平台发送的消息
	$msg = '';
	$errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
	if ($errCode == 0) {
		print("解密后: " . $msg . "\n");
	} else {
		print($errCode . "\n");
	}


## 其他说明

### 关于AES加密

* WXBizMsgCrypt.php文件提供了WXBizMsgCrypt类的实现，是用户接入企业微信的接口类。使用方法可以参考上面。errorCode.php, pkcs7Encoder.php, SHA1.php, xmlparse.php文件是实现这个类的辅助类，开发者无须关心其具体实现。

* WXBizMsgCrypt类封装了 DecryptMsg, EncryptMsg两个接口，分别用于开发者解密以及开发者回复消息的加密。使用方法可以参考上面。

* 加解密协议请参考微信公众平台官方文档。