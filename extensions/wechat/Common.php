<?php
/**
 * 常用类，包括基本配置，错误码常量等
 * 
 * @author lane&ShuangYa
 * @package SYFramework
 * @category Extension
 * @link http://www.lanecn.com http://www.sylingd.com/
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */
namespace sy\tool\wechat;
use \sy\lib\YFetchURL;
class Common {

	//-------系统错误相关--101 到200 ------
	const ERROR_SYSTEM = 101; //系统错误
	const ERROR_NEWS_ITEM_COUNT_MORE_TEN = 102; //图文消息的项数超过10
	const ERROR_MENU_CLICK = 103; //微信这个坑爹货，菜单跳转失败，请重试。
	

	//-------用户输入相关--1001到1100------
	const ERROR_INPUT_ERROR = 1001; //输入有误，请重新输入
	const ERROR_UNKNOW_TYPE = 1002; //收到了未知类型的消息
	const ERROR_CAPTCHA_ERROR = 1003; //验证码错误
	const ERROR_REQUIRED_FIELDS = 1004; //必填项未填写全

	//-------远程调用相关--1201到1300------
	const ERROR_REMOTE_SERVER_NOT_RESPOND = 1201; //远程服务器未响应
	const ERROR_GET_ACCESS_TOKEN = 1202; //获取ACCESS_TOKEN失败

	//-------文章管理相关--1301到1400------

	//-------分类管理相关--1401到1500------
	const ERROR_MENU_NOT_EXISTS = 1401; //菜单不存在

	//-------文案类-----------------------
	const ERROR_NO_BINDING_TEXT = '对不起，您尚未绑定微信'; //未绑定微信时错误文案
	
	//-------其他配置---------------------
	const URL = 'https://api.weixin.qq.com/';
	const FileURL = 'http://file.api.weixin.qq.com/';
	const MpURL = 'https://mp.weixin.qq.com/';
	
	//-------微信公众号相关---------------
	public static $APPID = '';
	public static $APPSECRET = '';
	
	
	//-------微信支付配置-----------------
	public static $MCHID = '';
	public static $PAYKEY = '';
	/**
	 * 默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少开启错误上报。
	 * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
	 */
	const PAY_REPORT = 1;
	const PAY_SSLKEY = __DIR__ . '/data/apiclient_key.pem';
	const PAY_SSLCERT = __DIR__ . '/data/apiclient_cert.pem';
	
	//-------微信支付缺少参数--1501到1550-
	const ERROR_PAY_NO_NOTIFY = 1501; //未设置NotifyURL
	const ERROR_PAY_NO_OPENID = 1502; //缺少必填参数openid
	const ERROR_PAY_NO_OUTTRADENO = 1503; //缺少必填参数out_trade_no
	const ERROR_PAY_NO_BODY = 1504; //缺少必填参数body
	const ERROR_PAY_NO_TOTALFEE = 1505; //缺少必填参数total_fee
	const ERROR_PAY_NO_TRADETYPE = 1506; //缺少必填参数trade_type
	const ERROR_PAY_NO_PRODUCTID = 1507; //缺少必填参数produce_id
	const ERROR_PAY_NO_QUERY = 1508; //out_trade_no、transaction_id至少一个
	const ERROR_PAY_NO_REFUNDFEE = 1509; //缺少必填参数refund_fee
	const ERROR_PAY_NO_BILLDATE = 1510; //缺少必填参数bill_date
	const ERROR_PAY_NO_AUTHCODE = 1511; //缺少必填参数auth_code
	const ERROR_PAY_NO_URL = 1512; //没有传递URL
	
	public static function setWx($id, $secret) {
		self::$APPID = $id;
		self::$APPSECRET = $secret;
	}
	public static function setWxPay($MCHID, $PAYKEY) {
		self::$MCHID = $MCHID;
		self::$PAYKEY = $PAYKEY;
	}
	public static function FetchURL($opt, $is_json = TRUE) {
		$opt = array_merge(array_change_key_case($opt, CASE_LOWER), ['sslversion' => 1, 'header' => 0]);
		$r = YFetchURL::i($opt)->exec();
		if ($is_json) {
			return json_decode($r, 1);
		} else {
			return $r;
		}
	}
}