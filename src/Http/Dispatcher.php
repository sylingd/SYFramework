<?php
/**
 * 请求分发类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy\Http;

use Sy\App;
use Sy\DI\Container;
use Sy\DI\EntryUtil;
use Sy\Exception\NotFoundException;

class Dispatcher {
	const ROUTE_VALID = 0;
	const ROUTE_ERR_MODULE = 1;
	const ROUTE_ERR_CONTROLLER = 2;
	const ROUTE_ERR_ACTION = 3;

	/** @var array $modules Avaliable modules */
	private static $modules;

	public static function init() {
		self::$modules = App::$config->get('modules');
		if (is_string(self::$modules)) {
			self::$modules = explode(',', self::$modules);
		}
	}
	/**
	 * 判断路由是否合法
	 * @codeCoverageIgnore
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return int
	 */
	public static function isValid($module, $controller, $action) {
		if (!in_array($module, self::$modules, true)) {
			return self::ROUTE_ERR_MODULE;
		}
		$className = EntryUtil::controller($module, $controller);
		if (!Container::getInstance()->has($className)) {
			return self::ROUTE_ERR_CONTROLLER;
		}
		$clazz = Container::getInstance()->get($className);
		if (!method_exists($clazz, $action . 'Action')) {
			return self::ROUTE_ERR_ACTION;
		}
		return self::ROUTE_VALID;
	}
	/**
	 * Handle http request
	 * 
	 * @access public
	 */
	public static function handleRequest() {
		$request = new Request();
		Router::parse($request);
		self::dispatch($request);
	}
	/**
	 * 进行路由分发
	 * 
	 * @access public
	 * @param object $request
	 * @return mixed
	 */
	public function dispatch(Request $request) {
		$module = ucfirst($request->module);
		$controller = ucfirst($request->controller);
		$action = ucfirst($request->action);
		if (!empty($request->extension)) {
			$response->mimeType($request->extension);
		}
		try {
			$code = self::isValid($module, $controller, $action);
			if ($code === self::ROUTE_VALID) {
				$className = EntryUtil::controller($module, $controller);
				$clazz = Container::getInstance()->get($className);
				$actionName = $action . 'Action';
				$result = $clazz->$actionName($request, $response);
				//触发afterDispatch事件
				Plugin::trigger('afterDispatch', [$request, $response, $result]);
			} else {
				// Not found
				self::handleNotFound($request, $response);
			}
		} catch (\Throwable $e) {
			$result = self::handleDispathException($request, $response, $e);
		}
		$request->end();
		$response->end();
		unset($request, $response);
		return $result;
	}
	private static function handleNotFound($request, $response) {
		$arr = [$request, $yesf_response];
		if (Plugin::trigger('dispatchFailed', $arr) === null) {
			$response->status(404);
			$response->disableView();
			$response->setCurrentTemplateEngine(Template::class);
			if (Yesf::app()->getEnvironment() === 'develop') {
				$response->assign('module', $request->module);
				$response->assign('controller', $request->controller);
				$response->assign('action', $request->action);
				$response->assign('code', $code);
				$response->assign('request', $request);
				$response->display(SY_PATH . 'Data/error_404_debug.php', true);
			} else {
				$response->display(SY_PATH . 'Data/error_404.php', true);
			}
		}
	}
	private static function handleDispathException($request, $response, $exception) {
		//日志记录
		Logger::error('Uncaught exception: ' . $e->getMessage() . '. Trace: ' . $e->getTraceAsString());
		//触发失败事件
		$arr = [$request, $response, $exception];
		if (Plugin::trigger('dispatchFailed', $arr) === null) {
			//如果用户没有自行处理，输出默认模板
			$response->disableView();
			$response->setCurrentTemplateEngine(Template::class);
			if (Yesf::app()->getEnvironment() === 'develop') {
				$response->assign('module', $request->module);
				$response->assign('controller', $request->controller);
				$response->assign('action', $request->action);
				$response->assign('exception', $exception);
				$response->assign('request', $request);
				$response->display(SY_PATH . 'Data/error_debug.php', true);
			} else {
				$response->display(SY_PATH . 'Data/error.php', true);
			}
		}
	}
}