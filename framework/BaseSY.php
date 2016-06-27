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
	//应用相关设置
	public static $app;
	public static $appDir;
	public static $siteDir;
	public static $sitePath;
	public static $frameworkDir;
	//会从data下的相应文件读取
	public static $mimeTypes = NULL;
	public static $httpStatus = NULL;
	//路由参数名称
	public static $routeParam = 'r';
	//调试模式
	public static $debug = TRUE;
	//CLI模式
	public static $isCli = FALSE;
	//HttpServer模式运行时，为swoole进程
	public static $httpServer = NULL;
	public static $httpRequest;
	public static $httpResponse;
	//当前Controller
	public static $controller = NULL;
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
		static::createApplicationInit(__DIR__, $config);
		//网站目录
		static::$sitePath = '/';
		if (!static::$isCli) {
			throw new SYException('Must run at CLI mode', '10005');
		}
		//根据参数决定运行Worker
		$opt = getopt('p:');
		if (isset($opt['p'])) {
			//以参数方式运行
			$run = $opt['p'];
		} else {
			$run = $argv[1];
		}
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
	 * 初始化：创建HttpApplication（需要swoole）
	 * 建议使用Nginx等软件作为前端
	 * 
	 * @access public
	 * @param mixed $config设置
	 */
	public static function createHttpApplication($siteDir, $config = NULL) {
		//swoole检查
		if (!extension_loaded('swoole')) {
			throw new SYException('Extension "Swoole" is required', '10027');
		}
		static::createApplicationInit($config);
		//网站目录
		static::$sitePath = '/';
		if (!static::$isCli) {
			throw new SYException('Must run at CLI mode', '10005');
		}
		//变量初始化
		static::$httpRequest = [];
		static::$httpResponse = [];
		//初始化Swoole
		$serv = new swoole_http_server(static::$app['httpServer']['ip'], static::$app['httpServer']['port']);
		$serv->set([
			'worker_num' => static::$app['httpServer']['worker_num'],
			'daemonize' => TRUE
		]);
		if (static::$app['httpServer']['global']) {
			$serv->setGlobal();
		}
		$serv->on('request', function($req, $response) {
			//生成唯一请求ID
			$remoteIp = ($req->server['remote_addr'] === '127.0.0.1' && isset($req->server['http_x_forwarded_for'])) ? $req->server['http_x_forwarded_for'] : $req->server['remote_addr']; //获取真实IP
			$requestId = md5(uniqid($remoteIp, TRUE));
			//设置请求信息
			static::$httpRequest[$requestId] = $req;
			static::$httpResponse[$requestId] = $response;
			//是否启用CSRF验证
			if (isset(static::$app['csrf']) && static::$app['csrf']) {
				\sy\lib\YSecurity::csrfSetCookie($requestId);
			}
			//根据设置，分配重写规则
			ob_start();
			if (static::$app['rewrite']) {
				//自定义规则
				if (is_array(static::$app['rewriteParseRule'])) {
					$matches = NULL;
					foreach (static::$app['rewriteParseRule'] as $oneRule) {
						if (preg_match($oneRule[0], $req->server['request_uri'], $matches)) {
							$route = $oneRule[1];
							$paramName = array_slice($oneRule, 2);
							$param = [];
							foreach ($paramName as $k => $v) {
								$param[$v] = isset($matches[$k + 1]) ? $matches[$k + 1] : '';
							}
							//写入相关的环境变量
							//合并至GET参数
							$req->get = array_merge($param, $req->get);
							$req->server['query_string'] = http_build_query($param);
							$req->server['request_uri'] = $req->server['php_self'] . '?' . $req->server['query_string'];
							if (static::$app['httpServer']['global']) {
								$_GET = $req->get;
								$_SERVER = $req->server;
							}
							break;
						}
					}
				}
				//没有匹配的重写规则
				if (!isset($route)) {
					$url = parse_url($req->server['request_uri']);
					$route = ltrim(preg_replace('/\.(\w+)$/', '', $url['path']), '/'); //去掉末尾的扩展名和开头的“/”符号
					empty($route) && $route = NULL;
				}
				static::router($route, $requestId);
			} else {
				$route = empty($req->get[static::$routeParam]) ? $req->get[static::$routeParam] : NULL;
				static::router($route, $requestId);
			}
			//请求结束，进行清理工作
			try {
				$response->end(ob_get_clean());
				unset(static::$httpRequest[$requestId], static::$httpResponse[$requestId], $requestId);
			} catch (\Exception $e) {
			}
			return;
		});
		$serv->start();
	}
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
	 * @access protected
	 */
	protected static function router($r = NULL, $requestId = NULL) {
		if ($r === NULL) {
			$r = trim($_GET[static::$routeParam]);
		}
		if (empty($r)) {
			$r = static::$app['defaultRouter'];
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
		//多级路由支持
		$last = strrpos($r, '/');
		$controllerName = substr($r, 0, $last);
		$actionName = substr($r, $last + 1);
		$isPath = strpos($controllerName, '/');
		//controller列表
		if (!in_array($controllerName, static::$app['controller'], TRUE)) {
			if (NULL === $requestId) {
				header(static::getHttpStatus('404'));
				exit;
			} else {
				static::$httpResponse[$requestId]->status(404);
				return;
			}
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
		$className = '\\' . static::$app['appNamespace'] . '\\controller\\' . ucfirst($className);
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
			if (NULL === $requestId) {
				header(static::getHttpStatus('404'));
				exit;
			} else {
				static::$httpResponse[$requestId]->status(404);
				return;
			}
		}
		static::$controller = $controller;
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
			if (isset(static::$app['class'][$className])) {
				$fileName = str_replace('@app/', static::$appDir, static::$app['class'][$className]);
			} elseif (is_string(static::$app['appNamespace']) && strpos($className, static::$app['appNamespace'] . '\\') === 0) {
				//namespace匹配
				$fileName = substr($className, strlen(static::$app['appNamespace']) + 1) . '.php';
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
		//Handle
		if (method_exists(static::$controller, '_handle_url') && !empty($router)) {
			$handle = call_user_func([static::$controller, '_handle_url'], $router, $anchor, $param, $ext);
			if (is_string($handle)) {
				return $handle;
			}
		}
		//基本URL
		$url = ($_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		if (empty($router)) {
			return $url . static::$sitePath;
		}
		unset($param[0]);
		//多级路由支持
		$last = strrpos($router, '/');
		$controllerName = substr($router, 0, $last);
		$actionName = substr($router, $last + 1);
		//是否启用了Rewrite
		if (static::$app['rewrite'] && isset(static::$app['rewriteRule'][$router])) {
			$url .= str_replace('@root/', static::$sitePath, static::$app['rewriteRule'][$router]);
			foreach ($param as $k => $v) {
				$k_tpl = '{{' . $k . '}}';
				if (strpos($url, $k_tpl) === FALSE) {
					continue;
				}
				$url = str_replace($k_tpl, $v, $url);
				//去掉此参数，防止后面http_build_query重复
				unset($param[$k]);
			}
		} elseif (static::$app['rewrite']) {
			if ($ext === NULL && empty(static::$app['rewriteExt'])) {
				$url .= static::$sitePath . $controllerName . '/' . $actionName;
			} else {
				$url .= static::$sitePath . $controllerName . '/' . $actionName . '.' . ($ext === NULL ? static::$app['rewriteExt'] : $ext);
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
	public static function setMimeType($type, $requestId = NULL) {
		$mimeType = static::getMimeType($type);
		if ($mimeType === NULL) {
			$mimeType = $type;
		}
		$header = $mimeType . ';';
		if (in_array($type, ['js', 'json', 'atom', 'rss', 'xhtml'], TRUE) || substr($mimeType, 0, 5) === 'text/') {
			$header .= ' charset=' . static::$app['charset'];
		}
		if (NULL === $requestId) {
			@header('Content-type:' . $header);
		} else {
			static::$httpResponse[$requestId]->header('Content-Type', $header);
		}
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
			$_param['_csrf_token'] = \sy\lib\YSecurity::csrfGetHash();
		}
		if (is_array($_param)) {
			unset($_param['__tpl'], $_param['__viewPath'], $_param['_GET'], $_param['_POST'], $_param['_REQUEST'], $_param['_ENV'], $_param['_COOKIE'], $_param['_SESSION'], $_param['GLOBALS'], $_param['argc'], $_param['argv'], $_param['_SERVER']);
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
		$modelClass = '\\' . static::$app['appNamespace'] . '\\model\\' . ucfirst($modelName);
		if (!class_exists($modelClass)) {
			$fileName = static::$appDir . 'models/' . lcfirst($modelName) . '.php';
			if (!is_file($fileName)) {
				throw new SYException('Model ' . $fileName . ' not exists', '10010');
			}
			require ($fileName);
		}
		return $modelClass::i();
	}
	/**
	 * 日志记录函数
	 * 仅用于HttpServer模式，且为异步写入
	 * 请注意：日志文件将保持打开状态，请勿直接删除日志文件
	 * @access public
	 * @param string $message
	 */
	public function writeLog($message) {
		
	}
}
