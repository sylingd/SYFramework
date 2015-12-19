# 微信扩展

* 源项目：[LaneWeChat](https://git.oschina.net/Lane/LaneWeChat)

* 开源协议：同框架

## 说明

### 关于AES加密

* WXBizMsgCrypt.php文件提供了WXBizMsgCrypt类的实现，是用户接入企业微信的接口类。使用方法可以参考下面。errorCode.php, pkcs7Encoder.php, SHA1.php, xmlparse.php文件是实现这个类的辅助类，开发者无须关心其具体实现。

* WXBizMsgCrypt类封装了 DecryptMsg, EncryptMsg两个接口，分别用于开发者解密以及开发者回复消息的加密。使用方法可以参考下面。

* 加解密协议请参考微信公众平台官方文档。

## 函数详解

### 被动给用户发送消息

#### 简介

用户输入文本、图片、语音、音乐、视频等消息，以及关注、取消关注，上报地理位置等事件后，服务器被动给出应答。

#### 名称

\sy\tool\wechat\ResponsePassive

#### 参数

	$fromusername = "谁发给你的？（用户的openId）"  //变量$request['fromusername']中
	$tousername = "你的公众号Id"; //变量$require['tousername']中
	$mediaId = "通过上传多媒体文件，得到的id。";

#### 范例

* 发送文本 `ResponsePassive::text($fromusername, $tousername, '文本消息内容');`

* 发送图片 `ResponsePassive::image($fromusername, $tousername, $mediaId);`

* 发送语音 `ResponsePassive::voice($fromusername, $tousername, $mediaId);`

* 发送视频 `ResponsePassive::video($fromusername, $tousername, $mediaId, '视频标题', '视频描述');`

* 发送音乐 `ResponsePassive::music($fromusername, $tousername, '音乐标题', '音乐描述', '音乐链接', '高质量音乐链接，WIFI环境优先使用该链接播放音乐', '缩略图的媒体id，通过上传多媒体文件，得到的id');`

* 发送图文


	//创建图文消息内容
	$tuwenList = [];
	$tuwenList[] = ['title'=>'标题1', 'description'=>'描述1', 'pic_url'=>'图片URL1', 'url'=>'点击跳转URL1'];
	$tuwenList[] = ['title'=>'标题2', 'description'=>'描述2', 'pic_url'=>'图片URL2', 'url'=>'点击跳转URL2'];
	//构建图文消息格式
	$itemList = [];
	foreach ($tuwenList as $tuwen) {
		$itemList[] = ResponsePassive::newsItem($tuwen['title'], $tuwen['description'], $tuwen['pic_url'], $tuwen['url']);
	}
	//发送图文消息
	ResponsePassive::news($fromusername, $tousername, $itemList);

### AccessToken授权

#### 简介

除了被动相应用户之外，在主动给用户发送消息，用户组管理等高级操作，是需要AccessToken授权的，我们调用一个URL给微信服务器，微信服务器会返回给我们一个散列字符串，在高级操作的时候需要将此串以参数的形式发送。散列字符串10分钟内有效，过期需要重新获取，获取新的后之前的全部失效。

#### 名称

\sy\tool\wechat\AccessToken;

#### 范例

* 获取AccessToken`AccessToken::getAccessToken();`

* 获取AccessToken和相关信息（用作自定义储存）`AccessToken::getFullAccessToken();`

#### 补充说明

根据介绍我们已经知道了，获取AccessToken只有10分钟的有效期，过期需要重新获取。因此，我们需要存储这个AccessToken。

因为用户环境不统一，SYFramework提供了函数用于设置读/写方法。在使用getAccessToken/getFullAccessToken之前进行设置即可

如不设置，每次请求中，首次调用getAccessToken/getFullAccessToken方法都会从微信服务器获取AccessToken

以文件为例

读

	\sy\tool\wechat\AccessToken::setHandle('read', function(){
		return unserialize(file_get_contents('accesstoken.txt'));
	});

写

	\sy\tool\wechat\AccessToken::setHandle('read', function($accessToken){
		file_put_contents('accesstoken.txt', serialize($accessToken));
	});

### 主动给用户发送消息

#### 简介

服务器主动给用户发送消息

#### 名称

\sy\tool\wechat\ResponsePassive

#### 参数

	$tousername = "你的公众号Id"; //变量$require['tousername']中
	$mediaId = "通过上传多媒体文件，得到的id。";

#### 范例

* 发送文本内容 `ResponseInitiative::text($tousername, '文本消息内容');`

* 发送图片 `ResponseInitiative::image($tousername, $mediaId);`

* 发送语音 `ResponseInitiative::voice($tousername, $mediaId);`

* 发送视频 `ResponseInitiative::video($tousername, $mediaId, '视频描述', '视频标题');`

* 发送音乐 `ResponseInitiative::music($tousername, '音乐标题', '音乐描述', '音乐链接', '高质量音乐链接，WIFI环境优先使用该链接播放音乐', '缩略图的媒体id，通过上传多媒体文件，得到的id');`

* 发送图文消息


	//创建图文消息内容
	$tuwenList = array();
	$tuwenList[] = array('title'=>'标题1', 'description'=>'描述1', 'pic_url'=>'图片URL1', 'url'=>'点击跳转URL1');
	$tuwenList[] = array('title'=>'标题2', 'description'=>'描述2', 'pic_url'=>'图片URL2', 'url'=>'点击跳转URL2');
	//构建图文消息格式
	$itemList = array();
	foreach($tuwenList as $tuwen){
		$itemList[] = ResponseInitiative::newsItem($tuwen['title'], $tuwen['description'], $tuwen['pic_url'], $tuwen['url']);
	}
	//发送图文消息
	ResponseInitiative::news($tousername, $itemList);

### 用户及用户组管理

#### 简介

获取粉丝列表，创建\修改用户组，讲用户添加\移除到用户组。

#### 名称

\sy\tool\wechat\UserManage

#### 参数

	$openId = '用户和微信公众号的唯一ID'; //变量$require['openid']中
	$mediaId = "通过上传多媒体文件，得到的id。";
	$groupId = '分组ID'; //添加新分组、获取分组列表的时候可以得到

#### 范例

* 分组管理 - 创建分组 `UserManage::createGroup('分组名');`

* 分组管理 - 获取分组列表 `UserManage::getGroupList();`

* 分组管理 - 查询用户所在分组 `UserManage::getGroupByOpenId($openId);`

* 分组管理 - 修改分组名 `UserManage::editGroupName($groupId, '新的组名');`

* 分组管理 - 移动用户分组 `UserManage::editUserGroup($openId, $groupId);`

* 用户管理 - 获取用户基本信息 `UserManage::getUserInfo($openId);`

* 用户管理 - 获取关注者列表 `UserManage::getFansList($next_openId='');`

* 用户管理 - 获取网络状态 `UserManage::getNetworkState();`

* 设置备注名

开发者可以通过该接口对指定用户设置备注名，该接口暂时开放给微信认证的服务号。

	参数：$openId：用户的openId
	参数：$remark：新的昵称
	UserManage::setRemark($openId, $remark);

#### 关于获取用户信息的新亮点 - unionId：

获取用户信息是根据openId获取，同一个微信用户对于不同的公众号，是不同的openId。那问题就来了，如果你有多个公众号，想要共享一份用户数据，可是同一个用户在不同的公众号是不同的openId，我们无法判断是否是同一个用户，现在微信引入了UnionId的概念。

如果开发者有在多个公众号，或在公众号、移动应用之间统一用户帐号的需求，需要前往微信开放平台（open.weixin.qq.com）绑定公众号后，才可利用UnionID机制来满足上述需求。

在绑定了公众号后，我们根据openId获取用户信息的时候，会新增一个字段“unionid”，只要是同一个用户，在不同的公众号用不同的openId获取用户信息的时候unionid是相同的。

此功能不需要新增/修改代码，只需要在微信开放平台绑定公众号就可以了。仍旧使用获取用户信息接口`UserManage::getUserInfo($openId);`

### 网页授权

#### 简介

在网页中获取来访用户的数据。

#### 名称

\sy\tool\wechat\OAuth

#### 参数

	$openId = '用户和微信公众号的唯一ID'; //在变量$require['openid']中
	$mediaId = "通过上传多媒体文件，得到的id。";
	$groupId = '分组ID'; //在添加新分组、获取分组列表的时候可以得到

#### 范例

* 获取CODE


	参数：$scope：snsapi_base不弹出授权页面，只能获得OpenId;snsapi_userinfo弹出授权页面，可以获得所有信息
	参数：$redirect_uri：将会跳转到redirect_uri/?code=CODE&state=STATE 通过GET方式获取code和state。获取CODE时，发送请求和参数给微信服务器，微信服务器会处理后将跳转到本参数指定的URL页面
	OAuth::getCode($redirect_uri, $scope='snsapi_base');

* 通过code换取网页授权access_token（access_token网页版）

首先请注意，这里通过code换取的网页授权access_token,与基础支持中的access_token不同。公众号可通过下述接口来获取网页授权access_token。如果网页授权的作用域为snsapi_base，则本步骤中获取到网页授权access_token的同时，也获取到了openid，snsapi_base式的网页授权流程即到此为止

	参数：$code getCode()获取的code参数。$code = $_GET['code'];
	OAuth::getAccessTokenAndOpenId($code);

* 刷新access_token（如果需要）

由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，refresh_token拥有较长的有效期（7天、30天、60天、90天），当refresh_token失效的后，需要用户重新授权。

	参数：$refreshToken：通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是refresh_token，就是这里的参数
	OAuth::refreshToken($refreshToken);

* 拉取用户信息(需scope为 snsapi_userinfo)

如果网页授权作用域为snsapi_userinfo，则此时开发者可以通过access_token和openid拉取用户信息了。

	参数：$accessToken:网页授权接口调用凭证。通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是access_token，就是这里的参数（注意：此access_token与基础支持的access_token不同）
	参数：$openId:用户的唯一标识
	参数：$lang:返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
	OAuth::getUserInfo($accessToken, $openId, $lang='zh_CN');

* 检验授权凭证（access_token）是否有效


	参数：$accessToken:网页授权接口调用凭证。通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是access_token，就是这里的参数（注意：此access_token与基础支持的access_token不同）
	OAuth::checkAccessToken($accessToken, $openId);

### 多媒体上传下载

#### 简介

在网页中获取来访用户的数据。上传的多媒体文件有格式和大小限制，如下：

* 图片（image）: 1M，支持JPG格式

* 语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式

* 视频（video）：10MB，支持MP4格式

* 缩略图（thumb）：64KB，支持JPG格式

媒体文件在后台保存时间为3天，即3天后media_id失效

#### 名称

\sy\tool\wechat\Media

#### 参数

	$filename 上传的文件的绝对路径
	$type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
	$mediaId = "通过上传多媒体文件，得到的id。";
	$groupId = '分组ID'; //在添加新分组、获取分组列表的时候可以得到

#### 范例

* 上传：（微信服务器会返回一个mediaId）`Media::upload($filename, $type);`

* 下载：（根据mediaId下载）`Media::download($mediaId);`

### 自定义菜单

#### 简介

添加自定义菜单。最多可以有三个一级菜单，每个一级菜单最多可以有五个菜单。一级菜单最多4个汉字，二级菜单最多7个汉字。创建自定义菜单后，由于微信客户端缓存，需要24小时微信客户端才会展现出来。建议测试时可以尝试取消关注公众账号后再次关注，则可以看到创建后的效果。

警告：设置菜单Menu::setMenu($menuList)参数结构和返回值重写，自1.4版本起不向下兼容。

注意：所有新增的菜单类型（除了click类型和view类型），仅支持微信iPhone5.4.1以上版本，和Android5.4以上版本的微信用户，旧版本微信用户点击后将没有回应，开发者也不能正常接收到事件推送。

菜单类型请参考微信官方网站

#### 名称

\sy\tool\wechat\Menu;

#### 范例

* 设置菜单

是所有的菜单数据全部发送一次，可不是每新增一个只发一个菜单。

	Menu::setMenu($menuList);
	$menuLis 是菜单列表，结构如下：
	$menuList ＝ array(
		array('id'=>'1', 'pid'=>'0', 'name'=>'顶级分类一', 'type'=>'', 'code'=>''),
		array('id'=>'2', 'pid'=>'1', 'name'=>'分类一子分类一', 'type'=>'click', 'code'=>'lane_wechat_menu_1_1'),
		array('id'=>'3', 'pid'=>'1', 'name'=>'分类一子分类二', 'type'=>'1', 'code'=>'http://www.lanecn.com'),
		array('id'=>'4', 'pid'=>'0', 'name'=>'顶级分类二', 'type'=>'1', 'code'=>'http://www.php.net/'),
		array('id'=>'5', 'pid'=>'0', 'name'=>'顶级分类三', 'type'=>'2', 'code'=>'lane_wechat_menu_3'),
	);

	'id'是您的系统中对分类的唯一编号；
	'pid'是该分类的上级分类，顶级分类则填写0；
	'name'是分类名称；
	'type'是菜单类型，如果该分类下有子分类请务必留空；
	'type'的值从以下类型中选择：click、view、scancode_push、scancode_waitmsg、pic_sysphoto、pic_photo_or_album、pic_weixin、location_select。
	'code'是view类型的URL或者其他类型的自定义key，如果该分类下有子分类请务必留空。

* 获取微信菜单

获取到的是已经设置过的菜单列表，为Array

	Menu::getMenu();

* 删除微信菜单

将会删除设置过的所有菜单（一键清空）。

	Menu::delMenu();

### 高级群发接口

#### 简介

根据分组、openId列表进行群发（推送）。图文消息需要先将图文消息当作一个素材上传，然后再群发，其他类型的消息直接群发即可。

注意：推送后用户到底是否成功接受，微信会向公众号推送一个消息。消息类型为事件消息，可以在lanewechat/wechatrequest.lib.php文件中的方法eventMassSendJobFinish(&$request)中收到这条消息。

#### 名称

\sy\tool\wechat\AdvancedBroadcast;

#### 范例

* 上传图文消息

创建一个图文消息，保存到微信服务器，可以得到一个id代表这个图文消息，发送的时候根据这个id发送就可以了。

	Menu::uploadNews($articles);

$articles 是图文消息列表，结构如下：

	$articles = array(
		array('thumb_media_id'=>'多媒体ID，由多媒体上传接口获得' , 'author'=>'作者', 'title'=>'标题', 'content_source_url'=>'www.lanecn.com', 'digest'=>'摘要', 'show_cover_pic'=>'是否设置为封面（0或者1）'),
		array('thumb_media_id'=>'多媒体ID，由多媒体上传接口获得' , 'author'=>'作者', 'title'=>'标题', 'content_source_url'=>'www.lanecn.com', 'digest'=>'摘要', 'show_cover_pic'=>'是否设置为封面（0或者1）'),
	);


	'thumb_media_id'多媒体ID，由多媒体上传接口获得：Media::upload($filename, $type);
	'author'作者
	'title'标题
	'content_source_url'一个URL，点击“阅读全文”跳转的地址
	'digest'摘要
	'show_cover_pic'0或1，是否设置为封面

下面的方法，图文消息的参数mediaId是由上面这个方法Menu::uploadNews($articles);获得的，其他的mediaId是多媒体上传获得的Media::upload($filename, $type);

根据分组群发的所有接口最后一个参数,$isToAll，默认未false。使用true且成功群发，会使得此次群发进入历史消息列表。

* 根据分组进行群发 - 发送图文消息：`AdvancedBroadcast::sentNewsByGroup($groupId, $mediaId, $isToAll=false);`

* 根据分组进行群发 - 发送文本消息`AdvancedBroadcast::sentTextByGroup($groupId, $content, $isToAll=false);`

* 根据分组进行群发 - 发送语音消息`AdvancedBroadcast::sentVoiceByGroup($groupId, $mediaId, $isToAll=false);`

* 根据分组进行群发 - 发送图片消息`AdvancedBroadcast::sentImageByGroup($groupId, $mediaId, $isToAll=false);`

* 根据分组进行群发 - 发送视频消息`AdvancedBroadcast::sentVideoByGroup($mediaId, $title, $description, $groupId, $isToAll=false);`

* 根据OpenID列表群发 - 发送图文消息`AdvancedBroadcast::sentNewsByOpenId($toUserList, $mediaId);`

* 根据OpenID列表群发 - 发送文本消息`AdvancedBroadcast::sentTextByOpenId($toUserList, $content);`

* 根据OpenID列表群发 - 发送语音消息`AdvancedBroadcast::sentVoiceByOpenId($toUserList, $mediaId);`

* 根据OpenID列表群发 - 发送图片消息`AdvancedBroadcast::sentImageByOpenId($toUserList, $mediaId);`

* 根据OpenID列表群发 - 发送视频消息`AdvancedBroadcast::sentVideoByOpenId($toUserList, $mediaId, $title, $description);`

* 删除群发

请注意，只有已经发送成功的消息才能删除删除消息只是将消息的图文详情页失效，已经收到的用户，还是能在其本地看到消息卡片。 另外，删除群发消息只能删除图文消息和视频消息，其他类型的消息一经发送，无法删除。

	AdvancedBroadcast::delete($msgId);
	$msgId:以上的群发接口成功时都会返回msg_id这个字段

* 预览图文消息 `AdvancedBroadcast::previewNewsByGroup($openId, $mediaId);`

* 预览文本消息 `AdvancedBroadcast::previewTextByGroup($openId, $content);`

* 预览语音消息 `AdvancedBroadcast::previewVoiceByGroup($openId, $mediaId);`

* 预览图片消息 `AdvancedBroadcast::previewImageByGroup($openId, $mediaId);`

* 预览视频消息 `AdvancedBroadcast::previewVideoByGroup($mediaId, $title, $description, $openId);`

* 查询群发消息发送状态【订阅号与服务号认证后均可用】 `AdvancedBroadcast::getStatus($openId, $mediaId);`

### 多客服接口

#### 简介

客服功能接口。

#### 名称

\sy\tool\wechat\CustomService;

* 获取客服聊天记录接口，有分页，一次获取一页，一页最多1000条。不能跨日。

在需要时，开发者可以通过获取客服聊天记录接口，获取多客服的会话记录，包括客服和用户会话的所有消息记录和会话的创建、关闭等操作记录。利用此接口可以开发如“消息记录”、“工作监控”、“客服绩效考核”等功能。

	CustomService::getRecord($startTime, $endTime, $pageIndex=1, $pageSize=1000, $openId='')

'startTime':查询开始时间，UNIX时间戳

'startTime':查询结束时间，UNIX时间戳，每次查询不能跨日查询

* 将用户发送的消息转发到客服：

	ResponsePassive::forwardToCustomService($fromusername, $tousername)。

如用户在微信给公众号发送一条文本消息“iphone 6 多少钱？”，我们就可以在lanewechat/wechatrequest.lib.php文件中的方法text(&$request)中收到这条消息（如果不了解为什么会在这里收到文本消息，请重头再看文档）。

然后在text(&$request)方法中，我们可以调用ResponsePassive::forwardToCustomService($request['fromusername'], $request['tousername'])。那么刚才用户发的“iphone 6 多少钱？”就会被转发到客服系统，在微信的客服客户端中就可以收到了。

* 添加客服帐号

必须先在公众平台官网为公众号设置微信号后才能使用该能力。

开发者可以通过本接口为公众号添加客服账号，每个公众号最多添加10个客服账号。

	CustomService::addAccount($kfAccount, $nickname, $password)
	
	$kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
	$nickname String 昵称
	$password String 密码

* 修改客服帐号

必须先在公众平台官网为公众号设置微信号后才能使用该能力。

	CustomService::editAccount($kfAccount, $nickname, $password)


	$kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
	$nickname String 昵称
	$password String 密码

* 删除客服帐号

必须先在公众平台官网为公众号设置微信号后才能使用该能力。

	CustomService::delAccount($kfAccount, $nickname, $password)

	$kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
	$nickname String 昵称
	$password String 密码

* 获取所有客服账号

必须先在公众平台官网为公众号设置微信号后才能使用该能力。

	CustomService::getAccountList($kfAccount, $nickname, $password)

	$kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
	$nickname String 昵称
	$password String 密码

* 设置客服帐号的头像

必须先在公众平台官网为公众号设置微信号后才能使用该能力。

	CustomService::setAccountImage($kfAccount, $imagePath)

	$kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
	$imagePath String 待上传的头像文件路径

### 智能接口

#### 简介

智能接口

#### 名称

\sy\tool\wechat\IntelligentInterface;

* 语义理解

比如输入文本串，如“查一下明天从北京到上海的南航机票”就可以收到关于这个问题的答案。

单类别意图比较明确，识别的覆盖率比较大，所以如果只要使用特定某个类别，建议将category只设置为该类别。

	IntelligentInterface::semanticSemproxy($query, $category, $openId, $latitude='', $longitude='', $region='', $city='')

	$query 输入文本串，如“查一下明天从北京到上海的南航机票"
	$category String 需要使用的服务类型，如“flight,hotel”，多个用“,”隔开，不能为空。详见《接口协议文档》
	$latitude Float 纬度坐标，与经度同时传入；与城市二选一传入。详见《接口协议文档》
	$longitude Float 经度坐标，与纬度同时传入；与城市二选一传入。详见《接口协议文档》
	$region String 区域名称，在城市存在的情况下可省；与经纬度二选一传入。详见《接口协议文档》
	$city 城市名称，如“北京”，与经纬度二选一传入
	$openId

### 推广支持

#### 简介

推广支持。

#### 名称

\sy\tool\wechat\Popularize;

* 生成带参数的二维码 - 第一步 创建二维码ticket

获取带参数的二维码的过程包括两步，首先创建二维码ticket，然后凭借ticket到指定URL换取二维码。

目前有2种类型的二维码，分别是临时二维码和永久二维码，

前者有过期时间，最大为1800秒，但能够生成较多数量，后者无过期时间，数量较少（目前参数只支持1--100000）。

两种二维码分别适用于帐号绑定、用户来源统计等场景。

	Popularize::createTicket($type, $expireSeconds, $sceneId);
	
	$type Int 临时二维码类型为1，永久二维码类型为2
	$expireSeconds Int 过期时间，只在类型为临时二维码时有效。最大为1800，单位秒
	$sceneId Int 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）

* 生成带参数的二维码 - 第二步 通过ticket换取二维码


	Popularize::getQrcode($ticket, $filename='')

	$ticket Popularize::createTicket()获得的
	$filename String 文件路径，如果不为空，则会创建一个图片文件，二维码文件为jpg格式，保存到指定的路径

返回值：如果传递了第二个参数filename则会在filename指定的路径生成一个二维码的图片。如果第二个参数filename为空，则直接echo本函数的返回值，并在调用页面添加header('Content-type: image/jpg');，将会展示出一个二维码的图片。

* 将一条长链接转成短链接。

主要使用场景：开发者用于生成二维码的原链接（商品、支付二维码等）太长导致扫码速度和成功率下降，将原长链接通过此接口转成短链接再生成二维码将大大提升扫码速度和成功率。

	Popularize::long2short($longUrl);

	$longUrl String 需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url

### 模板消息接口

#### 简介

模板消息仅用于公众号向用户发送重要的服务通知，只能用于符合其要求的服务场景中，如信用卡刷卡通知，商品购买成功通知等。不支持广告等营销类消息以及其它所有可能对用户造成骚扰的消息。

关于使用规则，请注意：

* 所有服务号都可以在功能->添加功能插件处看到申请模板消息功能的入口，但只有认证后的服务号才可以申请模板消息的使用权限并获得该权限；
	
* 需要选择公众账号服务所处的2个行业，每月可更改1次所选行业；

* 在所选择行业的模板库中选用已有的模板进行调用；

* 每个账号可以同时使用15个模板。

* 当前每个模板的日调用上限为10000次。

关于接口文档，请注意：

* 模板消息调用时主要需要模板ID和模板中各参数的赋值内容；

* 模板中参数内容必须以".DATA"结尾，否则视为保留字；

* 模板保留符号"{{ }}"。

#### 名称

\sy\tool\wechat\TemplateMessage;

#### 范例

* 向用户推送模板消息


	TemplateMessage::sendTemplateMessage($data, $touser, $templateId, $url, $topcolor='#FF0000');
	$data = array(
		'first'=>array('value'=>'您好，您已成功消费。', 'color'=>'#0A0A0A')
		'keynote1'=>array('value'=>'巧克力', 'color'=>'#CCCCCC')
		'keynote2'=>array('value'=>'39.8元', 'color'=>'#CCCCCC')
		'keynote3'=>array('value'=>'2014年9月16日', 'color'=>'#CCCCCC')
		'keynote3'=>array('value'=>'欢迎再次购买。', 'color'=>'#173177')
	);

	$touser 接收方的OpenId。
	$templateId 模板Id。在公众平台线上模板库中选用模板获得ID
	$url URL
	$topcolor 顶部颜色，可以为空。默认是红色

注意：推送后用户到底是否成功接受，微信会向公众号推送一个消息。消息类型为事件消息，可以在Request.php文件中的方法`eventTemplateSendJobFinish(&$request)`中收到这条消息。

* 设置行业


	TemplateMessage::setIndustry($industryId1, $industryId2)
	
	$industryId1 公众号模板消息所属行业编号 请打开连接查看行业编号 http://mp.weixin.qq.com/wiki/17/304c1885ea66dbedf7dc170d84999a9d.html#.E8.AE.BE.E7.BD.AE.E6.89.80.E5.B1.9E.E8.A1.8C.E4.B8.9A
	$industryId2 公众号模板消息所属行业编号。在公众平台线上模板库中选用模板获得ID

* 获得模板ID


	TemplateMessage::getTemplateId($templateIdShort)
	$templateIdShort 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式

### 安全性

#### 简介

安全性相关接口

#### 名称

\sy\tool\wechat\Auth;

* 获取微信服务器IP列表，用于验证每次的请求来源是否是微信服务器。


	Auth::getWeChatIPList();

### 自动回复

#### 简介

自动回复

#### 名称

\sy\tool\wechat\AutoReply;

#### 范例

* 获取自动回复规则


	AutoReply::getRole($industryId1, $industryId2);

返回结果与字段说明请查看[官方文档](http://mp.weixin.qq.com/wiki/7/7b5789bb1262fb866d01b4b40b0efecb.html)

### 实例示范：

#### 通过网页授权获得用户信息

场景：用户点击了我的自定义菜单，或者我发送的文本消息中包含一个URL，用户打开了我的微信公众号的网页版，我需要获取用户的信息。

代码：

	<?php
	use \sy\tool\wechat\OAuth;
	use \sy\tool\wechat\UserManage;

	//第一步，获取CODE
	OAuth::getCode('http://www.lanecn.com/index.php', 1, 'snsapi_base');
	//此时页面跳转到了http://www.lanecn.com/index.php，code和state在GET参数中。
	$code = $_GET['code'];
	//第二步，获取access_token网页版
	$openId = OAuth::getAccessTokenAndOpenId($code);
	//第三步，获取用户信息
	$userInfo = UserManage::getUserInfo($openId['openid']);
	?>

#### 被动响应用户 - 发送图文消息

场景描述：用户给我们的公众号发送了一条消息，我们的公众号被动响应，给用户回复一条图文消息。

场景举例：用户给我们的公众号发送了“周末聚会”，我们的公众号给用户回复了一条图文消息，有十条，每一条都是一个标题和图片，点击可以连接到一个地址。

代码：

	//图文列表逐条放入数组
	$tuwenList = array();
	$tuwenList[] = array(
		'title' => '标题：聚会地点一故宫',
		'description' => '描述：还有人去故宫聚会啊',
		'pic_url' => 'http://www.gugong.com/logo.jpg',
		'url' => 'http://www.lanecn.com/',
	);
	$tuwenList[] = array(
		'title' => '标题：聚会地点一八达岭',
		'description' => '描述：八达岭是聚会的吗？是去看人挤人的！',
		'pic_url' => 'http://www.badaling.com/logo.jpg',
		'url' => 'http://www.lanecn.com/',
	);
	$item = array();
	//构建图文列表
	foreach($tuwenList as $tuwen){
		$item[] = ResponsePassive::newsItem($tuwen['title'], $tuwen['description'], $tuwen['pic_url'], $tuwen['url']);
	}
	//发送图文列表
	ResponsePassive::news($request['fromusername'], $request['tousername'], $item);

#### 群发图文消息

场景描述：用户给我们的公众号发送了一条消息，我们的公众号被动响应，给用户回复一条图文消息。

场景举例：用户给我们的公众号发送了“周末聚会”，我们的公众号给用户回复了一条图文消息，有十条，每一条都是一个标题和图片，点击可以连接到一个地址。

代码：

	$fansList = \sy\tool\wechat\UserManage::getFansList();
	//上传图片
	$menuId = \sy\tool\wechat\Media::upload('/var/www/baidu_jgylogo3.jpg', 'image');
	if(empty($menuId['media_id'])){
		die('error');
	}
	//上传图文消息
	$list = array();
	$list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'作者', 'title'=>'标题', 'content_source_url'=>'www.lanecn.com', 'digest'=>'摘要', 'show_cover_pic'=>'1');
	$list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'作者', 'title'=>'标题', 'content_source_url'=>'www.lanecn.com', 'digest'=>'摘要', 'show_cover_pic'=>'0');
	$list[] = array('thumb_media_id'=>$menuId['media_id'] , 'author'=>'作者', 'title'=>'标题', 'content_source_url'=>'www.lanecn.com', 'digest'=>'摘要', 'show_cover_pic'=>'0');
	$mediaId = \sy\tool\wechat\AdvancedBroadcast::uploadNews($list);
	//给粉丝列表的用户群发图文消息
	$result = \sy\tool\wechat\AdvancedBroadcast::sentNewsByOpenId($fansList['data']['openid'], $mediaId);

#### 推送模板消息

场景描述：公众号推送的模板消息，比如领取红包、滴滴打车红包领取、大众点评微信支付等

场景举例：我们在实体店的一家服装店买了新衣服，而我们又是会员，他们检测到会员的手机号消费了，有新积分增加，而这个手机号又关注了这家服装店的微信公众号，根据手机号可以在服装店自己的数据库中查到微信粉丝openId，这个时候就会给这个用户发送一个模板消息。

代码：

	<?php

	$data = array(
	 'first'=>array('value'=>'您好，您已成功消费。', 'color'=>'#0A0A0A')
	 'keynote1'=>array('value'=>'巧克力', 'color'=>'#CCCCCC')
	 'keynote2'=>array('value'=>'39.8元', 'color'=>'#CCCCCC')
	 'keynote3'=>array('value'=>'2014年9月16日', 'color'=>'#CCCCCC')
	 'keynote3'=>array('value'=>'欢迎再次购买。', 'color'=>'#173177')
	);

	//$touser 接收方的OpenId。
	//$templateId 模板Id。在公众平台线上模板库中选用模板获得ID
	//$url URL 点击查看的时候跳转到URL。
	//$topcolor 顶部颜色，可以为空。默认是红色
	\sy\tool\wechat\TemplateMessage::sendTemplateMessage($data, $touser, $templateId, $url, $topcolor='#FF0000');

#### 添加自定义菜单

场景描述：微信公众号底部的导航栏按钮

场景举例：自定义菜单可以更加快捷方便的为用户服务。而不需要用户每次都要打字发送消息来获取所需要的信息。轻轻一点按钮，马上拥有！

注：微信官方仅供认证号使用自定义菜单。

代码：

	<?php
	$menuList = array(
		array('id'=>'1', 'pid'=>'0', 'name'=>'菜单1', 'type'=>'', 'code'=>''),
		array('id'=>'2', 'pid'=>'0', 'name'=>'菜单2', 'type'=>'', 'code'=>''),
		array('id'=>'3', 'pid'=>'0', 'name'=>'地理位置', 'type'=>'location_select', 'code'=>'key_7'),
		array('id'=>'4', 'pid'=>'1', 'name'=>'点击推事件', 'type'=>'click', 'code'=>'key_1'),
		array('id'=>'5', 'pid'=>'1', 'name'=>'跳转URL', 'type'=>'view', 'code'=>'http://www.lanecn.com/'),
		array('id'=>'6', 'pid'=>'2', 'name'=>'扫码推事件', 'type'=>'scancode_push', 'code'=>'key_2'),
		array('id'=>'7', 'pid'=>'2', 'name'=>'扫码等收消息', 'type'=>'scancode_waitmsg', 'code'=>'key_3'),
		array('id'=>'8', 'pid'=>'2', 'name'=>'系统拍照发图', 'type'=>'pic_sysphoto', 'code'=>'key_4'),
		array('id'=>'9', 'pid'=>'2', 'name'=>'弹拍照或相册', 'type'=>'pic_photo_or_album', 'code'=>'key_5'),
		array('id'=>'10', 'pid'=>'2', 'name'=>'弹微信相册', 'type'=>'pic_weixin', 'code'=>'key_6'),
	);

	$result = \sy\tool\wechat\Menu::setMenu($menuList);

#### 页面展示二维码

场景描述：在网页中展示微信公众号的二维码

代码：

	<?php
	header('Content-type: image/jpg');
	$ticket = \sy\tool\wechat\Popularize::createTicket(1, 1800, 1);
	$ticket = $ticket['ticket'];
	$qrcode = \sy\tool\wechat\Popularize::getQrcode($ticket);
	echo $qrcode;

## 微信支付

### 微信内网页支付

#### 流程

php生成订单 - JS打开支付 - 用户支付 - 微信后台通知商户 - 商户进行发货等操作

#### 代码示例

* 首先生成订单


	use \sy\tool\wechat\WxPay;
	use \sy\tool\wechat\WxPayData;
	$openId = '用户的openid';
	//统一下单
	$input = [
		'body' => '商品描述',
		'attach' => '商品名称',
		'out_trade_no' => '商户网站内部订单号',
		'total_fee' => '总价格，单位：分',
		'time_start' => date('YmdHis'),
		'time_expire' => date('YmdHis', time() + 600), //交易超时
		'goods_tag' => '', //商品标签
		'notify_url' => '微信后台通知URL',
		'trade_type' => 'JSAPI',
		'openid' => $openid
	];
	$order = WxPay::unifiedOrder($input);
	$jsapi = new WxPayData([
		'appid' => $order['appid'],
		'timeStamp' => time(),
		'nonceStr' => WxPay::getNonceStr(),
		'package' => 'prepay_id=' . $order['prepay_id'],
		'signType' => 'MD5'
	]);
	$jsapi->values['paySign'] = $jsapi->MakeSign();
	$jsApiParameters = json_encode($jsapi->values);

* JS打开支付界面


	<script>
	function jsApiCall() {
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res);
			}
		);
	}
	function callpay() {
		if (typeof WeixinJSBridge == "undefined"){
		    if (document.addEventListener) {
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    } else if (document.attachEvent) {
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>
	<button onclick="callpay()">立即支付</button>

* 后台接收微信通知


	use \sy\tool\wechat\Wechat;
	use \sy\tool\wechat\WxPay;
	$notify = WxPay::getNotify();
	list($check, $output, $data) = $notify->check();
	if ($check) {
		//验证成功
		//进行业务处理，如自动发货等
		//注意判断此订单是否已经处理过
		$out_trade_no = $data->out_trade_no;
		$total_fee = $data->total_fee;
	} else {
		//验证失败，不做处理
		echo $output;
	}
