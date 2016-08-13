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

final class RpcServerEventHandle {
	//Task信息
	private static $taskInfo;
	//事件回调
	public static $taskHandle = [];
	//HTTP事件：收到请求
	public static function onRequest($req, $response) {
		//JSON格式
		foreach ($this->httpConfig['response_header'] as $k => $v) {
			$response->header($k, $v);
		}
		//状态必定为200
		$response->status(200);

		//检查参数
		if (!isset($request->post['params'])) {
			$response->end(json_encode(DoraDoraPacket::packFormat('Parameter was not set or wrong', 100003)));
			return;
		}
		$params = $request->post;
		$params = json_decode($params['params'], true);
		if (!isset($params['guid']) || !isset($params['api']) || count($params['api']) == 0) {
			$response->end(json_encode(DoraDoraPacket::packFormat('Parameter was not set or wrong', 100003)));
			return;
		}
		//Task基本参数
		$task = array(
			'guid' => $params['guid'],
			'fd' => $request->fd,
			'protocol' => 'http',
		);

		$url = trim($request->server['request_uri'], "\r\n/ ");

		switch ($url) {
			case 'api/multisync':
				$task['type'] = DoraConst::SW_MODE_WAITRESULT_MULTI;
				foreach ($params['api'] as $k => $v) {
					$task['api'] = $params['api'][$k];
					$taskid = Sy::$swServer->task($task, -1, function ($serv, $task_id, $data) use ($response) {
						self::_httpFinished($serv, $task_id, $data, $response);
					});
					self::taskInfo[$task['fd']][$task['guid']]['taskkey'][$taskid] = $k;
				}
				break;
			case 'api/multinoresult':
				$task['type'] = DoraConst::SW_MODE_NORESULT_MULTI;

				foreach ($params['api'] as $k => $v) {
					$task['api'] = $params['api'][$k];
					Sy::$swServer->task($task);
				}
				$pack = DoraDoraPacket::packFormat('transfer success.已经成功投递', 100001);
				$pack['guid'] = $task['guid'];
				$response->end(json_encode($pack));

				break;
			//TODO: 重写控制台部分
			case 'server/cmd':
			/*
				$task['type'] = DoraConst::SW_CONTROL_CMD;

				if ($params['api']['cmd']['name'] == 'getStat') {
					$pack = DoraDoraPacket::packFormat('OK', 0, array('server' => $this->server->stats()));
					$pack['guid'] = $task['guid'];
					$response->end(json_encode($pack));
					return;
				}
				if ($params['api']['cmd']['name'] == 'reloadTask') {
					$pack = DoraDoraPacket::packFormat('OK', 0, array('server' => $this->server->stats()));
					$this->server->reload(true);
					$pack['guid'] = $task['guid'];
					$response->end(json_encode($pack));
					return;
				}
				break;
			*/
			default:
				$response->end(json_encode(DoraDoraPacket::packFormat('unknow task type.未知类型任务', 100002)));
				unset(self::taskInfo[$task['fd']]);
				return;
		}
	}
	//HTTP请求结束
	public static function _httpFinished($serv, $task_id, $data, $response) {
		$fd = $data['fd'];
		$guid = $data['guid'];
		//if the guid not exists .it's mean the api no need return result
		if (!isset(self::taskInfo[$fd][$guid])) {
			return true;
		}
		//get the api key
		$key = self::taskInfo[$fd][$guid]['taskkey'][$task_id];
		//save the result
		self::taskInfo[$fd][$guid]['result'][$key] = $data['result'];
		//remove the used taskid
		unset(self::taskInfo[$fd][$guid]['taskkey'][$task_id]);
		switch ($data['type']) {
			case DoraConst::SW_MODE_WAITRESULT_MULTI:
				//all task finished
				if (count(self::taskInfo[$fd][$guid]['taskkey']) == 0) {
					$packet = DoraPacket::packFormat('OK', 0, self::taskInfo[$fd][$guid]['result']);
					$packet['guid'] = $guid;
					$packet = DoraPacket::packEncode($packet, $data['protocol']);
					unset(self::taskInfo[$fd][$guid]);
					$response->end($packet);
					return true;
				} else {
					//multi call task
					//not finished
					//waiting other result
					return true;
				}
				break;
			default:
				return true;
				break;
		}
	}
	//TCP事件：收到请求
	public static function onTcpReceive($serv, $fd, $from_id, $data) {
		$requestInfo = Packet::packDecode($data);

		#decode error
		if ($requestInfo["code"] != 0) {
			$pack["guid"] = $requestInfo["guid"];
			$req = Packet::packEncode($requestInfo);
			$serv->send($fd, $req);

			return true;
		} else {
			$requestInfo = $requestInfo["data"];
		}

		#api was not set will fail
		if (!is_array($requestInfo["api"]) && count($requestInfo["api"])) {
			$pack = Packet::packFormat("param api is empty", 100003);
			$pack["guid"] = $requestInfo["guid"];
			$pack = Packet::packEncode($pack);
			$serv->send($fd, $pack);

			return true;
		}
		$guid = $requestInfo["guid"];

		//prepare the task parameter
		$task = array(
			"type" => $requestInfo["type"],
			"guid" => $requestInfo["guid"],
			"fd" => $fd,
			"protocol" => "tcp",
		);

		//different task type process
		switch ($requestInfo["type"]) {

			case DoraConst::SW_MODE_WAITRESULT_SINGLE:
				$task["api"] = $requestInfo["api"]["one"];
				$taskid = $serv->task($task);

				//result with task key
				$this->taskInfo[$fd][$guid]["taskkey"][$taskid] = "one";

				return true;
				break;
			case DoraConst::SW_MODE_NORESULT_SINGLE:
				$task["api"] = $requestInfo["api"]["one"];
				$serv->task($task);

				//return success deploy
				$pack = Packet::packFormat("transfer success.已经成功投递", 100001);
				$pack["guid"] = $task["guid"];
				$pack = Packet::packEncode($pack);
				$serv->send($fd, $pack);

				return true;

				break;

			case DoraConst::SW_MODE_WAITRESULT_MULTI:
				foreach ($requestInfo["api"] as $k => $v) {
					$task["api"] = $requestInfo["api"][$k];
					$taskid = $serv->task($task);
					$this->taskInfo[$fd][$guid]["taskkey"][$taskid] = $k;
				}

				return true;
				break;
			case DoraConst::SW_MODE_NORESULT_MULTI:
				foreach ($requestInfo["api"] as $k => $v) {
					$task["api"] = $requestInfo["api"][$k];
					$serv->task($task);
				}

				$pack = Packet::packFormat("transfer success.已经成功投递", 100001);
				$pack["guid"] = $task["guid"];
				$pack = Packet::packEncode($pack);

				$serv->send($fd, $pack);

				return true;
				break;
			case DoraConst::SW_CONTROL_CMD:
				switch ($requestInfo["api"]["cmd"]["name"]) {
					case "getStat":
						$pack = Packet::packFormat("OK", 0, array("server" => $serv->stats()));
						$pack["guid"] = $task["guid"];
						$pack = Packet::packEncode($pack);
						$serv->send($fd, $pack);
						return true;

						break;
					case "reloadTask":
						$pack = Packet::packFormat("OK", 0, array("server" => $serv->stats()));
						$pack["guid"] = $task["guid"];
						$pack = Packet::packEncode($pack);
						$serv->send($fd, $pack);
						$serv->reload(true);
						return true;

						break;
					default:
						$pack = Packet::packFormat("unknow cmd", 100011);
						$pack = Packet::packEncode($pack);

						$serv->send($fd, $pack);
						unset($this->taskInfo[$fd]);
						break;
				}
				break;

			case DoraConst::SW_MODE_ASYNCRESULT_SINGLE:
				$task["api"] = $requestInfo["api"]["one"];
				$taskid = $serv->task($task);
				$this->taskInfo[$fd][$guid]["taskkey"][$taskid] = "one";

				//return success
				$pack = Packet::packFormat("transfer success.已经成功投递", 100001);
				$pack["guid"] = $task["guid"];
				$pack = Packet::packEncode($pack);
				$serv->send($fd, $pack);

				return true;
				break;
			case DoraConst::SW_MODE_ASYNCRESULT_MULTI:
				foreach ($requestInfo["api"] as $k => $v) {
					$task["api"] = $requestInfo["api"][$k];
					$taskid = $serv->task($task);
					$this->taskInfo[$fd][$guid]["taskkey"][$taskid] = $k;
				}

				//return success
				$pack = Packet::packFormat("transfer success.已经成功投递", 100001);
				$pack["guid"] = $task["guid"];
				$pack = Packet::packEncode($pack);

				$serv->send($fd, $pack);
				break;
			default:
				$pack = Packet::packFormat("unknow task type.未知类型任务", 100002);
				$pack = Packet::packEncode($pack);

				$serv->send($fd, $pack);
				//unset($this->taskInfo[$fd]);

				return true;
		}

		return true;
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
	//普通事件：进程出错
	public static function onWorkerError($serv, $worker_id, $worker_pid, $exit_code) {
	}
	//普通事件：Task结束
	public static function onFinish($serv, $task_id, $data) {
		$fd = $data["fd"];
		$guid = $data["guid"];

		//if the guid not exists .it's mean the api no need return result
		if (!isset($this->taskInfo[$fd][$guid])) {
			return true;
		}

		//get the api key
		$key = $this->taskInfo[$fd][$guid]["taskkey"][$task_id];

		//save the result
		$this->taskInfo[$fd][$guid]["result"][$key] = $data["result"];

		//remove the used taskid
		unset($this->taskInfo[$fd][$guid]["taskkey"][$task_id]);

		switch ($data["type"]) {

			case DoraConst::SW_MODE_WAITRESULT_SINGLE:
				$packet = DoraPacket::packFormat("OK", 0, $data["result"]);
				$packet["guid"] = $guid;
				$packet = DoraPacket::packEncode($packet, $data["protocol"]);

				$serv->send($fd, $packet);
				unset($this->taskInfo[$fd][$guid]);

				return true;
				break;

			case DoraConst::SW_MODE_WAITRESULT_MULTI:
				if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
					$packet = DoraPacket::packFormat("OK", 0, $this->taskInfo[$fd][$guid]["result"]);
					$packet["guid"] = $guid;
					$packet = DoraPacket::packEncode($packet, $data["protocol"]);
					$serv->send($fd, $packet);
					//$serv->close($fd);
					unset($this->taskInfo[$fd][$guid]);

					return true;
				} else {
					//multi call task
					//not finished
					//waiting other result
					return true;
				}
				break;

			case DoraConst::SW_MODE_ASYNCRESULT_SINGLE:
				$packet = DoraPacket::packFormat("OK", 0, $data["result"]);
				$packet["guid"] = $guid;
				//flag this is result
				$packet["isresult"] = 1;
				$packet = DoraPacket::packEncode($packet, $data["protocol"]);

				//sys_get_temp_dir
				$serv->send($fd, $packet);
				unset($this->taskInfo[$fd][$guid]);

				return true;
				break;
			case DoraConst::SW_MODE_ASYNCRESULT_MULTI:
				if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
					$packet = DoraPacket::packFormat("OK", 0, $this->taskInfo[$fd][$guid]["result"]);
					$packet["guid"] = $guid;
					$packet["isresult"] = 1;
					$packet = DoraPacket::packEncode($packet, $data["protocol"]);
					$serv->send($fd, $packet);

					unset($this->taskInfo[$fd][$guid]);

					return true;
				} else {
					//multi call task
					//not finished
					//waiting other result
					return true;
				}
				break;
			default:
				//
				return true;
				break;
		}
	}
	//普通事件：接收到task
	public static function onTask($serv, $task_id, $from_id, $data) {
		try {
			$data["result"] = Packet::packFormat("OK", 0, call_user_func(self::$taskHandle[$type], $data));
		} catch (\Exception $e) {
			$data["result"] = Packet::packFormat($e->getMessage(), $e->getCode());
		}
		return $data;
	}
}