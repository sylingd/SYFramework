<?php
/**
 * 处理请求
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\tool\wechat;

class Request {
	/**
	 * 分发请求
	 * @param $request
	 * @return array|string
	 */
	public static function switchType(&$request){
		$data = array();
		switch ($request['msgtype']) {
			//事件
			case 'event':
				$request['event'] = strtolower($request['event']);
				switch ($request['event']) {
					//关注
					case 'subscribe':
						//二维码关注
						if(isset($request['eventkey']) && isset($request['ticket'])){
							$data = self::eventQrsceneSubscribe($request);
						//普通关注
						}else{
							$data = self::eventSubscribe($request);
						}
						break;
					//扫描二维码
					case 'scan':
						$data = self::eventScan($request);
						break;
					//地理位置
					case 'location':
						$data = self::eventLocation($request);
						break;
					//自定义菜单 - 点击菜单拉取消息时的事件推送
					case 'click':
						$data = self::eventClick($request);
						break;
					//自定义菜单 - 点击菜单跳转链接时的事件推送
					case 'view':
						$data = self::eventView($request);
						break;
					//自定义菜单 - 扫码推事件的事件推送
					case 'scancode_push':
						$data = self::eventScancodePush($request);
						break;
					//自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
					case 'scancode_waitmsg':
						$data = self::eventScancodeWaitMsg($request);
						break;
					//自定义菜单 - 弹出系统拍照发图的事件推送
					case 'pic_sysphoto':
						$data = self::eventPicSysPhoto($request);
						break;
					//自定义菜单 - 弹出拍照或者相册发图的事件推送
					case 'pic_photo_or_album':
						$data = self::eventPicPhotoOrAlbum($request);
						break;
					//自定义菜单 - 弹出微信相册发图器的事件推送
					case 'pic_weixin':
						$data = self::eventPicWeixin($request);
						break;
					//自定义菜单 - 弹出地理位置选择器的事件推送
					case 'location_select':
						$data = self::eventLocationSelect($request);
						break;
					//取消关注
					case 'unsubscribe':
						$data = self::eventUnsubscribe($request);
						break;
					//群发接口完成后推送的结果
					case 'masssendjobfinish':
						$data = self::eventMassSendJobFinish($request);
						break;
					//模板消息完成后推送的结果
					case 'templatesendjobfinish':
						$data = self::eventTemplateSendJobFinish($request);
						break;
					default:
						return Msg::returnErrMsg(Config::ERROR_UNKNOW_TYPE, '收到了未知类型的消息', $request);
						break;
				}
				break;
			//文本
			case 'text':
				$data = self::text($request);
				break;
			//图像
			case 'image':
				$data = self::image($request);
				break;
			//语音
			case 'voice':
				$data = self::voice($request);
				break;
			//视频
			case 'video':
				$data = self::video($request);
				break;
			//小视频
			case 'shortvideo':
				$data = self::shortvideo($request);
				break;
			//位置
			case 'location':
				$data = self::location($request);
				break;
			//链接
			case 'link':
				$data = self::link($request);
				break;
			default:
				$data = self::unknow($request);
				break;
		}
		return $data;
	}


	/**
	 * 文本
	 * @param $request
	 * @return array
	 */
	public static function text(&$request){
		return ['type' => 'text', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 图像
	 * @param $request
	 * @return array
	 */
	public static function image(&$request){
		return ['type' => 'image', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 语音
	 * @param $request
	 * @return array
	 */
	public static function voice(&$request){
		if(!isset($request['recognition'])){
			return ['type' => 'voice', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
		}else{
			return ['type' => 'text', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername'], 'recognition' => $request['recognition']];
		}
	}

	/**
	 * 视频
	 * @param $request
	 * @return array
	 */
	public static function video(&$request){
		return ['type' => 'video', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 视频
	 * @param $request
	 * @return array
	 */
	public static function shortvideo(&$request){
		return ['type' => 'shortvideo', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 地理
	 * @param $request
	 * @return array
	 */
	public static function location(&$request){
		return ['type' => 'location', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 链接
	 * @param $request
	 * @return array
	 */
	public static function link(&$request){
		return ['type' => 'link', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 未知
	 * @param $request
	 * @return array
	 */
	public static function unknow(&$request){
		return ['type' => 'unknow', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 关注
	 * @param $request
	 * @return array
	 */
	public static function eventSubscribe(&$request){
		return ['type' => 'eventSubscribe', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 取消关注
	 * @param $request
	 * @return array
	 */
	public static function eventUnsubscribe(&$request){
		return ['type' => 'eventUnsubscribe', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 扫描二维码关注（未关注时）
	 * @param $request
	 * @return array
	 */
	public static function eventQrsceneSubscribe(&$request){
		return ['type' => 'eventQrsceneSubscribe', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 扫描二维码（已关注时）
	 * @param $request
	 * @return array
	 */
	public static function eventScan(&$request){
		return ['type' => 'eventScan', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 上报地理位置
	 * @param $request
	 * @return array
	 */
	public static function eventLocation(&$request){
		return ['type' => 'eventLocation', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername']];
	}

	/**
	 * 自定义菜单 - 点击菜单拉取消息时的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventClick(&$request){
		//获取该分类的信息
		return ['type' => 'eventClick', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername'], 'eventkey' => $request['eventkey']];
	}

	/**
	 * 自定义菜单 - 点击菜单跳转链接时的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventView(&$request){
		//获取该分类的信息
		return ['type' => 'eventView', 'fromusername' => $request['fromusername'], 'tousername' => $request['tousername'], 'eventkey' => $request['eventkey']];
	}

	/**
	 * 自定义菜单 - 扫码推事件的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventScancodePush(&$request){
		//获取该分类的信息
		return [
			'type' => 'eventScancodePush',
			'fromusername' => $request['fromusername'],
			'tousername' => $request['tousername'],
			'eventkey' => $request['eventkey'],
			'scancodeinfo' => $request['scancodeinfo'],
			'scantype' => $request['scantype'],
			'scanresult' => $request['scanresult']
		];
	}

	/**
	 * 自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventScancodeWaitMsg(&$request){
		//获取该分类的信息
		return [
			'type' => 'eventScancodeWaitMsg',
			'fromusername' => $request['fromusername'],
			'tousername' => $request['tousername'],
			'eventkey' => $request['eventkey'],
			'scancodeinfo' => $request['scancodeinfo'],
			'scantype' => $request['scantype'],
			'scanresult' => $request['scanresult']
		];
	}

	/**
	 * 自定义菜单 - 弹出系统拍照发图的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventPicSysPhoto(&$request){
		//获取该分类的信息
		return [
			'type' => 'eventPicSysPhoto',
			'fromusername' => $request['fromusername'],
			'tousername' => $request['tousername'],
			'eventkey' => $request['eventkey'],
			'sendpicsinfo' => $request['sendpicsinfo'],
			'count' => $request['count'],
			'piclist' => $request['piclist'],
			'picmd5sum' => $request['picmd5sum']
		];
	}

	/**
	 * 自定义菜单 - 弹出拍照或者相册发图的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventPicPhotoOrAlbum(&$request){
		//获取该分类的信息
		return [
			'type' => 'eventPicPhotoOrAlbum',
			'fromusername' => $request['fromusername'],
			'tousername' => $request['tousername'],
			'eventkey' => $request['eventkey'],
			'sendpicsinfo' => $request['sendpicsinfo'],
			'count' => $request['count'],
			'piclist' => $request['piclist'],
			'picmd5sum' => $request['picmd5sum']
		];
	}

	/**
	 * 自定义菜单 - 弹出微信相册发图器的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventPicWeixin(&$request){
		//获取该分类的信息
		return [
			'type' => 'eventClick',
			'fromusername' => $request['fromusername'],
			'tousername' => $request['tousername'],
			'eventkey' => $request['eventkey'],
			'sendpicsinfo' => $request['sendpicsinfo'],
			'count' => $request['count'],
			'piclist' => $request['piclist'],
			'picmd5sum' => $request['picmd5sum']
		];
	}

	/**
	 * 自定义菜单 - 弹出地理位置选择器的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventLocationSelect(&$request){
		//获取该分类的信息
		return [
			'type' => 'eventLocationSelect',
			'fromusername' => $request['fromusername'],
			'tousername' => $request['tousername'],
			'eventkey' => $request['eventkey'],
			'sendlocationinfo' => $request['sendlocationinfo'], //发送的位置信息
			'location' => [
				'x' => $request['location_x'], //X坐标信息
				'y' => $request['location_y'], //Y坐标信息
			],
			'scale' => $request['scale'], //精度(可理解为精度或者比例尺、越精细的话 scale越高)
			'label' => $request['label'], //地理位置的字符串信息
			'poiname' => $request['poiname'] //朋友圈POI的名字，可能为空
		];
	}

	/**
	 * 群发接口完成后推送的结果
	 *
	 * 本消息有公众号群发助手的微信号“mphelper”推送的消息
	 * @param $request
	 */
	public static function eventMassSendJobFinish(&$request){
		//发送状态，为“send success”或“send fail”或“err(num)”。但send success时，也有可能因用户拒收公众号的消息、系统错误等原因造成少量用户接收失败。err(num)是审核失败的具体原因，可能的情况如下：err(10001), //涉嫌广告 err(20001), //涉嫌政治 err(20004), //涉嫌社会 err(20002), //涉嫌色情 err(20006), //涉嫌违法犯罪 err(20008), //涉嫌欺诈 err(20013), //涉嫌版权 err(22000), //涉嫌互推(互相宣传) err(21000), //涉嫌其他
		$status = $request['status'];
		//计划发送的总粉丝数。group_id下粉丝数；或者openid_list中的粉丝数
		$totalCount = $request['totalcount'];
		//过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数，原则上，FilterCount = SentCount + ErrorCount
		$filterCount = $request['filtercount'];
		//发送成功的粉丝数
		$sentCount = $request['sentcount'];
		//发送失败的粉丝数
		$errorCount = $request['errorcount'];
		return [
			'type' => 'eventMassSendJobFinish',
			'status' => $status,
			'totalCount' => $totalCount,
			'filterCount' => $filterCount,
			'success' => $sentCount,
			'fail' => $errorCount
		];
	}

	/**
	 * 群发接口完成后推送的结果
	 *
	 * 本消息有公众号群发助手的微信号“mphelper”推送的消息
	 * @param $request
	 * @return array
	 */
	public static function eventTemplateSendJobFinish(&$request){
		//发送状态，成功success，用户拒收failed:user block，其他原因发送失败failed: system failed
		$status = $request['status'];
		return [
			'type' => 'eventTemplateSendJobFinish',
			'status' => $status
		];
	}
}
