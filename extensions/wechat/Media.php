<?php
/**
 * 多媒体的上传与下载
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\tool\wechat;

class Media {
	/**
	 * 多媒体上传。上传图片、语音、视频等文件到微信服务器，上传后服务器会返回对应的media_id，公众号此后可根据该media_id来获取多媒体。
	 * 上传的多媒体文件有格式和大小限制，如下：
	 * 图片（image）: 1M，支持JPG格式
	 * 语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
	 * 视频（video）：10MB，支持MP4格式
	 * 缩略图（thumb）：64KB，支持JPG格式
	 * 媒体文件在后台保存时间为3天，即3天后media_id失效。
	 *
	 * @param $filename，文件绝对路径
	 * @param $type, 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
	 * @return array{"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
	 */
	public static function upload($filename, $type) {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::FileURL . 'cgi-bin/media/upload?access_token=' . $accessToken.'&type='.$type;
		$data = ['media' => '@' . $filename];
		return Common::FetchURL(['url' => $queryUrl, 'postfields' => $data]);
	}

	/**
	 * 下载多媒体文件
	 * @param $mediaId 多媒体ID
	 * @return string
	 */
	public static function download($mediaId) {
		//获取ACCESS_TOKEN
		$accessToken = AccessToken::getAccessToken();
		$queryUrl = Common::FileURL . 'cgi-bin/media/get?' . http_build_query(['access_token' => $accessToken, 'media_id' => $mediaId]);
		return Common::FetchURL(['url' => $queryUrl], FALSE);
	}
}