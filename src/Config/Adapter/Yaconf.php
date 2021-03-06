<?php
/**
 * Yaconf设置支持类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy\Config\Adapter;

use Sy\App;
use Sy\Config\ConfigTrait;
use Sy\Config\ConfigInterface;

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
		return Yaconf::has($key) ? Yaconf::get($key) : $default;
	}
	/**
	 * 检查配置是否存在
	 * @access public
	 * @param string $key 形似a.b.c的key
	 * @return bool
	 */
	public function has($key) {
		$key = $this->getKey($key);
		return Yaconf::has($key);
	}
}