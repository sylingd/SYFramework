<?php

/**
 * UDP支持类
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
use \sy\base\SYException;

class UdpServer {
	public static function eventReceive($server, int $fd, int $from_id, string $data) {
		if (Sy::$debug && function_exists('xdebug_start_trace')) {
			xdebug_start_trace();
		}
		$info = $server->connection_info($fd);
		$port = $info['server_port'];
		if (isset(Server::$eventHandle['Receive'][$port]) && is_callable(Server::$eventHandle['Receive'][$port])) {
			call_user_func(Server::$eventHandle['Receive'][$port], $serv, $fd, $from_id, $data);
		}
		if (Sy::$debug && function_exists('xdebug_stop_trace')) {
			xdebug_stop_trace();
		}
	}
	public static function eventPacket($server, string $data, array $client_info) {
		$info = $server->connection_info($fd);
		$port = $info['server_port'];
		if (isset(Server::$eventHandle['Packet'][$port]) && is_callable(Server::$eventHandle['Packet'][$port])) {
			call_user_func(Server::$eventHandle['Packet'][$port], $data, $client_info);
		}
	}
}