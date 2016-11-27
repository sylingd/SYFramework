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
use \sy\base\SYException;

class Server {
	//事件回调
	public static $taskHandle = [];
	public static $eventHandle = [];
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
	public static function addTask(array $task, $callback = NULL) {
		if (!isset($task['type'])) {
			throw new SYException('Unknow task type', '10061');
		}
		$param = [$task];
		$param[] = -1;
		if (NULL !== $callback && is_callable($callback)) {
			$param[] = $callback;
		}
		call_user_func_array([Sy::$swServer, 'task'], $param);
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
		static::$taskHandle[$type] = $callback;
	}
	/**
	 * 获取config.php中配置的端口
	 * @access public
	 * @param string $name
	 * @return int
	 */
	public static function getPort($name) {
		return Sy::$app->get('swoole.' . $name . '.port');
	}
	/**
	 * 添加服务（也就是一个端口监听）
	 * 注意：若服务类型为RPC，则会自动绑定Web事件
	 * @access public
	 * @param string $type 服务类型，可选：RPC，TCP，UDP，WS
	 * @param mixed $config 配置，传入String则从config读取，也可以直接传入Array
	 */
	public static function addListener(string $type, $config) {
		if (is_string($config)) {
			$config = Sy::$app->get('swoole.' . $config);
		}
		if (!isset($config['port'])) {
			throw new SYException('Invalid config', '10062');
		}
		if ('RPC' === $type) {
			//处理高级选项
			$httpConfig = $config['http'];
			if (!is_array($httpConfig['response_header'])) {
				$httpConfig['response_header'] = [];
			}
			if (!isset($httpConfig['response_header']['Content_Type'])) {
				$httpConfig['response_header']['Content_Type'] = 'application/json; charset=' . Sy::$app->get('charset');
			}
			Sy::$swServer->set($httpConfig);
			$tcpConfig = $config['tcp']['advanced'];
			$tcpIp = isset($config['ip']) ? $config['ip'] : Sy::$app->get('swoole.ip');
			$tcpPort = $config['tcp']['port'];
			Sy::$swService[$tcpPort] = Sy::$swServer->listen($tcpIp, $config['tcp']['port'], \SWOOLE_TCP);
			Sy::$swService[$tcpPort]->set($tcpConfig);
			Sy::$swService[$tcpPort]->on('Receive', ['\sy\swoole\RpcServer', 'eventTcpReceive']);
			if (!Sy::$swServerInited) {
				Sy::$swServer->on('Task', ['\sy\swoole\RpcServer', 'eventTask']);
				Sy::$swServer->on('Request', ['\sy\swoole\RpcServer', 'eventRequest']);
				Sy::$swServerInited = TRUE;
			}
		} elseif ('TCP' === $type) {
			$tcpIp = isset($config['ip']) ? $config['ip'] : Sy::$app->get('swoole.ip');
			$tcpPort = $config['port'];
			Sy::$swService[$tcpPort] = Sy::$swServer->listen($tcpIp, $tcpPort, \SWOOLE_TCP);
			if (isset($config['advanced'])) {
				Sy::$swService[$tcpPort]->set($config['advanced']);
			}
			Sy::$swService[$tcpPort]->on('Receive', ['\sy\swoole\TcpServer', 'eventReceive']);
			Sy::$swService[$tcpPort]->on('Connect', ['\sy\swoole\TcpServer', 'eventConnect']);
			Sy::$swService[$tcpPort]->on('Close', ['\sy\swoole\TcpServer', 'eventClose']);
		} elseif ('UDP' === $type) {
			$udpIp = isset($config['ip']) ? $config['ip'] : Sy::$app->get('swoole.ip');
			$udpPort = $config['port'];
			Sy::$swService[$udpPort] = Sy::$swServer->listen($udpIp, $udpPort, \SWOOLE_UDP);
			if (isset($config['advanced'])) {
				Sy::$swService[$udpPort]->set($config['advanced']);
			}
			Sy::$swService[$udpPort]->on('Receive', ['\sy\swoole\UdpServer', 'eventReceive']);
			Sy::$swService[$udpPort]->on('Packet', ['\sy\swoole\UdpServer', 'eventPacket']);
		}/* elseif ('WS' === $type) {
			$tcpIp = isset($config['ip']) ? $config['ip'] : Sy::$app->get('swoole.ip');
			$tcpPort = $config['port'];
			Sy::$swService[$tcpPort] = Sy::$swServer->addListener($tcpIp, $tcpPort, \SWOOLE_TCP);
			Sy::$swService[$tcpPort]->on('Receive', ['\sy\swoole\WsServer', 'eventReceive']);
		}*/
	}
	/**
	 * 添加事件响应函数
	 * @access public
	 * @param string $event
	 * @param int $port 当事件为WorkerStart时传入0
	 * @param callable $callback
	 */
	public static function addEventHandle(string $event, int $port, callable $callback) {
		if (!isset(static::$eventHandle[$port])) {
			static::$eventHandle[$port] = [];
		}
		static::$eventHandle[$port][$event] = $callback;
	}
	public static function triggerEventHandle(string $event, int $port, array $param) {
		if (isset(static::$eventHandle[$port][$event]) && is_callable(static::$eventHandle[$port][$event])) {
			call_user_func_array(Server::$eventHandle[$port][$event], $param);
		}
	}
	/**
	 * 开始执行服务
	 * @access public
	 */
	public static function start() {
		if (!Sy::$swServerInited) {
			$config = Sy::$app->get('swoole.http.advanced');
			$ssl = Sy::$app->get('swoole.http.ssl');
			if ($ssl['enable']) {
				$config['ssl_cert_file'] = $ssl['cert'];
				$config['ssl_key_file'] = $ssl['key'];
			}
			if (Sy::$app->get('swoole.http.http2')) {
				if (!isset($config['ssl_cert_file'])) {
					throw new SYException('Certfile not found', '10006');
				}
				$config['open_http2_protocol'] = TRUE;
			}
			if (!is_array($config['response_header'])) {
				$config['response_header'] = [];
			}
			if (!isset($config['response_header']['Content_Type'])) {
				$config['response_header']['Content_Type'] = 'application/html; charset=' . Sy::$app->get('charset');
			}
			Sy::$swServer->set($config);
			Sy::$swServer->on('Request', ['\sy\swoole\HttpServer', 'eventRequest']);
			Sy::$swServer->on('Task', ['\sy\swoole\Server', 'eventTask']);
			Sy::$swServerInited = TRUE;
		}
		Sy::$swServer->start();
	}
	
	
	
	/**
	 * 普通事件：启动Master进程
	 * @access public
	 * @param object $serv
	 */
	public static function eventStart($serv) {
		swoole_set_process_name('SY ' . Sy::$app->get('=.ip') . ' master');
		$pidPath = rtrim(Sy::$app->get('swoole.pidPath'), '/') . '/';
		file_put_contents($pidPath . Sy::$app->get('name') . '_master.pid', $serv->master_pid);
		file_put_contents($pidPath . Sy::$app->get('name') . '_manager.pid', $serv->manager_pid);
	}
	/**
	 * 普通事件：启动Manager进程
	 * @access public
	 * @param object $serv
	 */
	public static function eventManagerStart($serv) {
		swoole_set_process_name('SY ' . Sy::$app->get('name') . ' manager');
	}
	/**
	 * 普通事件：启动一个进程
	 * @access public
	 * @param object $serv
	 * @param int $worker_id
	 */
	public static function eventWorkerStart($serv, $worker_id) {
		//根据类型，设置不同的进程名
		if ($serv->taskworker) {
			swoole_set_process_name(Sy::$app->get('name') . ' task ' . $worker_id . ' (SY)');
		} else {
			swoole_set_process_name(Sy::$app->get('name') . ' worker ' . $worker_id . ' (SY)');
		}
		//回调
		static::triggerEventHandle('WorkerStart', 0, [$serv->taskworker, $worker_id]);
	}
	/**
	 * 普通事件：进程出错
	 * @access public
	 * @param object $serv
	 * @param int $worker_id
	 * @param int $worker_pid
	 * @param int $exit_code
	 */
	public static function eventWorkerError($serv, $worker_id, $worker_pid, $exit_code) {
	}
	/**
	 * 普通事件：接收到task
	 * @access public
	 * @param object $serv
	 * @param int $worker_id
	 * @param int $worker_pid
	 * @param int $exit_code
	 */
	public static function eventTask($serv, $task_id, $from_id, $task) {
		$type = $task['type'];
		if (!isset(static::$taskHandle[$type])) {
			return '';
		}
		$swooleData = ['task_id' => $task_id, 'from_id' => $from_id];
		try {
			$result = call_user_func(static::$taskHandle[$type], $swooleData, $task);
		} catch (\Exception $e) {
			$result = '';
		}
		return is_string($result) ? $result : strval($result);
	}
	public static function eventFinish($serv, int $task_id, string $data) {
		
	}
}