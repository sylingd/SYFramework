<?php
/**
 * 发送主动响应
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\tool\wechat;
class ResponseInitiative {

	protected static $queryUrl;

	protected static function init() {
		self::$queryUrl = Common::URL . 'cgi-bin/message/custom/send?access_token=' . AccessToken::getAccessToken();
	}
	/**
	 * 文本
	 * @param $tousername
	 * @param $content 回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）
	 * @return string
	 */
	public static function text($tousername, $content) {
		self::init();
		//开始
		$template = [
			'touser' => $tousername,
			'msgtype' => 'text',
			'text' => [
				'content' => $content,
			],
		];
		$template = json_encode($template);
		return Common::FetchURL(['url' => self::$queryUrl, 'postfields' => $template]);
	}

	/**
	 * 图片
	 * @param $tousername
	 * @param $mediaId 通过上传多媒体文件，得到的id。
	 * @return string
	 */
	public static function image($tousername, $mediaId) {
		self::init();
		//开始
		$template = [
			'touser' => $tousername,
			'msgtype' => 'image',
			'image' => [
				'media_id' => $mediaId,
			],
		];
		$template = json_encode($template);
		return Common::FetchURL(['url' => self::$queryUrl, 'postfields' => $template]);
	}

	/**
	 * 语音
	 * @param $tousername
	 * @param $mediaId 通过上传多媒体文件，得到的id
	 * @return string
	 */
	public static function voice($tousername, $mediaId) {
		self::init();
		//开始
		$template = [
			'touser' => $tousername,
			'msgtype' => 'voice',
			'voice' => [
				'media_id' => $mediaId,
			],
		];
		$template = json_encode($template);
		return Common::FetchURL(['url' => self::$queryUrl, 'postfields' => $template]);
	}

	/**
	 * 视频
	 * @param $tousername
	 * @param $mediaId 通过上传多媒体文件，得到的id
	 * @param $title 标题
	 * @param $description 描述
	 * @return string
	 */
	public static function video($tousername, $mediaId, $title, $description) {
		self::init();
		//开始
		$template = [
			'touser' => $tousername,
			'msgtype' => 'video',
			'video' => [
				'media_id' => $mediaId,
				'title' => $title,
				'description' => $description,
			],
		];
		$template = json_encode($template);
		return Common::FetchURL(['url' => self::$queryUrl, 'postfields' => $template]);
	}

	/**
	 * 音乐
	 * @param $tousername
	 * @param $title 标题
	 * @param $description 描述
	 * @param $musicUrl 音乐链接
	 * @param $hqMusicUrl 高质量音乐链接，WIFI环境优先使用该链接播放音乐
	 * @param $thumbMediaId 缩略图的媒体id，通过上传多媒体文件，得到的id
	 * @return string
	 */
	public static function music($tousername, $title, $description, $musicUrl, $hqMusicUrl, $thumbMediaId) {
		self::init();
		//开始
		$template = [
			'touser' => $tousername,
			'msgtype' => 'music',
			'music' => [
				'title' => $title,
				'description' => $description,
				'musicurl' => $musicUrl,
				'hqmusicurl' => $hqMusicUrl,
				'thumb_media_id' => $thumbMediaId,
			],
		];
		$template = json_encode($template);
		return Common::FetchURL(['url' => self::$queryUrl, 'postfields' => $template]);
	}

	/**
	 * 图文消息 - 单个项目的准备工作，用于内嵌到self::news()中。现调用本方法，再调用self::news()
	 *			  多条图文消息信息，默认第一个item为大图,注意，如果调用本方法得到的数组总项数超过10，则将会无响应
	 * @param $title 标题
	 * @param $description 描述
	 * @param $picUrl 图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200
	 * @param $url 点击图文消息跳转链接
	 * @return string
	 */
	public static function newsItem($title, $description, $picUrl, $url) {
		return $template = [
			'title' => $title,
			'description' => $description,
			'url' => $picUrl,
			'picurl' => $url,
		];
	}

	/**
	 * 图文 - 先调用self::newsItem()再调用本方法
	 * @param $tousername
	 * @param $item 数组，每个项由self::newsItem()返回
	 * @return string
	 */
	public static function news($tousername, $item) {
		self::init();
		//开始
		$template = [
			'touser' => $tousername,
			'msgtype' => 'news',
			'news' => [
				'articles' => $item
			],
		];
		$template = json_encode($template);
		return Common::FetchURL(['url' => self::$queryUrl, 'postfields' => $template]);
	}


}