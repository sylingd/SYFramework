<?php
/**
 * 微信公众平台来来路认证，处理中心，消息分发
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\tool\wechat;

class Wechat {
	
	protected $token;

	/**
	 * 以数组的形式保存微信服务器每次发来的请求
	 * @var array
	 */
	protected $request;
	protected $rawRequest;

	/**
	 * 初始化，判断此次请求是否为验证请求，并以数组形式保存
	 * @param string $token 验证信息
	 * @param boolean $noCheckSignature 强制不进入签名校验模式（即首次验证）
	 */
	public function __construct($token, $noCheckSignature = FALSE) {
		$this->token = $token;
		$this->rawRequest = file_get_contents('php://input');
		//未通过消息真假性验证
		if ($this->isValid() && $this->validateSignature()) {
			return $_GET['echostr'];
		}
		//签名校验模式
		if (empty($this->rawRequest) && isset($_GET['signature']) && isset($_GET['echostr']) && !$noCheckSignature) {
			$this->checkSignature();
		}
		//接受并解析微信中心POST发送XML数据
		$xml = (array)simplexml_load_string($this->rawRequest, 'SimpleXMLElement', LIBXML_NOCDATA);
		$this->request = array_change_key_case($xml, CASE_LOWER);
	}

	/**
	 * 判断此次请求是否为验证请求
	 * @return boolean
	 */
	protected function isValid() {
		return isset($_GET['echostr']);
	}

	/**
	 * 判断验证请求的签名信息是否正确
	 * @param  string $token 验证信息
	 * @return boolean
	 */
	protected function validateSignature() {
		$signature = $_GET['signature'];
		$timestamp = $_GET['timestamp'];
		$nonce = $_GET['nonce'];
		$signatureArray = array($this->token, $timestamp, $nonce);
		sort($signatureArray, SORT_STRING);
		return sha1(implode($signatureArray)) == $signature;
	}

	/**
	 * 获取本次请求中的参数，不区分大小
	 * @param  string $param 参数名，默认为无参
	 * @return mixed
	 */
	public function getRequest($param = FALSE) {
		if ($param === FALSE) {
			return $this->request;
		}
		$param = strtolower($param);
		if (isset($this->request[$param])) {
			return $this->request[$param];
		}
		return NULL;
	}

	/**
	 * 分析消息类型
	 * @return void
	 */
	public function switchType() {
		return Request::switchType($this->request);
	}

	public function checkSignature() {
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$tmpArr = array($this->token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = join($tmpArr);
		$tmpStr = sha1($tmpStr);

		if ($tmpStr == $signature) {
			echo $_GET['echostr'];
			return true;
		} else {
			return false;
		}
	}
}