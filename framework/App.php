<?php

/**
 * 应用基本类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015-2016 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy;
use \sy\base\SYException;

trait App {
	//应用相关设置
	public static $app;
	public static $appDir;
	public static $siteDir;
	public static $sitePath;
	public static $frameworkDir;
	//HttpServer模式或RPC模式运行时，为swoole进程
	public static $swServer = NULL;
	public static $swServerInited = FALSE;
	public static $swService = [];
	public static $httpRequest;
	public static $httpResponse;
	/**
	 * 初始化：创建Application（通用）
	 * @access protected
	 * @param mixed $config设置
	 */
	protected static function createApplicationInit($siteDir, $config = NULL) {
		static::$frameworkDir =  str_replace('\\', '/', __DIR__ ) . '/';
		//PHP运行模式
		if (PHP_SAPI === 'cli') {
			static::$isCli = TRUE;
		}
		if ($config === NULL) {
			throw new SYException('Configuration is required', '10001');
		} elseif (is_string($config)) {
			if (is_file($config)) {
				$config = require($config);
			} else {
				throw new SYException('Config file ' . $config . ' not exists', '10002');
			}
		} elseif (!is_array($config)) {
			throw new SYException('Config can not be recognised', '10003');
		}
		//路径相关
		static::$siteDir = str_replace('\\', '/', $siteDir) . '/';
		static::$frameworkDir = str_replace('\\', '/', __DIR__) . '/';
		//基本信息
		$config['cookie']['path'] = str_replace('@app/', $dir, $config['cookie']['path']);
		static::$app = $config;
		//应用的绝对路径
		static::$appDir = str_replace('\\', '/', $config['dir']) . '/';
		if (isset($config['debug'])) {
			static::$debug = $config['debug'];
		}
		//编码相关
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding($config['charset']);
		}
		//加载App的基本函数
		if (is_file(static::$appDir . 'common.php')) {
			require(static::$appDir . 'common.php');
		}
	}
	/**
	 * 初始化：创建WebApplication
	 * @access public
	 * @param mixed $config设置
	 */
	public static function createApplication($siteDir, $config = NULL) {
		static::createApplicationInit($siteDir, $config);
		//网站目录
		$now = $_SERVER['PHP_SELF'];
		$dir = str_replace('\\', '/', dirname($now));
		$dir !== '/' && $dir = rtrim($dir, '/') . '/';
		static::$sitePath = $dir;
		//是否启用CSRF验证
		if (isset(static::$app['csrf']) && static::$app['csrf']) {
			\sy\lib\YSecurity::csrfSetCookie();
		}
		//开始路由分发
		static::router();
	}
	/**
	 * 初始化：创建ConsoleApplication
	 * @access public
	 * @param mixed $config设置
	 */
	public static function createConsoleApplication($siteDir, $config = NULL) {
		global $argv;
		static::createApplicationInit($siteDir, $config);
		//网站目录
		static::$sitePath = '/';
		if (!static::$isCli) {
			throw new SYException('Must run at CLI mode', '10005');
		}
		//仅支持参数方式运行
		$opt = getopt(static::$routeParam . ':');
		//以参数方式运行
		$run = $opt[static::$routeParam];
		if (!empty($run) && isset(static::$app['console'][$run])) {
			list($fileName, $callback) = static::$app['console'][$run];
		} else {
			list($fileName, $callback) = static::$app['console']['default'];
		}
		require(static::$appDir . '/workers/' . $fileName);
		if (is_callable($callback)) {
			call_user_func($callback);
		}
	}
	/**
	 * 初始化：创建SwooleApplication（需要swoole）
	 * 建议使用Nginx等软件作为前端
	 * 
	 * @access public
	 * @param mixed $config设置
	 */
	public static function createSwooleApplication($siteDir, $config = NULL) {
		//swoole检查
		if (!extension_loaded('swoole')) {
			throw new SYException('Extension "Swoole" is required', '10027');
		}
		static::createApplicationInit($siteDir, $config);
		//网站目录
		static::$sitePath = '/';
		if (!static::$isCli) {
			throw new SYException('Must run at CLI mode', '10005');
		}
		//变量初始化
		static::$httpRequest = [];
		static::$httpResponse = [];
		//初始化Swoole
		static::$swServer = new \swoole_http_server(static::$app['swoole']['ip'], static::$app['swoole']['port']);
		//基本事件
		static::$swServer->on('Start', ['\sy\swoole\Server', 'eventStart']);
		static::$swServer->on('ManagerStart', ['\sy\swoole\Server', 'eventManagerStart']);
		static::$swServer->on('WorkerStart', ['\sy\swoole\Server', 'eventWorkerStart']);
		static::$swServer->on('WorkerError', ['\sy\swoole\Server', 'eventWorkerError']);
		static::$swServer->on('Finish', ['\sy\swoole\Server', 'eventFinish']);
	}
}
