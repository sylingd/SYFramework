<?php

/**
 * 配置�?
 * 
 * @author ShuangYa
 * @package EUser
 * @category Library
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=euser&type=license
 */

namespace euser\libs;
use \sy\lib\YRedis;

class option {
	public $option = NULL;
	static $_instance = null;
	static public function _i() {
		if (self::$_instance === null) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	/**
	 * 构造函�?
	 */
	public function __construct() {

	}
	/**
	 * 读取配置
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		if ($this->option === NULL) {
			$this->option = unserialize(YRedis::_i()->get('option'));
		}
		return $this->option[$key];
	}
	/**
	 * 写入配置
	 * @access public
	 * @param string $key
	 * @param mixed $val
	 */
	public function set($key, $val) {
		if ($this->option === NULL) {
			$this->option = unserialize(YRedis::_i()->get('option'));
		}
		$this->option[$key] = $val;
		YRedis::_i()->set('option', serialize($this->option));
	}

}
