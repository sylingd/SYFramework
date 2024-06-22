<?php
/**
 * Request
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Library
 * @link https://www.sylibs.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy\Http;

use Sy\App;
use Sy\Exception\Exception;

class Request {
	/** @var object $session Cached session */
	private $session = null;

	/** @var string $module Module */
	public $module = null;
	/** @var string $controller Controller */
	public $controller = null;
	/** @var string $action Action */
	public $action = null;

	/** @var array $param Request params */
	public $param = [];

	/** @var string $uri Parsed request uri */
	public $uri = '';
	
	/** @var array Same as php */
	public $get;
	public $post;
	public $request;
	public $server;
	public $cookie;
	public $files;

	/** @var string $extension Extension */
	public $extension;

	/** @var array $store A storage can used by your self */
	public $store = [];

	private function parseJsonBody() {
		$cType = isset($this->server['HTTP_CONTENT_TYPE']) ? $this->server['HTTP_CONTENT_TYPE'] : '';
		if (strpos($cType, 'application/json') === 0) {
			$this->post = json_decode($this->rawContent(), true);
		}
	}

	public function __construct() {
		$this->get = &$_GET;
		$this->post = &$_POST;
		$this->request = &$_REQUEST;
		$this->server = &$_SERVER;
		$this->cookie = &$_COOKIE;
		$this->files = &$_FILES;
		// Parse json object
		try {
			$this->parseJsonBody();
		} catch (\Error) {
			// ignore
		}
	}

	/**
	 * Magic methods to read & write store
	 */
	public function __get($name)  {
		if (isset($this->store[$name])) {
			return $this->store[$name];
		}
		if (isset($this->request[$name])) {
			return $this->request[$name];
		}
		trigger_error('Undefined property ' . $name, E_USER_NOTICE);
		return null;
	}
	public function __set($name, $value) {
		$this->store[$name] = $value;
	}
	public function __isset($name) {
		return isset($this->store[$name]) || isset($this->request[$name]);
	}
	public function __unset($name) {
		unset($this->store[$name], $this->request[$name]);
	}

	/**
	 * HTTP Request methods check
	 */
	public function isGet() {
		return $this->server['REQUEST_METHOD'] === 'GET';
	}
	public function isPost() {
		return $this->server['REQUEST_METHOD'] === 'POST';
	}
	public function isHead() {
		return $this->server['REQUEST_METHOD'] === 'HEAD';
	}
	public function isPut() {
		return $this->server['REQUEST_METHOD'] === 'PUT';
	}
	public function isPatch() {
		return $this->server['REQUEST_METHOD'] === 'PATCH';
	}
	public function isOptions() {
		return $this->server['REQUEST_METHOD'] === 'OPTIONS';
	}
	public function isDelete() {
		return $this->server['REQUEST_METHOD'] === 'DELETE';
	}

	/**
	 * Get original post body
	 * 
	 * @access public
	 * @return string
	 */
	public function rawContent() {
		return file_get_contents('php://input');
	}

	/**
	 * Get session
	 * 
	 * @access public
	 * @return object
	 */
	public function session() {
		if ($this->session === null) {
			$this->session = new Session();
		}
		return $this->session;
	}
}