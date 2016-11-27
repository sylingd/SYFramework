<?php

/**
 * 分层类
 * 因为设计思路是提倡自己编写SQL，因此本框架只做简单分层
 * 实际上并不会因为分层不同而有不同待遇
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

trait Stratified {
	/**
	 * 获取DAO操作类
	 * @access public
	 * @param string $name
	 * @return object
	 */
	public static function DAO($name) {
		//名称
		$className = '\\' . static::$app['appNamespace'] . '\\DAO\\' . ucfirst($name);
		if (!class_exists($className)) {
			$fileName = static::$appDir . 'DAO/' . lcfirst($name) . '.php';
			if (!is_file($fileName)) {
				throw new SYException('DAO ' . $fileName . ' not exists', '10011');
			}
			require ($fileName);
		}
		return $className::i();
	}
	/**
	 * 获取Service操作类
	 * @access public
	 * @param string $name
	 * @return object
	 */
	public static function service($name) {
		//名称
		$className = '\\' . static::$app['appNamespace'] . '\\service\\' . ucfirst($name);
		if (!class_exists($className)) {
			$fileName = static::$appDir . 'services/' . lcfirst($name) . '.php';
			if (!is_file($fileName)) {
				throw new SYException('Service ' . $fileName . ' not exists', '10010');
			}
			require ($fileName);
		}
		return $className::i();
	}
	/**
	 * 获取Model操作类
	 * @access public
	 * @param string $name
	 * @return object
	 */
	public static function model($name) {
		//名称
		$className = '\\' . static::$app['appNamespace'] . '\\models\\' . ucfirst($name);
		if (!class_exists($className)) {
			$fileName = static::$appDir . 'models/' . lcfirst($name) . '.php';
			if (!is_file($fileName)) {
				throw new SYException('Model  ' . $fileName . ' not exists', '10010');
			}
			require ($fileName);
		}
		return $className::i();
	}
	/**
	 * 获取Controller操作类
	 * @access public
	 * @param string $controllerName
	 * @return object
	 */
	public static function controller($controllerName) {
		//多级路由支持
		$isPath = strpos($controllerName, '/');
		//controller列表
		if (!in_array($controllerName, static::$app['controller'], TRUE)) {
			return NULL;
		}
		//多级路由时，引入顶级类（如果存在）
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
			throw new SYException('Controller ' . $controllerName . ' not exists', '10012');
		}
		if (!class_exists($className, FALSE)) {
			require($fileName);
		}
		$controller = new $className;
		return $controller;
	}
}