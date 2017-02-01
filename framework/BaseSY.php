<?php

/**
 * 基本类
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

//载入依赖库
if (!trait_exists('sy\App', FALSE)) {
	require(__DIR__ . '/App.php');
}
if (!trait_exists('sy\Stratified', FALSE)) {
	require(__DIR__ . '/Stratified.php');
}

//将系统异常封装为自有异常
set_exception_handler(function ($e) {
	if (isset(\Sy::$httpServer) && \Sy::$httpServer !== NULL) {
		\Sy::setMimeType('Content-Type:text/html; charset=utf-8');
	}
	if (!($e instanceof SYException)) {
		$e = new SYException($e->getMessage(), '10000', [$e->getFile(), $e->getLine()]);
	}
	echo strval($e);
	if (isset(\Sy::$httpServer) && \Sy::$httpServer !== NULL) {
		exit;
	}
});

class BaseSY {
	use App;
	use Stratified;
	//会从data下的相应文件读取
	public static $mimeTypes = NULL;
	public static $httpStatus = NULL;
	//路由参数名称
	public static $routeParam = 'r';
	//调试模式
	public static $debug = TRUE;
	//CLI模式
	public static $isCli = FALSE;
	//Hook列表
	public static $hookList = [];
	public static $hookListObj = [];
	/**
	 * 获取HTTP状态文字
	 * @access public
	 * @param string $status 状态码
	 */
	public static function getHttpStatus($status) {
		if (static::$httpStatus === NULL) {
			static::$httpStatus = require(static::$frameworkDir . 'data/httpStatus.php');
		}
		$version = ((isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') ? '1.0' : '1.1');
		if (isset(static::$httpStatus[$status])) {
			$statusText = static::$httpStatus[$status];
			return "HTTP/$version $status $statusText";
		} else {
			return "HTTP/$version $status";
		}
	}
	/**
	 * 简单Router
	 * @access public
	 */
	public static function router($r = NULL, $requestId = NULL) {
		if ($r === NULL) {
			$r = trim($_GET[static::$routeParam]);
		}
		if (empty($r)) {
			$r = static::$app->get('defaultRouter');
		}
		if (strpos($r, '.') !== FALSE || strpos($r, '/') === FALSE) {
			if (NULL === $requestId) {
				header(static::getHttpStatus('404'));
				exit;
			} else {
				static::$httpResponse[$requestId]->status(404);
				return;
			}
		}
		//解析controller名称和action名称
		$last = strrpos($r, '/');
		$controllerName = substr($r, 0, $last);
		$actionName = substr($r, $last + 1);
		//获取操作类
		$controller = static::controller($controllerName);
		if (NULL === $controller) {
			if (NULL === $requestId) {
				header(static::getHttpStatus('404'));
				exit;
			} else {
				static::$httpResponse[$requestId]->status(404);
				return;
			}
		}
		//执行动作
		$actionName = 'action' . ucfirst($actionName);
		if (!method_exists($controller, $actionName)) {
			if (NULL === $requestId) {
				header(static::getHttpStatus('404'));
				exit;
			} else {
				static::$httpResponse[$requestId]->status(404);
				return;
			}
		}
		if ($requestId !== NULL) {
			call_user_func([$controller, $actionName], $requestId);
		} else {
			call_user_func([$controller, $actionName]);
		}
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
			if (static::$app->has('class.' . $className)) {
				$fileName = str_replace('@app/', static::$appDir, static::$app->get('class.' . $className));
			} elseif (is_string(static::$app->get('appNamespace')) && strpos($className, static::$app->get('appNamespace') . '\\') === 0) {
				//namespace匹配
				$fileName = substr($className, strlen(static::$app->get('appNamespace')) + 1) . '.php';
				$fileName = static::$appDir . str_replace('\\', '/', $fileName);
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
		//Hook
		$hookResult = static::triggerHook('sy_createUrl', [$router, $anchor, $param, $ext]);
		if (is_string($hookResult)) {
			return $hookResult;
		}
		//基本URL
		$url = '';
		if (empty($router)) {
			return static::$sitePath;
		}
		unset($param[0]);
		//多级路由支持
		$last = strrpos($router, '/');
		$controllerName = substr($router, 0, $last);
		$actionName = substr($router, $last + 1);
		//是否启用了Rewrite
		if (static::$app->get('rewrite') && static::$app->has('rewriteRule.' . $router)) {
			$url .= str_replace('@root/', static::$sitePath, static::$app->get('rewriteRule.' . $router));
			foreach ($param as $k => $v) {
				$k_tpl = '{{' . $k . '}}';
				if (strpos($url, $k_tpl) === FALSE) {
					continue;
				}
				$url = str_replace($k_tpl, $v, $url);
				//去掉此参数，防止后面http_build_query重复
				unset($param[$k]);
			}
		} elseif (static::$app->get('rewrite')) {
			if ($ext === NULL && empty(static::$app->get('rewriteExt'))) {
				$url .= static::$sitePath . $controllerName . '/' . $actionName;
			} else {
				$url .= static::$sitePath . $controllerName . '/' . $actionName . '.' . ($ext === NULL ? static::$app->get('rewriteExt') : $ext);
			}
		} else {
			$url .= static::$sitePath . 'index.php?r=' . $controllerName . '/' . $actionName;
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
		$header = $mimeType . ';';
		if (in_array($type, ['js', 'json', 'atom', 'rss', 'xhtml'], TRUE) || substr($mimeType, 0, 5) === 'text/') {
			$header .= ' charset=' . static::$app->get('charset');
		}
		header('Content-type:' . $header);
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
		if (static::$app->get('csrf')) {
			$_param['_csrf_token'] = \sy\lib\YSecurity::csrfGetHash();
		}
		if (is_array($_param)) {
			extract($_param, EXTR_SKIP);
		}
		$__viewPath = static::viewPath($__tpl);
		if (is_file($__viewPath)) {
			include($__viewPath);
		}
	}
}
