<?php

/**
 * URL抓取类
 * fsockopen暂不支持文件上传！
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Library
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\lib;
use \Sy;

class YFetchURL {
	protected $handle;
	protected $connectMethod;
	protected $strExec = FALSE;
	protected $defaultOpt = [
		'returntransfer' => 1,
		'header' => 1,
		'ssl_verifypeer' => 0,
		'ssl_verifyhost' => 0,
		'timeout' => 10,
		'useragent' => 'SYFramework FetchURL'
	];
	protected $defaultHeader = [
		'Connection' => 'close'
	];
	protected $opt = [];
	protected $header = [];
	static public function i($opt = []) {
		return new self($opt);
	}
	public function __construct($opt, $execute = TRUE) {
		if (function_exists('curl_init')) {
			$this->connectMethod = 'curl';
		} elseif (function_exists('fsockopen')) {
			$this->connectMethod = 'fsockopen';
		} else {
			throw new SYException('Extension "curl" or "fsockopen" is required', '10050');
		}
		$this->init();
		$this->setheader($this->defaultHeader);
		$this->setopt(array_merge($this->defaultOpt, array_change_key_case($opt, CASE_LOWER)));
		$this->strExec = $execute;
	}
	protected function init() {
		if ($this->connectMethod === 'curl') {
			$this->handle = curl_init();
		}
	}
	/**
	 * 设置基本选项
	 * @access public
	 * @param array $opt
	 */
	public function setopt($opt) {
		if (isset($opt['httpheader'])) {
			$this->setheader($this->headerImport($opt['httpheader']));
			unset($opt['httpheader']);
		}
		$this->opt = array_merge($this->opt, array_change_key_case($opt, CASE_LOWER));
		return $this;
	}
	/**
	 * CURL形式Header与框架内部Header互转
	 * @access protected
	 */
	protected function headerImport($header) {
		$r = [];
		foreach ($header as $h) {
			list($k, $v) = explode(':', $h, 2);
			$r[$k] = trim($v);
		}
		return $r;
	}
	protected function headerOutput($header) {
		$r = [];
		foreach ($header as $k => $v) {
			$r[] = $k . ': ' . $v; 
		}
		return $r;
	}
	/**
	 * 设置header
	 * 仅接受框架格式的header
	 * @access public
	 * @param array $header
	 */
	public function setheader($header) {
		unset($header['Host'], $header['User-Agent']);
		$this->header = array_merge($this->header, $header);
		return $this;
	}
	/**
	 * 执行请求
	 * @access public
	 * @return array
	 */
	public function exec() {
		//基本处理
		if (isset($this->opt['postfields']) && !isset($this->opt['customrequest'])) {
			$this->opt['customrequest'] === 'POST';
		}
		if ($this->connectMethod === 'curl') {
			//处理请求方式
			if (isset($this->opt['customrequest'])) {
				$this->opt['customrequest'] = strtoupper($this->opt['customrequest']);
				if ($this->opt['customrequest'] === 'GET') {
				} elseif ($this->opt['customrequest'] === 'POST') {
					curl_setopt($this->handle, CURLOPT_POST, 1);
				} else {
					curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $this->opt['customrequest']);
				}
				unset($this->opt['customrequest']);
			}
			//处理timeout
			if (isset($this->opt['timeout_ms'])) {
				unset($this->opt['timeout']);
			}
			//处理其他参数
			foreach ($this->opt as $k => $v) {
				if (defined('CURLOPT_' . strtoupper($k))) {
					curl_setopt($this->handle, constant('CURLOPT_' . strtoupper($k)), $v);
				}
			}
			curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->headerOutput($this->header));
			//执行
			$response = curl_exec($this->handle);
			$errno = curl_errno($this->handle);
			if ($errno > 0) {
				throw new SYException(curl_error($this->handle), '10051');
			}
			//将header和body分开
			if ($this->opt['header'] == 1) {
				return $this->parseResponse($response);
			} else {
				return $response;
			}
		} elseif ($this->connectMethod === 'fsockopen') {
			//处理timeout
			if (isset($this->opt['timeout_ms'])) {
				$this->opt['timeout'] = round($this->opt['timeout_ms'] / 1000);
			}
			$start_time = time();
			while (TRUE) {
				//解析URL
				$urlInfo = parse_url($this->opt['url']);
				if ($urlInfo === FALSE) {
					throw new SYException('Invalid URL', '10052');
				}
				if ($urlInfo['scheme'] == 'https') {
					$urlInfo['host'] = 'ssl://' . $urlInfo['host'];
					$urlInfo['port'] = ($urlInfo['port'] != 0) ? $urlInfo['port'] : 443;
				} else {
					$urlInfo['host'] = $urlInfo['host'];
					$urlInfo['port'] = ($urlInfo['port'] != 0) ? $urlInfo['port'] : 80;
				}
				$urlInfo['path'] = (isset($urlInfo['path']) ? $urlInfo['path'] : '/') . (isset($urlInfo['query']) ? '?' . $urlInfo['query'] : '');
				$errorNumber = 0;
				$errorString = NULL;
				try {
					$this->handle = fsockopen($urlInfo['host'], $urlInfo['port'], $errorNumber, $errorString, $this->opt['timeout'] - (time() - $start_time));
					stream_set_timeout($this->handle, $this->opt['timeout'] - (time() - $start_time));
				} catch (\Exception $e) {
					throw new SYException($e->getMessage(), '10053');
				}
				if ($this->handle === FALSE) {
					throw new SYException($errorString, '10053');
				}
				//header处理
				if (isset($this->opt['customrequest'])) {
					$this->opt['customrequest'] = strtoupper($this->opt['customrequest']);
				} else {
					$this->opt['customrequest'] = 'GET';
				}
				$request  = $this->opt['customrequest'] . ' ' . $urlInfo['path'] . '  HTTP/1.1' . '\r\n';
				$request .= 'Host: ' . $urlInfo['host'] . "\r\n";
				$request .= 'User-Agent: ' . $this->opt['useragent'] . "\r\n";
				if ($this->opt['customrequest'] === 'POST') {
					$queryString = is_array($this->opt['postfields']) ? http_build_query($this->opt['postfields']) : $this->opt['postfields'];
					$request .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
					$request .= 'Content-Length: ' . strlen($queryString) . "\r\n";
				}
				if (isset($urlInfo['user'])) {
					$request .= 'Authorization: Basic ' . base64_encode($urlInfo['user'] . ':' . $urlInfo['pass']) . "\r\n";
				}
				foreach ($this->header as $k => $v) {
					$request .= $k . ': ' . $v . "\r\n";
				}
				$request .= "\r\n\r\n";
				if ($this->opt['customrequest'] === 'POST') {
					$request .= $queryString;
				}
				//执行
				fwrite($this->handle, $request);
				$response = '';
				do {
					$response .= fread($this->handle, 512);
				} while (!preg_match('/\\r\\n\\r\\n$/', $response));
				//超时错误
				$info = stream_get_meta_data($this->handle);
				if ($info['time_out']) {
					throw new SYException('Connect timeout', '10054');
				}
				$response = $this->parseResponse($response);
				//处理跳转
				if (isset($response['head']['Location']) && $this->opt['followlocation']) {
					$this->opt['url'] = $response['head']['Location'];
				} else {
					return $response;
				}
			}
		}
	}
	/**
	 * 解析回复数据，将head和body分开
	 * @param string $response
	 * @return array
	 */
	protected function parseResponse($response) {
		list($header, $body) = explode("\r\n\r\n", $response, 2);
		$header = explode("\r\n", $header);
		$responseType = $header[0];
		unset($header[0]);
		//转为数组
		$head = [];
		foreach ($header as $v) {
			list($kk, $vv) = explode(':', $v, 2);
			$head[trim($kk)] = trim($vv);
		}
		return ['type' => $responseType, 'head' => $head, 'body' => $body];
	}
	public function __toString() {
		if ($this->strExec) {
			$this->setopt(['header' => 0]);
			return $this->exec();
		} else {
			return 'Object ' . __CLASS__ . '(' . strval($this->opt). ')';
		}
	}
	public function __destruct() {
		if ($this->connectMethod === 'curl' && is_resource($this->handle)) {
			@curl_close($this->handle);
		} elseif ($this->connectMethod === 'fsockopen' && is_resource($this->handle)) {
			@fclose($this->handle);
		}
	}
}