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

final class HttpServer extends Server {
	//HTTP事件：收到请求
	public static function eventRequest($req, $response) {
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
		if (Sy::$debug && function_exists('xdebug_start_trace')) {
			xdebug_start_trace();
		}
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
				$url = parse_url('http://www.sylingd.com/' . Sy::$httpRequest[$requestId]->server['request_uri']);
				$route = ltrim(preg_replace('/\.(\w+)$/', '', $url['path']), '/'); //去掉末尾的扩展名和开头的“/”符号
				empty($route) && $route = NULL;
			}
			Sy::router($route, $requestId);
		} else {
			$route = empty(Sy::$httpRequest[$requestId]->get[Sy::$routeParam]) ? Sy::$httpRequest[$requestId]->get[Sy::$routeParam] : NULL;
			Sy::router($route, $requestId);
		}
		if (Sy::$debug && function_exists('xdebug_stop_trace')) {
			xdebug_stop_trace();
		}
		//请求结束，进行清理工作
		try {
			Sy::$httpResponse[$requestId]->end();
		} catch (\Exception $e) {
		} finally {
			unset(Sy::$httpRequest[$requestId], Sy::$httpResponse[$requestId], $requestId);
		}
		return;
	}
}