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

class RpcServer extends Server {
	//HTTP事件：收到请求
	public static function eventRequest($req, $response) {
		//JSON格式
		foreach (self::httpConfig['response_header'] as $k => $v) {
			$response->header($k, $v);
		}
		//状态必定为200
		$response->status(200);

		//检查参数
		if (!isset($request->post['params'])) {
			$response->end(json_encode(DoraPacket::packFormat('Parameter was not set or wrong', 100003)));
			return;
		}
		$params = $request->post;
		$params = json_decode($params['params'], true);
		if (!isset($params['guid']) || !isset($params['api']) || count($params['api']) == 0) {
			$response->end(json_encode(DoraPacket::packFormat('Parameter was not set or wrong', 100003)));
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
				$pack = DoraPacket::packFormat('transfer success.已经成功投递', 100001);
				$pack['guid'] = $task['guid'];
				$response->end(json_encode($pack));
				break;
			case 'server/cmd':
				$task['type'] = DoraConst::SW_CONTROL_CMD;
				if ($params['api']['cmd']['name'] == 'getStat') {
					// $pack = DoraPacket::packFormat('OK', 0, array('server' => $this->server->stats()));
					// $pack['guid'] = $task['guid'];
					// $response->end(json_encode($pack));
					// return;
				}
				if ($params['api']['cmd']['name'] == 'reloadTask') {
					// $pack = DoraPacket::packFormat('OK', 0, array('server' => $this->server->stats()));
					// $this->server->reload(true);
					// $pack['guid'] = $task['guid'];
					// $response->end(json_encode($pack));
					// return;
				}
				break;
			default:
				$response->end(json_encode(DoraPacket::packFormat('unknow task type.未知类型任务', 100002)));
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
	public static function eventTcpReceive($serv, $fd, $from_id, $data) {
		$requestInfo = DoraPacket::packDecode($data);

		#decode error
		if ($requestInfo["code"] != 0) {
			$pack["guid"] = $requestInfo["guid"];
			$req = DoraPacket::packEncode($requestInfo);
			$serv->send($fd, $req);

			return true;
		} else {
			$requestInfo = $requestInfo["data"];
		}

		#api was not set will fail
		if (!is_array($requestInfo["api"]) && count($requestInfo["api"])) {
			$pack = DoraPacket::packFormat("param api is empty", 100003);
			$pack["guid"] = $requestInfo["guid"];
			$pack = DoraPacket::packEncode($pack);
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
				self::taskInfo[$fd][$guid]["taskkey"][$taskid] = "one";

				return true;
				break;
			case DoraConst::SW_MODE_NORESULT_SINGLE:
				$task["api"] = $requestInfo["api"]["one"];
				$serv->task($task);

				//return success deploy
				$pack = DoraPacket::packFormat("transfer success.已经成功投递", 100001);
				$pack["guid"] = $task["guid"];
				$pack = DoraPacket::packEncode($pack);
				$serv->send($fd, $pack);

				return true;

				break;

			case DoraConst::SW_MODE_WAITRESULT_MULTI:
				foreach ($requestInfo["api"] as $k => $v) {
					$task["api"] = $requestInfo["api"][$k];
					$taskid = $serv->task($task);
					self::taskInfo[$fd][$guid]["taskkey"][$taskid] = $k;
				}

				return true;
				break;
			case DoraConst::SW_MODE_NORESULT_MULTI:
				foreach ($requestInfo["api"] as $k => $v) {
					$task["api"] = $requestInfo["api"][$k];
					$serv->task($task);
				}

				$pack = DoraPacket::packFormat("transfer success.已经成功投递", 100001);
				$pack["guid"] = $task["guid"];
				$pack = DoraPacket::packEncode($pack);

				$serv->send($fd, $pack);

				return true;
				break;
			case DoraConst::SW_CONTROL_CMD:
				switch ($requestInfo["api"]["cmd"]["name"]) {
					case "getStat":
						$pack = DoraPacket::packFormat("OK", 0, array("server" => $serv->stats()));
						$pack["guid"] = $task["guid"];
						$pack = DoraPacket::packEncode($pack);
						$serv->send($fd, $pack);
						return true;
						break;
					case "reloadTask":
						$pack = DoraPacket::packFormat("OK", 0, array("server" => $serv->stats()));
						$pack["guid"] = $task["guid"];
						$pack = DoraPacket::packEncode($pack);
						$serv->send($fd, $pack);
						$serv->reload(true);
						return true;
						break;
					default:
						$pack = DoraPacket::packFormat("unknow cmd", 100011);
						$pack = DoraPacket::packEncode($pack);
						$serv->send($fd, $pack);
						unset(self::taskInfo[$fd]);
						break;
				}
				break;

			case DoraConst::SW_MODE_ASYNCRESULT_SINGLE:
				$task["api"] = $requestInfo["api"]["one"];
				$taskid = $serv->task($task);
				self::taskInfo[$fd][$guid]["taskkey"][$taskid] = "one";
				//return success
				$pack = DoraPacket::packFormat("transfer success.已经成功投递", 100001);
				$pack["guid"] = $task["guid"];
				$pack = DoraPacket::packEncode($pack);
				$serv->send($fd, $pack);
				return true;
				break;
			case DoraConst::SW_MODE_ASYNCRESULT_MULTI:
				foreach ($requestInfo["api"] as $k => $v) {
					$task["api"] = $requestInfo["api"][$k];
					$taskid = $serv->task($task);
					self::taskInfo[$fd][$guid]["taskkey"][$taskid] = $k;
				}
				//return success
				$pack = DoraPacket::packFormat("transfer success.已经成功投递", 100001);
				$pack["guid"] = $task["guid"];
				$pack = DoraPacket::packEncode($pack);
				$serv->send($fd, $pack);
				break;
			default:
				$pack = DoraPacket::packFormat("unknow task type.未知类型任务", 100002);
				$pack = DoraPacket::packEncode($pack);
				$serv->send($fd, $pack);
				//unset(self::taskInfo[$fd]);
				return true;
		}

		return true;
	}
	//普通事件：Task结束
	public static function eventFinish($serv, $task_id, $data) {
		$fd = $data["fd"];
		$guid = $data["guid"];
		//if the guid not exists .it's mean the api no need return result
		if (!isset(self::taskInfo[$fd][$guid])) {
			return true;
		}
		//get the api key
		$key = self::taskInfo[$fd][$guid]["taskkey"][$task_id];
		//save the result
		self::taskInfo[$fd][$guid]["result"][$key] = $data["result"];
		//remove the used taskid
		unset(self::taskInfo[$fd][$guid]["taskkey"][$task_id]);
		switch ($data["type"]) {
			case DoraConst::SW_MODE_WAITRESULT_SINGLE:
				$packet = DoraPacket::packFormat("OK", 0, $data["result"]);
				$packet["guid"] = $guid;
				$packet = DoraPacket::packEncode($packet, $data["protocol"]);

				$serv->send($fd, $packet);
				unset(self::taskInfo[$fd][$guid]);

				return true;
				break;

			case DoraConst::SW_MODE_WAITRESULT_MULTI:
				if (count(self::taskInfo[$fd][$guid]["taskkey"]) == 0) {
					$packet = DoraPacket::packFormat("OK", 0, self::taskInfo[$fd][$guid]["result"]);
					$packet["guid"] = $guid;
					$packet = DoraPacket::packEncode($packet, $data["protocol"]);
					$serv->send($fd, $packet);
					//$serv->close($fd);
					unset(self::taskInfo[$fd][$guid]);

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
				unset(self::taskInfo[$fd][$guid]);

				return true;
				break;
			case DoraConst::SW_MODE_ASYNCRESULT_MULTI:
				if (count(self::taskInfo[$fd][$guid]["taskkey"]) == 0) {
					$packet = DoraPacket::packFormat("OK", 0, self::taskInfo[$fd][$guid]["result"]);
					$packet["guid"] = $guid;
					$packet["isresult"] = 1;
					$packet = DoraPacket::packEncode($packet, $data["protocol"]);
					$serv->send($fd, $packet);

					unset(self::taskInfo[$fd][$guid]);

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
	//普通事件：接收到task
	public static function eventTask($serv, $task_id, $from_id, $data) {
		if (isset($task['guid'])) {
			//RPC事件
			$apiName = $task['api']['name'];
			try {
				$c = Sy::controller($apiName);
				$data["result"] = DoraPacket::packFormat("OK", 0, call_user_func($c, $data));
			} catch (\Exception $e) {
				$data["result"] = DoraPacket::packFormat($e->getMessage(), $e->getCode());
			}
			return $data;
		} else {
			return parent::eventTask($serv, $task_id, $from_id, $data);
		}
	}
}