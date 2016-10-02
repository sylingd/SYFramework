<?php

/**
 * WebSocket支持类
 * WebSocket有几个特殊事件
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

final class WsServer extends Server {
	
    const OPCODE_CONTINUATION_FRAME = 0x0;
    const OPCODE_TEXT_FRAME         = 0x1;
    const OPCODE_BINARY_FRAME       = 0x2;
    const OPCODE_CONNECTION_CLOSE   = 0x8;
    const OPCODE_PING               = 0x9;
    const OPCODE_PONG               = 0xa;

    const CLOSE_NORMAL              = 1000;
    const CLOSE_GOING_AWAY          = 1001;
    const CLOSE_PROTOCOL_ERROR      = 1002;
    const CLOSE_DATA_ERROR          = 1003;
    const CLOSE_STATUS_ERROR        = 1005;
    const CLOSE_ABNORMAL            = 1006;
    const CLOSE_MESSAGE_ERROR       = 1007;
    const CLOSE_POLICY_ERROR        = 1008;
    const CLOSE_MESSAGE_TOO_BIG     = 1009;
    const CLOSE_EXTENSION_MISSING   = 1010;
    const CLOSE_SERVER_ERROR        = 1011;
    const CLOSE_TLS                 = 1015;

    const WEBSOCKET_VERSION         = 13;
	
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    public $frame_list = [];
    public $connections = [];
    public $max_connect = 10000;
    public $max_frame_size = 2097152; //数据包最大长度，超过此长度会被认为是非法请求
    public $heart_time = 600; //600s life time
	
	public $keepalive = TRUE;
	
	//收到请求
	public static function eventReceive($req, $response) {
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
		} catch (\Exception $e) {
		} final {
			unset(Sy::$httpRequest[$requestId], Sy::$httpResponse[$requestId], $requestId);
		}
		return;
	}
}