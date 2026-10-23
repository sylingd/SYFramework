<?php
/**
 * Yaconf设置支持类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2015-2026 ShuangYa
 * @license Apache License 2.0
 */
namespace Sy\Config\Adapter;

use \Yaconf as YaconfClass;
use Sy\App;
use Sy\Config\ConfigTrait;
use Sy\Config\ConfigInterface;
use Sy\Exception\RequirementException;

class Yaconf implements ConfigInterface {
	use ConfigTrait;
	/** @var string $appName Application name */
	protected $appName;

	/** @var string $environment Environment name */
	protected $environment;
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param string $appName
	 */
	public function __construct($appName = null) {
		if (!extension_loaded('yaconf')) {
			throw new RequirementException("Extension Yaconf is required");
		}
		$this->appName = $appName;
		$this->environment = App::getEnv();
	}
	/**
	 * Get full key
	 * 
	 * @access protected
	 * @param string $key original key
	 * @return string
	 */
	protected function getKey($key) {
		$key = $this->environment . '.' . $key;
		if (!empty($this->appName)) {
			$key = $this->appName . '.' . $key;
		}
		return $key;
	}
	/**
	 * 获取配置
	 * @access public
	 * @param string $key 形似a.b.c的key
	 * @param mixed $default 默认
	 * @return mixed
	 */
	public function get($key, $default = null) {
		$key = $this->getKey($key);
		return YaconfClass::has($key) ? YaconfClass::get($key) : $default;
	}
	/**
	 * 检查配置是否存在
	 * @access public
	 * @param string $key 形似a.b.c的key
	 * @return bool
	 */
	public function has($key) {
		$key = $this->getKey($key);
		return YaconfClass::has($key);
	}
}