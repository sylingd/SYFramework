<?php

/**
 * 基本类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy;
use \sy\base\SYException;

//将系统异常封装为自有异常
set_exception_handler(function ($e) {
	@header('Content-Type:text/html; charset=utf-8');
	if (!($e instanceof SYException)) {
		$e = new SYException($e->getMessage(), '10000', [$e->getFile(), $e->getLine()]);
	}
	echo $e->getHtml();
	exit;
});

class BaseSY {
	//应用相关设置
	public static $app;
	public static $appDir;
	public static $siteDir;
	public static $frameworkDir;
	public static $rootDir;
	public static $webrootDir;
	//会从data下的相应文件读取
	public static $mimeTypes = NULL;
	public static $httpStatus = NULL;
	//路由参数名称
	public static $routeParam = 'r';
	//调试模式
	public static $debug = TRUE;
	//CLI模式
	public static $isCli = FALSE;
	/**
	 * 初始化：创建Application（通用）
	 * @access protected
	 * @param mixed $config设置
	 */
	protected static function createApplicationInit($config = NULL) {
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
		//框架所在的绝对路径
		static::$rootDir = str_replace('\\', '/', realpath(static::$frameworkDir . '../')) . '/';
		//程序相对网站根目录所在
		$now = $_SERVER['PHP_SELF'];
		$dir = str_replace('\\', '/', dirname($now));
		$dir !== '/' && $dir = rtrim($dir, '/') . '/';
		static::$siteDir = $dir;
		//网站根目录
		static::$webrootDir = substr(static::$rootDir, 0, strlen(static::$rootDir) - strlen(static::$siteDir)) . '/';
		//基本信息
		$config['cookie']['path'] = str_replace('@app/', $dir, $config['cookie']['path']);
		static::$app = $config;
		//应用的绝对路径
		static::$appDir = str_replace('\\', '/', realpath(static::$frameworkDir . $config['dir'])) . '/';
		if (isset($config['debug'])) {
			static::$debug = $config['debug'];
		}
		//编码相关
		mb_internal_encoding($config['charset']);
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
	public static function createApplication($config = NULL) {
		static::createApplicationInit($config);
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
	public static function createConsoleApplication($config = NULL) {
		static::createApplicationInit($config);
		if (!static::$isCli) {
			throw new SYException('Must run at CLI mode', '10005');
		}
		if (isset(static::$app['console'])) {
			list($fileName, $callback) = static::$app['console'];
			require(static::$appDir . '/' . $fileName);
			if (is_callable($callback)) {
				call_user_func($callback);
			}
		}
	}
	/**
	 * 报错：HTTP状态
	 * @access public
	 * @param string $status 状态码
	 * @param boolean $end 是否自动结束当前请求
	 */
	public static function httpStatus($status, $end = FALSE) {
		if (static::$httpStatus === NULL) {
			static::$httpStatus = require(static::$frameworkDir . 'data/httpStatus.php');
		}
		$version = ((isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') ? '1.0' : '1.1');
		if (isset(static::$httpStatus[$status])) {
			$statusText = static::$httpStatus[$status];
			@header("HTTP/$version $status $statusText");
		} else {
			@header("HTTP/$version $status");
		}
		if ($end) {
			echo isset($statusText) ? $statusText : $status . ' error';
			exit;
		}
	}
	/**
	 * 简单Router
	 * @access public
	 */
	public static function router() {
		$r = trim($_GET[static::$routeParam]);
		if (empty($r)) {
			$r = static::$app['defaultRouter'];
		}
		if (strpos($r, '.') !== FALSE || strpos($r, '/') === FALSE) {
			static::httpStatus('404', TRUE);
		}
		//多级路由支持
		$last = strrpos($r, '/');
		$controllerName = substr($r, 0, $last);
		$actionName = substr($r, $last + 1);
		//Alias路由表
		if (isset(static::$app['alias'][$controllerName])) {
			$controllerName = static::$app['alias'][$controllerName];
		}
		$isPath = strpos($controllerName, '/');
		//controller列表
		if (!in_array($controllerName, static::$app['controller'], TRUE)) {
			static::httpStatus('404', TRUE);
		}
		//多级路由时，引入顶级路由（如果存在）
		if ($isPath !== FALSE) {
			$topController = substr($controllerName, 0, $isPath);
			$topControllerFile = static::$appDir . 'controllers/' . $topController . '/_base.php';
			if (is_file($topControllerFile)) {
				require($topControllerFile);
			}
		}
		//初始化Controller
		$fileName = static::$appDir . 'controllers/' . $controllerName . '.php';
		if ($isPath !== FALSE) {
			$className = substr($controllerName, strrpos($controllerName, '/') + 1);
		} else {
			$className = $controllerName;
		}
		$className = 'C' . ucfirst($className);
		if (!is_file($fileName)) {
			throw new SYException('Controller ' . $controllerName . ' not exists', '10004');
		}
		if (!class_exists($className, FALSE)) {
			require($fileName);
		}
		$controller = new $className;
		//执行动作
		$actionName = 'action' . ucfirst($actionName);
		if (!method_exists($controller, $actionName)) {
			static::httpStatus('404', TRUE);
		}
		$controller->$actionName();
	}
	/**
	 * 自动加载类
	 * @access public
	 * @param string $className
	 */
	public static function autoload($className) {
		//判断是否为框架的class
		if (strpos($className, 'sy\\') === FALSE) {
			//是否为App自有class
			if (isset(static::$app['class'][$className])) {
				$fileName = str_replace('@app/', static::$appDir, static::$app['class'][$className]);
			} else {
				return;
			}
		} elseif (strpos($className, 'sy\\') === 0) {
			$fileName = substr($className, 3) . '.php';
			$fileName = static::$frameworkDir . str_replace('\\', '/', $fileName);
		} else {
			return;
		}
		if (is_file($fileName)) {
			require($fileName);
		}
	}
	/**
	 * 创建URL
	 * @access public
	 * @param mixed $param URL参数
	 * @param string $ext 自定义扩展名
	 * @return string
	 */
	public static function createUrl($param = '', $ext = NULL) {
		$param = (array )$param;
		$router = $param[0];
		$anchor = isset($param['#']) ? '#' . $param['#'] : '';
		unset($param[static::$routeParam], $param['#']);
		//基本URL
		$url = ($_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		if ($param[0] === '') {
			return $url . static::$siteDir;
		}
		unset($param[0]);
		//多级路由支持
		$last = strrpos($router, '/');
		$controllerName = substr($router, 0, $last);
		$actionName = substr($router, $last + 1);
		//Alias路由表
		if (in_array($controllerName, static::$app['alias'], TRUE)) {
			$controllerName = array_search($controllerName, static::$app['alias']);
		}
		//是否启用了Rewrite
		if (static::$app['rewrite'] && isset(static::$app['rewriteRule'][$router])) {
			$url .= str_replace('@root/', static::$siteDir, static::$app['rewriteRule'][$router]);
			foreach ($param as $k => $v) {
				$k_tpl = '{{' . $k . '}}';
				if (strpos($url, $k_tpl) === FALSE) {
					continue;
				}
				$url = str_replace($k_tpl, urlencode($v), $url);
				//去掉此参数，防止后面http_build_query重复
				unset($param[$k]);
			}
		} elseif (static::$app['rewrite']) {
			$url .= static::$siteDir . $controllerName . '/' . $actionName . '.' . ($ext === NULL ? static::$app['rewriteExt'] : $ext);
		} else {
			$url .= static::$siteDir . 'index.php?r=' . $controllerName . '/' . $actionName;
		}
		if (count($param) > 0) {
			if (strpos($url, '?') === FALSE) {
				$url .= '?';
			} else {
				$url .= '&';
			}
			$url .= http_build_query($param);
		}
		$url .= $anchor;
		return $url;
	}
	/**
	 * 发送Content-type的header，也就是mimeType
	 * @access public
	 * @param string $type 可为文件扩展名，或者Content-type的值
	 */
	public static function setMimeType($type) {
		$mimeType = static::getMimeType($type);
		if ($mimeType === NULL) {
			$mimeType = $type;
		}
		$header = 'Content-type:' . $mimeType . ';';
		if (in_array($type, ['js', 'json', 'atom', 'rss', 'xhtml'], TRUE) || substr($mimeType, 0, 5) === 'text/') {
			$header .= ' charset=' . static::$app['charset'];
		}
		@header($header);
	}
	/**
	 * 获取扩展名对应的mimeType
	 * @access public
	 * @param string $ext
	 * @return string
	 */
	public static function getMimeType($ext) {
		if (static::$mimeTypes === NULL) {
			static::$mimeTypes = require(static::$frameworkDir . 'data/mimeTypes.php');
		}
		$ext = strtolower($ext);
		return isset(static::$mimeTypes[$ext]) ? (static::$mimeTypes[$ext]) : null;
	}
	/**
	 * 获取模板路径
	 * @access public
	 * @param string $tpl 模板文件
	 */
	public static function viewPath($tpl) {
		return static::$appDir . 'views/' . $tpl . '.php';
	}
	/**
	 * 引入模板
	 * @access public
	 * @param string $__tpl 模板文件
	 * @param array $_param 参数
	 */
	public static function view($__tpl, $_param = NULL) {
		//是否启用CSRF验证
		if (static::$app['csrf']) {
			$_csrf_token = \sy\lib\YSecurity::csrfGetHash();
		}
		if (is_array($_param)) {
			unset($_param['__tpl'], $_param['__viewPath'], $_param['_csrf_token']);
			extract($_param);
		}
		$__viewPath = static::viewPath($__tpl);
		if (is_file($__viewPath)) {
			include($__viewPath);
		}
	}
	/**
	 * 获取Model操作类
	 * @access public
	 * @param string $modelName
	 * @return object
	 */
	public static function model($modelName) {
		//Model名称
		$modelClass = 'M' . ucfirst($modelName);
		if (!class_exists($modelClass)) {
			$fileName = static::$appDir . 'models/' . $modelName . '.php';
			if (!is_file($fileName)) {
				throw new SYException('Model ' . $fileName . ' not exists', '10010');
			}
			require ($fileName);
		}
		return $modelClass::i();
	}
}
