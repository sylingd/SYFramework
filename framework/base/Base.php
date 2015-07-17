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

define(SY_ROOT, rtrim(str_replace('\\', '/', realpath(__DIR__ . '/../')), '/') . '/');


require (SY_ROOT . 'base/Exception.php');
require (SY_ROOT . 'base/Controller.php');

class BaseSY {
	public static $app;
	public static $appDir;
	public static $siteDir;
	public static $mimeTypes = NULL;
	public static $routeParam = 'r';
	/**
	 * 初始化：创建Application
	 * @access public
	 * @param array $config设置
	 */
	public static function createApplication($config = NULL) {
		if ($config === NULL) {
			throw new SYException('缺少配置信息', '10005');
		} elseif (is_string($config)) {
			if (is_file($config)) {
				$config = require ($config);
			} else {
				throw new SYException('缺少配置信息', '10005');
			}
		} elseif (!is_array($config)) {
			throw new SYException('无法识别配置信息', '10006');
		}
		//基本信息
		static::$app = $config;
		static::$appDir = rtrim(str_replace('\\', '/', realpath(SY_ROOT . $config['dir'])), '/') . '/';
		//本程序相对网站根目录所在
		$now = $_SERVER['PHP_SELF'];
		$dir = str_replace('\\', '/', dirname($now));
		$dir !== '/' && $dir = rtrim($dir, '/');
		static::$siteDir = $dir;
		//开始路由分发
		static::router();
	}
	/**
	 * 报错：HTTP状态
	 * @access public
	 * @param string $status 状态码
	 */
	public static function httpError($status) {
		switch ($status) {
			case '404':
				header('HTTP/1.0 404 Not Found');
				echo '您所请求的页面不存在';
				break;
		}
		exit;
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
			static::httpError('404');
		}
		list($controllerName, $actionName) = $r;
		//Alias路由表
		if (isset(static::$app['alias'][$controllerName])) {
			$controllerName = static::$app['alias'][$controllerName];
		}
		//controller列表
		if (!in_array($controllerName, static::$app['controller'], TRUE)) {
			static::httpError('404');
		}
		$fileName = static::$appDir . 'controllers/' . $controllerName . '.php';
		$className = 'C' . ucfirst($controllerName);
		if (!is_file($fileName)) {
			throw new SYException('Controller ' . $controllerName . ' 未找到', '10003');
		}
		if (!class_exists($className, FLASE)) {
			require ($fileName);
		}
		//初始化Controller
		$controller = new $controllerName;
		$actionName = 'action' . ucfirst($actionName);
		if (!method_exists($controller, $actionName)) {
			static::httpError('404');
		}
		//出错都是抛出异常，因此使用try-catch容错
		try {
			$controller->$actionName();
		}
		catch (SYException $e) {
			echo $e;
			exit;
		}
	}
	/**
	 * 自动加载类
	 * @access public
	 * @param string $className
	 */
	public static function autoload($className) {
		//判断是否为框架的class
		if (strpos($className, 'sy') !== 0) {
			//是否为App自有class
			if (isset(static::$app['class'][$className])) {
				$fileName = str_replace('@app/', static::$appDir, static::$app['class'][$className]);
			} else {
				return;
			}
		} else {
			$fileName = $className;
			$fileName = SY_ROOT . str_replace('\\', '/', $fileName);
		}
		if (is_file($fileName)) {
			require ($fileName);
		} else {
			throw new SYException('类文件 ' . $fileName . ' 不存在', '10000');
		}
		if (!class_exists($className, false)) {
			throw new SYException('类文件 ' . $fileName . ' 存在错误', '10001');
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
			$url .= static::$$siteDir . $controllerName . '/' . $actionName . '.' . ($ext === NULL ? static::$app['rewriteExt'] : $ext);
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
		if (static::$mimeTypes === NULL) {
			static::$mimeTypes = require (SY_ROOT . 'data/mimeTypes.php');
		}
		$type = strtolower($type);
		$type = (isset(static::$mimeTypes[$type]) ? static::$mimeTypes[$type] : $type);
		$header = 'Content-type:' . $type . ';';
		if (in_array($type, ['js', 'json', 'atom', 'rss', 'xhtml'], TRUE) || substr($type, 0, 5) === 'text/') {
			$header .= ' charset=' . static::$app['charset'];
		}
		@header($header);
	}
	/**
	 * 获取模板路径
	 * @access public
	 * @param string $tpl 模板文件
	 */
	public static function viewPath($tpl) {
		return static::$appDir . 'views/' . $_tpl . '.php';
	}
	/**
	 * 引入模板
	 * @access public
	 * @param string $_tpl 模板文件
	 * @param array $_param 参数
	 */
	public static function view($_tpl, $_param = NULL) {
		if (is_array($_param)) {
			unset($_param['_tpl']);
			extract($_param);
		}
		include (static::viewPath($_tpl));
	}
}
