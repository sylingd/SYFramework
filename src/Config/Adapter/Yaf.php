<?php
/**
 * Yaf设置支持类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2015-2026 ShuangYa
 * @license Apache License 2.0
 */
namespace Sy\Config\Adapter;

use Sy\App;
use Sy\Exception\RequirementException;
use Sy\Config\ConfigTrait;
use Sy\Config\ConfigInterface;

class Yaf implements ConfigInterface {
	use ConfigTrait;
	/** @var string $environment Environment name */
	protected $environment;

	/** @var object $conf Configs */
	protected $conf;

	/**
	 * Constructor
	 * 
	 * @access public
	 * @param string $file
	 */
	public function __construct($file) {
		if (!extension_loaded('yaf')) {
			throw new RequirementException("Extension Yaf is required");
		}
		$this->environment = App::getEnv();
		if (class_exists('\\Yaf_Config_Ini', false)) {
			$this->conf = new \Yaf_Config_Ini($file, $this->environment);
		} else {
			$this->conf = new \Yaf\Config\Ini($file, $this->environment);
		}
	}
	/**
	 * 获取配置
	 * 
	 * @access public
	 * @param string $key 形似a.b.c的key
	 * @param mixed $default 默认
	 * @return mixed
	 */
	public function get($key, $default = null) {
		if (!$this->has($key)) {
			return $default;
		}
		$rs = $this->conf->get($key);
		if (is_object($rs)) {
			$rs = $rs->toArray();
		}
		return $rs;
	}
	/**
	 * 检查配置是否存在
	 * 
	 * @access public
	 * @param string $key 形似a.b.c的key
	 * @return bool
	 */
	public function has($key) {
		return isset($this->conf[$key]);
	}
}