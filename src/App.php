<?php
/**
 * 基本类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link https://www.sylibs.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy;

use Sy\Router;
use Sy\Plugin;
use Sy\DI\Container;
use Sy\Exception\Exception;
use Sy\Exception\StartException;
use Sy\Utils\Security;
use Sy\Config\ConfigInterface;
use Sy\Config\Adapter\Arr;

if (!defined('SY_PATH')) {
	define('SY_PATH', __DIR__ . '/');
}

class App {
	//会从data下的相应文件读取
	public static $mimeTypes = NULL;
	public static $httpStatus = NULL;
	//调试模式
	public static $debug = TRUE;
	//CLI模式
	public static $isCli = FALSE;
	//应用相关设置
	public static $config;
	public static $sitePath;
	//应用namespace
	public static $cfgNamespace = NULL;
	/**
	 * 初始化：创建Application（通用）
	 * @access protected
	 * @param object $config设置
	 */
	protected static function createInit($config) {
		//路径相关
		self::$siteDir = $siteDir . '/';
		//PHP运行模式
		if (PHP_SAPI === 'cli') {
			self::$isCli = TRUE;
		}
		if (!defined('APP_PATH')) {
			throw new StartException('You must define APP_PATH');
		}
		if (is_string($config) && is_file($config)) {
			$config = Arr::fromIniFile($config);
		}
		if (is_array($config)) {
			$config = new Arr($config);
		}
		if (!is_object($config) || !($config instanceof ConfigInterface)) {
			throw new StartException('Config can not be recognised');
		}
		//基本信息
		self::$config = $config;
		if ($config->get('debug')) {
			self::$debug = $config->get('debug');
		}
		//编码相关
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding($config->get('charset'));
		}
		//设置一些基本参数
		self::$cfgNamespace = $config->get('namespace');
		Dispatcher::init();
		//Configuration
		self::callConfiguration();
	}
	/**
	 * 初始化：创建WebApplication
	 * @access public
	 * @param object $config设置
	 */
	public static function create($config) {
		self::createInit($config);
		//网站目录
		$now = $_SERVER['PHP_SELF'];
		$dir = str_replace('\\', '/', dirname($now));
		$dir !== '/' && $dir = rtrim($dir, '/') . '/';
		self::$sitePath = $dir;
		//单元测试
		if (defined('SY_UNIT')) {
			return;
		}
		//是否启用CSRF验证
		if (self::$config->get('csrf')) {
			Security::csrfSetCookie();
		}
		//调试模式
		if (self::$debug && function_exists('xdebug_start_trace')) {
			xdebug_start_trace();
		}
		//开始路由分发
		Dispatcher::handleRequest();
		if (self::$debug && function_exists('xdebug_stop_trace')) {
			xdebug_stop_trace();
		}
	}
	/**
	 * 初始化：创建ConsoleApplication
	 * @access public
	 * @param object $config设置
	 */
	public static function createConsole($config = NULL) {
		self::createInit($config);
		//网站目录
		self::$sitePath = '/';
		if (!self::$isCli) {
			throw new SYException('Must run at CLI mode', '10005');
		}
		//仅支持参数方式运行
		$opt = getopt(self::$routeParam . ':');
		//以参数方式运行
		$run = $opt[self::$routeParam];
		if (!empty($run) && self::$config->has('console.' . $run)) {
			list($fileName, $callback) = self::$config->get('console.' . $run);
		} else {
			list($fileName, $callback) = self::$config->get('console.default');
		}
		require(APP_PATH . '/workers/' . $fileName);
		if (is_callable($callback)) {
			call_user_func($callback);
		}
	}
	/**
	 * Configuration
	 * 
	 * @access private
	 */
	private static function callConfiguration() {
		$container = Container::getInstance();
		$className = self::$cfgNamespace . 'Configuration';
		if ($container->has($className)) {
			$clazz = $container->get($className);
			$methods = (new ReflectionClass($clazz))->getMethods();
			foreach ($methods as $method) {
				if (strpos($method->name, 'set') !== 0) {
					continue;
				}
				$params = $method->getParameters();
				$init_params = [];
				foreach ($params as $param) {
					$type = $param->getType();
					if (class_exists('ReflectionNamedType') && $type instanceof \ReflectionNamedType) {
						$typeName = $type->getName();
					} else {
						$typeName = $type->__toString();
					}
					if ($type->isBuiltin()) {
						$value = null;
						settype($value, $typeName);
						$init_params[] = $value;
					} else {
						$init_params[] = $container->get($typeName);
					}
				}
				$method->invokeArgs($clazz, $init_params);
			}
		}
	}
	public static function getEnv() {
		return self::$debug ? 'develop' : 'product';
	}
}
