<?php

/**
 * URL抓取类
 * 暂时只封装CURL，后续封装fsockopen
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
		'ssl_verifyhost' => 0
	];
	protected $opt = [];
	static public function i($opt = []) {
		return new self($opt);
	}
	public function __construct($opt, $execute = TRUE) {
		if (function_exists('curl_init')) {
			$this->connectMethod = 'curl';
		} else {
			throw new SYException('Extension "curl" is required', '10050');
		}
		$this->init();
		$this->setopt(array_merge($this->defaultOpt, array_change_key_case($opt, CASE_LOWER)));
		$this->strExec = $execute;
	}
	protected function init() {
		if ($this->connectMethod === 'curl') {
			$this->handle = curl_init();
		}
	}
	public function setopt($opt) {
		$this->opt = array_merge($this->opt, array_change_key_case($opt, CASE_LOWER));
	}
	public function exec() {
		if ($this->connectMethod === 'curl') {
			if (isset($this->opt['postfields']) && !isset($this->opt['customrequest'])) {
				$this->opt['customrequest'] === 'POST';
			}
			if (isset($this->opt['customrequest'])) {
				if ($this->opt['customrequest'] === 'GET') {
				} elseif ($this->opt['customrequest'] === 'POST') {
					curl_setopt($this->handle, CURLOPT_POST, 1);
				} else {
					curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $this->opt['customrequest']);
				}
				unset($this->opt['customrequest']);
			}
			foreach ($this->opt as $k => $v) {
				if (defined('CURLOPT_' . strtoupper($k))) {
					curl_setopt($this->handle, constant('CURLOPT_' . strtoupper($k)), $v);
				}
			}
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
		}
	}
	/**
	 * 解析回复数据，将head和body分开
	 * @param string $response
	 * @return array
	 */
	protected function parseResponse($response) {
		if (strpos($response ,"\r\n") !== FALSE) {
			list($header, $body) = explode("\r\n\r\n", $response, 2);
			$header = explode("\r\n", $header);
		} else {
			list($header, $body) = explode("\n\n", $response, 2);
			$header = explode("\n", $header);
		}
		$responseType = $header[0];
		unset($header[0]);
		//转为数组
		$head = [];
		foreach ($header as $v) {
			list($kk, $vv) = explode(': ', $v, 2);
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
		if ($this->connectMethod === 'curl' && $this->handle !== NULL) {
			@curl_close($this->handle);
		}
	}
}