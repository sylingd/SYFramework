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

set_exception_handler(function ($e) {
	@header('Content-Type:text/html; charset=utf-8'); echo $e; exit; }
);

define(SY_ROOT, rtrim(str_replace('\\', '/', __DIR__ ), '/') . '/');

class BaseSY {
	//应用相关设置
	public static $app;
	public static $appDir;
	public static $siteDir;
	//会从data下的相应文件读取
	public static $mimeTypes = NULL;
	public static $httpStatus = NULL;
	//路由参数名称
	public static $routeParam = 'r';
	//调试模式
	public static $debug = TRUE;
	/**
	 * 初始化：创建Application
	 * @access public
	 * @param array $config设置
	 */
	public static function createApplication($config = NULL) {
		if ($config === NULL) {
			throw new SYException('缺少配置信息', '10001');
		} elseif (is_string($config)) {
			if (is_file($config)) {
				$config = require ($config);
			} else {
				throw new SYException('配置文件 ' . $config . ' 不存在', '10002');
			}
		} elseif (!is_array($config)) {
			throw new SYException('无法识别配置信息', '10003');
		}
		//本程序相对网站根目录所在
		$now = $_SERVER['PHP_SELF'];
		$dir = str_replace('\\', '/', dirname($now));
		$dir !== '/' && $dir = rtrim($dir, '/') . '/';
		static::$siteDir = $dir;
		//基本信息
		$config['cookie']['path'] = str_replace('@app/', $dir, $config['cookie']['path']);
		static::$app = $config;
		static::$appDir = rtrim(str_replace('\\', '/', realpath(SY_ROOT . $config['dir'])), '/') . '/';
		if (isset($config['debug'])) {
			static::$debug = $config['debug'];
		}
		//是否启用CSRF验证
		if ($config['csrf']) {
			\sy\lib\YSecurity::csrfSetCookie();
		}
		//开始路由分发
		static::router();
	}
	/**
	 * 报错：HTTP状态
	 * @access public
	 * @param string $status 状态码
	 * @param boolean $end 是否自动结束当前请求
	 */
	public static function httpStatus($status, $end = FALSE) {
		if (static::$httpStatus === NULL) {
			static::$httpStatus = require (SY_ROOT . 'data/httpStatus.php');
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
		$r = explode('/', $r);
		if (count($r) !== 2) {
			static::httpStatus('404', TRUE);
		}
		list($controllerName, $actionName) = $r;
		//Alias路由表
		if (isset(static::$app['alias'][$controllerName])) {
			$controllerName = static::$app['alias'][$controllerName];
		}
		//controller列表
		if (!in_array($controllerName, static::$app['controller'], TRUE)) {
			static::httpStatus('404', TRUE);
		}
		$fileName = static::$appDir . 'controllers/' . $controllerName . '.php';
		$className = 'C' . ucfirst($controllerName);
		if (!is_file($fileName)) {
			throw new SYException('Controller ' . $controllerName . ' not exists', '10004');
		}
		if (!class_exists($className, FALSE)) {
			require ($fileName);
		}
		//初始化Controller
		$controller = new $className;
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
			$fileName = SY_ROOT . str_replace('\\', '/', $fileName);
		} else {
			return;
		}
		if (is_file($fileName)) {
			require ($fileName);
		}
	}
	/**
	 * 创建URL
	 * @access public
	 * @param mixed $param URL参数
	 * @param string $ext 自定义扩展名
	 * @return string
	 */
	public static function createUrl($param, $ext = NULL) {
		$param = (array )$param;
		$router = $param[0];
		$anchor = isset($param['#']) ? '#' . $param['#'] : '';
		unset($param[0], $param[static::$routeParam], $param['#']);
		//基本URL
		$url = ($_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		//Alias路由表
		list($controllerName, $actionName) = explode('/', $router);
		if (in_array($controllerName, static::$app['alias'], TRUE)) {
			$controllerName = array_search($controllerName, static::$app['alias']);
		}
		//是否启用了Rewrite
		if (static::$app['rewrite'] && isset(static::$app['rewriteRule'][$router])) {
			$url .= str_replace('@root/', static::$siteDir, static::$app['rewriteRule'][$router]);
			foreach ($param as $k => $v) {
				$k = '{{' . $k . '}}';
				if (strpos($search, $k) === FALSE) {
					continue;
				}
				$url = str_replace($k, $v, $url);
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
			static::$mimeTypes = require (SY_ROOT . 'data/mimeTypes.php');
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
	 * @param string $_tpl 模板文件
	 * @param array $_param 参数
	 */
	public static function view($_tpl, $_param = NULL) {
		//是否启用CSRF验证
		if ($config['csrf']) {
			$_csrf_token = \sy\lib\YSecurity::csrfGetHash();
		}
		if (is_array($_param)) {
			unset($_param['_tpl'], $_param['_csrf_token']);
			extract($_param);
		}
		include (static::viewPath($_tpl));
	}
}
