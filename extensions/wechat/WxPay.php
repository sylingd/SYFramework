<?php
/**
 * 
 * 接口访问类，包含所有微信支付API列表的封装，类中方法为static方法，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 *
 * @author ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */
 
namespace sy\tool\wechat;
use \sy\lib\YFetchURL;
class WxPay {
	/**
	 * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function unifiedOrder($input, $timeOut = 6) {
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		//检测必填参数
		if (!isset($input['out_trade_noSet'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_OUTTRADENO, "缺少统一支付接口必填参数out_trade_no");
		} elseif (!isset($input['body'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_BODY, "缺少统一支付接口必填参数body");
		} elseif (!isset($input['total_fee'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_TOTALFEE, "缺少统一支付接口必填参数total_fee");
		} elseif (!isset($input['trade_type'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_TRADETYPE, "缺少统一支付接口必填参数trade_type");
		}
		//关联参数
		if ($input['trade_type'] === "JSAPI" && !isset($input['openid'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_OPENID, "统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！");
		}
		if ($input['trade_type'] === "NATIVE" && !isset($input['product_id'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_PRODUCTID, "统一支付接口中，缺少必填参数product_id！trade_type为JSAPI时，product_id为必填参数！");
		}
		//异步通知url未设置
		if(!isset($input['notify_url'])){
			Msg::returnErrMsg(Common::ERROR_PAY_NO_NOTIFY, '缺少必填参数Notify_url');
		}
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['spbill_create_ip'] = $_SERVER['REMOTE_ADDR']; //终端ip	      
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		//签名
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		//上报
		$result = new WxPayData($response);
		self::reportCostTime($url, $startTimeStamp, $result->values);//上报请求花费时间
		return $result;
	}
	
	/**
	 * 查询订单，array中out_trade_no、transaction_id至少填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function orderQuery($input, $timeOut = 6) {
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//检测必填参数
		if(!isset($input['out_trade_no']) && !isset($input['transaction_id'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_ORDERQUERY, "订单查询接口中，out_trade_no、transaction_id至少填一个！");
		}
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		//签名
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		$startTimeStamp = self::getMillisecond(); //请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		//上报
		$result = new WxPayData($response);
		self::reportCostTime($url, $startTimeStamp, $result->values);//上报请求花费时间
		
		return $result;
	}
	/**
	 * 关闭订单，WxPayCloseOrder中out_trade_no必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function closeOrder($input, $timeOut = 6) {
		$url = "https://api.mch.weixin.qq.com/pay/closeorder";
		//检测必填参数
		if(!isset($input['out_trade_no'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_QUERY, "订单查询接口中，out_trade_no必填！");
		}
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		//上报
		$result = new WxPayData($response);
		self::reportCostTime($url, $startTimeStamp, $result->values);//上报请求花费时间
		
		return $result;
	}

	/**
	 * 申请退款，WxPayRefund中out_trade_no、transaction_id至少填一个且
	 * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function refund($input, $timeOut = 6) {
		$url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
		//检测必填参数
		if(!isset($input['out_trade_no']) && !isset($input['transaction_id'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_QUERY, "退款申请接口中，out_trade_no、transaction_id至少填一个！");
		} elseif (!isset($input['out_refund_no'])){
			Msg::returnErrMsg(Common::ERROR_PAY_NO_OUTTRADENO, "退款申请接口中，缺少必填参数out_refund_no！");
		} elseif (!isset($input['total_fee'])){
			Msg::returnErrMsg(Common::ERROR_PAY_NO_TOTALFEE, "退款申请接口中，缺少必填参数total_fee！");
		} elseif (!isset($input['refund_fee'])){
			Msg::returnErrMsg(Common::ERROR_PAY_NO_REFUNDFEE, "退款申请接口中，缺少必填参数refund_fee！");
		} elseif (!isset($input['op_user_id'])){
			Msg::returnErrMsg(Common::ERROR_PAY_NO_OPENID, "退款申请接口中，缺少必填参数op_user_id！");
		}
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, true, $timeOut);
		//上报
		$result = new WxPayData($response);
		self::reportCostTime($url, $startTimeStamp, $result->values);//上报请求花费时间
		
		return $result;
	}
	
	/**
	 * 查询退款
	 * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，
	 * 用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态。
	 * WxPayRefundQuery中out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function refundQuery($input, $timeOut = 6) {
		$url = "https://api.mch.weixin.qq.com/pay/refundquery";
		//检测必填参数
		if(!isset($input['out_refund_no']) && !isset($input['out_trade_no']) && !isset($input['transaction_id']) && !isset($input['refund_id'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_QUERY, "退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！");
		}
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		//上报
		$result = new WxPayData($response);
		self::reportCostTime($url, $startTimeStamp, $result->values);//上报请求花费时间
		
		return $result;
	}
	
	/**
	 * 下载对账单，WxPayDownloadBill中bill_date为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function downloadBill($input, $timeOut = 6) {
		$url = "https://api.mch.weixin.qq.com/pay/downloadbill";
		//检测必填参数
		if(!isset($input['bill_date'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_BILLDATE, "对账单接口中，缺少必填参数bill_date！");
		}
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		if (substr($response, 0 , 5) === "<xml>") {
			return '';
		}
		return $response;
	}
	/**
	 * 提交被扫支付API
	 * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
	 * 由商户收银台或者商户后台调用该接口发起支付。
	 * WxPayWxPayMicroPay中body、out_trade_no、total_fee、auth_code参数必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 */
	public static function micropay($input, $timeOut = 10) {
		$url = "https://api.mch.weixin.qq.com/pay/micropay";
		//检测必填参数
		if(!isset($input['body'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_BODY, "提交被扫支付API接口中，缺少必填参数body！");
		}  elseif (!isset($input['out_trade_no'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_OUTTRADENO, "提交被扫支付API接口中，缺少必填参数out_trade_no！");
		}  elseif (!isset($input['total_fee'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_TOTALFEE, "提交被扫支付API接口中，缺少必填参数total_fee！");
		}  elseif (!isset($input['auth_code'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_AUTHCODE, "提交被扫支付API接口中，缺少必填参数auth_code！");
		}
		
		$input['spbill_create_ip'] = $_SERVER['REMOTE_ADDR']; //终端ip
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		//上报
		$result = new WxPayData($response);
		self::reportCostTime($url, $startTimeStamp, $result->values);//上报请求花费时间
		
		return $result;
	}
	
	/**
	 * 撤销订单API接口，WxPayReverse中参数out_trade_no和transaction_id必须填写一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 */
	public static function reverse($input, $timeOut = 6) {
		$url = "https://api.mch.weixin.qq.com/secapi/pay/reverse";
		//检测必填参数
		if(!isset($input['out_trade_no']) && !isset($input['transaction_id'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_QUERY, "撤销订单API接口中，参数out_trade_no和transaction_id必须填写一个！");
		}
		
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, true, $timeOut);
		//上报
		$result = new WxPayData($response);
		self::reportCostTime($url, $startTimeStamp, $result->values);//上报请求花费时间
		
		return $result;
	}
	/**
	 * 测速上报，该方法内部封装在report中，使用时请注意异常流程
	 * WxPayReport中interface_url、return_code、result_code、user_ip、execute_time_必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function report($input, $timeOut = 1) {
		$url = "https://api.mch.weixin.qq.com/payitil/report";
		//检测必填参数
		if (!isset($input['interface_url']) || !isset($input['return_code']) || !isset($input['result_code']) || !isset($input['user_ip']) || !isset($input['execute_time_'])) {
			return FALSE;
		}
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['user_ip'] = $_SERVER['REMOTE_ADDR'];//终端ip
		$input['time'] = date("YmdHis");//商户上报时间	 
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		return $response;
	}
	
	/**
	 * 生成二维码规则,模式一生成支付二维码
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param array $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function bizpayurl($input, $timeOut = 6) {
		if(!isset($input['Product_id'])){
			Msg::returnErrMsg(Common::ERROR_PAY_NO_PRODUCTID, "生成二维码，缺少必填参数product_id！");
		}
		
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['time_stamp'] = $_SERVER['REQUEST_TIME']; //时间戳
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		
		return $inputObj->values;
	}
	
	/**
	 * 转换短链接
	 * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
	 * 减小二维码数据量，提升扫描速度和精确度。
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayShortUrl $input
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function shorturl($input, $timeOut = 6) {
		$url = "https://api.mch.weixin.qq.com/tools/shorturl";
		//检测必填参数
		if(!isset($input['Long_url'])) {
			Msg::returnErrMsg(Common::ERROR_PAY_NO_URL, "需要转换的URL，签名用原串，传输需URL encode！");
		}
		$input['appid'] = Common::$APPID; //公众账号ID
		$input['mch_id'] = Common::$MCHID; //商户号
		$input['nonce_str'] = self::getNonceStr(); //随机字符串
		
		$inputObj = new WxPayData($input);
		$inputObj->sign();
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		//上报
		$result = new WxPayData($response);
		self::reportCostTime($url, $startTimeStamp, $result->values);//上报请求花费时间
		
		return $result;
	}
	/**
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32)  {
		$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		while (strlen($str) < $length) {
			$str .= $str;
		}
		$str = str_shuffle($str);
		return substr($str, 0, $length);
	}
	/**
	 * 上报数据， 上报的时候将屏蔽所有异常流程
	 * @param string $usrl
	 * @param int $startTimeStamp
	 * @param array $data
	 */
	private static function reportCostTime($url, $startTimeStamp, $data) {
		//如果不需要上报数据
		if(Common::PAY_REPORT == 0){
			return;
		} 
		//如果仅失败上报
		if (Common::PAY_REPORT == 1 && isset($data['return_code']) && $data["return_code"] == "SUCCESS" && isset($data['result_code']) && $data["result_code"] == "SUCCESS") {
		 	return;
		}
		//上报逻辑
		$endTimeStamp = self::getMillisecond();
		$input = [];
		$input['interface_url'] = $url;
		$input['execute_time_'] = $endTimeStamp - $startTimeStamp;
		//返回状态码
		if (isset($data['return_code'])) {
			$input['return_code'] = $data["return_code"];
		}
		//返回信息
		if (isset($data['return_msg'])) {
			$input['return_msg'] = $data["return_msg"];
		}
		//业务结果
		if (isset($data['result_code'])) {
			$input['result_code'] = $data["result_code"];
		}
		//错误代码
		if (isset($data['err_code'])) {
			$input['err_code'] = $data["err_code"];
		}
		//错误代码描述
		if (isset($data['err_code_des'])) {
			$input['err_code_des'] = $data["err_code_des"];
		}
		//商户订单号
		if (isset($data['out_trade_no'])) {
			$input['out_trade_no'] = $data["out_trade_no"];
		}
		//设备号
		if (isset($data['device_info'])) {
			$input['device_info'] = $data["device_info"];
		}
		try {
			self::report($input);
		} catch (\Exception $e) {
			//不做任何处理
		}
	}

	/**
	 * 以post方式提交xml到对应的接口url
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 */
	private static function postXmlCurl($xml, $url, $useCert = false, $second = 30) {
		$ch = YFetchURL::i([
			'url' => $url,
			'ssl_verifypeer' => 1,
			'ssl_verifyhost' => 2,
			'timeout' => $second,
			'header' => 0,
			'postfields' => $xml
		]);
		if ($useCert) {
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			$ch->setopt([
				'sslcerttype' => 'PEM',
				'sslcert' => Common::PAY_SSLCERT,
				'sslkeytype' => 'PEM',
				'sslkey' => Common::PAY_SSLKEY
			]);
		}
		return $ch->exec();
	}
	
	/**
	 * 获取毫秒级别的时间戳
	 */
	private static function getMillisecond() {
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}
	/**
	 * 获取WxPayNotify接口
	 * @access public
	 * @return object
	 */
	public static function getNotify($type = 'default') {
		$classname = 'WxPay' . ucfirst($type) . 'Notify';
		if (class_exists($classname, FALSE)) {
			return new $classname;
		}
	}
	/**
	 * 基本回调
	 */
	public function notify() {
		//获取通知的数据
		$xml = file_get_contents('php://input');
		//如果返回成功则验证签名
		$result = TRUE;
		$resultObj = new WxPayData;
		$resultObj->values['return_code'] = 'SUCCESS';
		$resultObj->values['return_msg'] = 'OK';
		try {
			$resultXml = WxPayData::init($xml);
		} catch (\Exception $e) {
			$result = FALSE;
			$resultObj->values['return_code'] = 'FAIL';
			$resultObj->values['return_msg'] = $e->getMessage();
		}
		return [$result, $resultObj, $resultXml];
	}
}


/**
 * 
 * 数据对象基础类，该类中定义数据类最基本的行为，包括：
 * 计算/设置/获取签名、输出xml格式的参数、从xml读取数据对象等
 * @author widyhu
 *
 */
class WxPayData {
	public $values = array();
	/**
	 * 初始化
	 * @param string/array $values
	 * @param boolean $noCheckSign
	 * @throws \Exception
	 */
	public function __construct ($values = '', $noCheckSign = FALSE) {
		if (is_array($values)) {
			$this->values = $values;
			if (!$noCheckSign) {
				$this->CheckSign();
			}
		} else {
			if (!empty($values)) {
				$this->FromXml($values);
			}
			$this->CheckSign();
		}
	}
	/**
	* 获取签名，详见签名生成算法
	* @param string $value 
	**/
	public function sign() {
		if (!isset($this->values['sign'])) {
			$sign = $this->MakeSign();
			$this->values['sign'] = $sign;
		}
		return $this->values['sign'];
	}
	/**
	 * 输出xml字符
	 * @throws \Exception
	**/
	public function ToXml() {
		if (!is_array($this->values) || count($this->values) <= 0) {
			throw new \Exception("数组数据异常！");
		}
		$xml = '<xml>';
		foreach ($this->values as $k => $v) {
			if (is_numeric($val)){
				$xml .= '<' . $k . '>' . $v . '</' . $k . '>';
			} else {
				$xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
			}
		}
		$xml .= '</xml>';
		return $xml; 
	}
	/**
	 * 将xml转为array
	 * @param string $xml
	 * @throws \Exception
	 */
	public function FromXml($xml) {	
		if(!$xml){
			throw new \Exception("xml数据异常！");
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$this->values = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);		
		return $this->values;
	}
	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams() {
		$buff = '';
		foreach ($this->values as $k => $v) {
			if ($k !== 'sign' && $v !== '' && !is_array($v)) {
				$buff .= $k . '=' . $v . '&';
			}
		}
		$buff = rtrim($buff, '&');
		return $buff;
	}
	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用sign方法赋值
	 */
	public function MakeSign() {
		//按字典序排序参数
		ksort($this->values);
		$string = $this->ToUrlParams();
		//在string后加入KEY
		$string = $string . '&key=' . Common::$PAYKEY;
		//MD5加密
		$string = md5($string);
		//所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
	/**
	 * 检测签名
	 */
	public function CheckSign() {
		if(!isset($this->values['sign'])){
			throw new \Exception("签名错误！");
		}
		$sign = $this->MakeSign();
		if($this->values['sign'] == $sign){
			return true;
		}
		throw new \Exception("签名错误！");
	}
	/**
	 * 设置参数
	 * @param string $key
	 * @param string $value
	 */
	public function __set($key, $value) {
		$this->values[$key] = $value;
	}
	/**
	 * 读取参数
	 * @param string $key
	 */
	public function __get($key) {
		return (isset($this->values[$key])?$this->values[$key]:NULL);
	}
}

class WxPayDefaultNotify {
	//查询订单
	public function Queryorder($transaction_id) {
		$result = WxPay::orderQuery(['transaction_id' => $transaction_id]);
		if (isset($result['return_code']) && isset($result['result_code']) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
			return TRUE;
		}
		return FALSE;
	}
	public function check($needSign = TRUE) {
		list($result, $returnObj, $resultXml) = WxPay::notify();
		if(!isset($data['transaction_id'])){
			$result = FALSE;
			$resultObj->values['return_code'] = 'FAIL';
			$resultObj->values['return_msg'] = '输入参数不正确';
		} elseif (!$this->Queryorder($data["transaction_id"])) { //查询订单，判断订单真实性
			$result = FALSE;
			$resultObj->values['return_code'] = 'FAIL';
			$resultObj->values['return_msg'] = '订单无效';
		}
		if ($needSign && $result) {
			$resultObj->sign();
		}
		return [$result, $resultObj->ToXml(), $resultXml];
	}
}