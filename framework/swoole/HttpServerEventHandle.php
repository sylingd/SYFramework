<?php

/**
 * 事件响应类
 * 仅当运行于HttpServer模式时支持
 * 此类不可继承
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

final class HttpServerEventHandle {
	//事件回调
	public static $taskHandle = [];
	//HTTP事件：收到请求
	public static function onRequest($req, $response) {
		//生成唯一请求ID
		$remoteIp = ($req->server['remote_addr'] === '127.0.0.1' && isset($req->server['http_x_forwarded_for'])) ? $req->server['http_x_forwarded_for'] : $req->server['remote_addr']; //获取真实IP
		$requestId = md5(uniqid($remoteIp, TRUE));
		//设置请求信息
		Sy::$httpRequest[$requestId] = $req;
		Sy::$httpResponse[$requestId] = $response;
		//是否启用CSRF验证
		if (isset(Sy::$app['csrf']) && Sy::$app['csrf']) {
			\sy\lib\YSecurity::csrfSetCookie($requestId);
		}
		//根据设置，分配重写规则
		ob_start();
		if (Sy::$app['rewrite']) {
			//自定义规则
			if (is_array(Sy::$app['rewriteParseRule'])) {
				$matches = NULL;
				foreach (Sy::$app['rewriteParseRule'] as $oneRule) {
					if (preg_match($oneRule[0], Sy::$httpRequest[$requestId]->server['request_uri'], $matches)) {
						$route = $oneRule[1];
						$paramName = array_slice($oneRule, 2);
						$param = [];
						foreach ($paramName as $k => $v) {
							$param[$v] = isset($matches[$k + 1]) ? $matches[$k + 1] : '';
						}
						//写入相关的环境变量
						//合并至GET参数
						Sy::$httpRequest[$requestId]->get = array_merge($param, (array)Sy::$httpRequest[$requestId]->get);
						Sy::$httpRequest[$requestId]->server['query_string'] = http_build_query(Sy::$httpRequest[$requestId]->get);
						Sy::$httpRequest[$requestId]->server['request_uri'] = Sy::$httpRequest[$requestId]->server['php_self'] . '?' . Sy::$httpRequest[$requestId]->server['query_string'];
						break;
					}
				}
			}
			//没有匹配的重写规则
			if (!isset($route)) {
				$url = parse_url(Sy::$httpRequest[$requestId]->server['request_uri']);
				$route = ltrim(preg_replace('/\.(\w+)$/', '', $url['path']), '/'); //去掉末尾的扩展名和开头的“/”符号
				empty($route) && $route = NULL;
			}
			Sy::router($route, $requestId);
		} else {
			$route = empty(Sy::$httpRequest[$requestId]->get[Sy::$routeParam]) ? Sy::$httpRequest[$requestId]->get[Sy::$routeParam] : NULL;
			Sy::router($route, $requestId);
		}
		//请求结束，进行清理工作
		try {
			$output = ob_get_clean();
			if (empty($output)) {
				Sy::$httpResponse[$requestId]->end();
			} else {
				Sy::$httpResponse[$requestId]->end(ob_get_clean());
			}
			unset(Sy::$httpRequest[$requestId], Sy::$httpResponse[$requestId], $requestId);
		} catch (\Exception $e) {
		}
		return;
	}
	//普通事件：启动master进程
	public static function onStart($serv) {
		swoole_set_process_name('SY: ' . Sy::$app['name'] . ' master');
        $pidPath = rtrim(Sy::$app['httpServer']['advanced'], '/') . '/';
        file_put_contents($pidPath . Sy::$app['name'] . '_master.pid', $serv->master_pid);
        file_put_contents($pidPath . Sy::$app['name'] . '_manager.pid', $serv->manager_pid);
	}
	//普通事件：启动manager进程
    public static function onManagerStart($serv) {
        swoole_set_process_name('SY: ' . Sy::$app['name'] . ' manager');
    }
	//普通事件：启动一个进程
	public static function onWorkerStart($serv, $worker_id) {
		//根据类型，设置不同的进程名
		if ($serv->taskworker) {
			swoole_set_process_name('SY: ' . Sy::$app['name'] . ' task');
		} else {
			swoole_set_process_name('SY: ' . Sy::$app['name'] . ' worker');
		}
		if (isset(Sy::$app['httpServer']['event']['workerStart']) && is_callable(Sy::$app['httpServer']['event']['workerStart'])) {
			call_user_func(Sy::$app['httpServer']['event']['workerStart'], $serv, $worker_id);
		}
	}
	//普通事件：接收到task
	public static function onTask($serv, $task_id, $from_id, $taskObj) {
		$type = $taskObj->getType();
		if (!isset(self::$taskHandle[$type])) {
			return '';
		}
		$data = $taskObj->getData();
		$swooleData = ['serv' => $serv, 'task_id' => $task_id, 'from_id' => $from_id];
		$result = call_user_func(self::$taskHandle[$type], $swooleData, $data);
		return is_string($result) ? $result : strval($result);
	}
}