<?php

/**
 * 主操作类
 * 涉及到Swoole的主要操作均通过此类进行
 * Swoole版本需求：1.8.6+
 * 仅当运行于HttpServer模式时支持
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Swoole
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015-2016 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\swoole;

use \Sy;

class Server {
	/**
	 * 获取Swoole版本
	 * @access public
	 * @return string
	 */
	public static function getVersion() {
		return \SWOOLE_VERSION;
	}
	/**
	 * 推送任务到SwooleTask进程
	 */
	public static function addTask($task, $callback = NULL) {
		if (!($task instanceof \sy\swoole\task)) {
			return FALSE;
		}
		$param = [$task];
		$param[] = -1;
		if (NULL !== $callback && is_callable($callback)) {
			$param[] = $callback;
		}
		call_user_func_array([Sy::$httpServer, 'task'], $param);
	}
	/**
	 * 添加task响应函数
	 * 建议在workerStart时添加
	 * 如果已存在相同type的响应函数，则会被替换
	 * @access public
	 * @param string $type Task类型
	 * @param callable $callback
	 */
	public static function addTaskHandle(string $type, callable $callback) {
		\sy\swoole\serverEventHandle::$taskHandle[$type] = $callback;
	}
}