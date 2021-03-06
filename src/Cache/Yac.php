<?php
/**
 * Yac
 * 
 * @see Psr\SimpleCache\CacheInterface
 * @author ShuangYa
 * @package SYFramework
 * @category Cache
 * @link https://www.sylibs.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy\Cache;

use Psr\SimpleCache\CacheInterface;
use Sy\App;
use Sy\Exception\Exception;
use Sy\Exception\RequirementException;

class Yac implements CacheInterface {
	/** @var Yac $handler Yac class instance */
	private $handler;

	public function __construct() {
		if (!class_exists(\Yac::class)) {
			throw new RequirementException("Extension Yac is required");
		}
		$prefix = App::$config->get('cache.prefix', '');
		$len = YAC_MAX_KEY_LEN - 32;
		if (strlen($prefix) > $len) {
			throw new Exception("Prefix length must be less than $len");
		}
		$this->handler = new \Yac($prefix);
	}

	private function getKey($key) {
		return strlen($key) > 32 ? md5($key) : $key;
	}

	public function get($key, $default = null) {
		$res = $this->handler->get($this->getKey($key));
		if ($res === false) {
			return $default;
		}
		return $res;
	}

	public function set($key, $value, $ttl = 0) {
		$this->handler->set($this->getKey($key), $value, $ttl);
	}

	public function delete($key) {
		$this->handler->delete($this->getKey($key));
	}

	public function clear() {
		$this->handler->flush();
	}

	public function getMultiple($keys, $default = null) {
		$toGet = [];
		foreach ($keys as $v) {
			$toGet[] = $this->getKey($v);
		}
		$result = [];
		$res = $this->handler->get($toGet);
		foreach ($toGet as $k => $v) {
			if ($res[$v] === false) {
				$result[$keys[$k]] = $default;
			} else {
				$result[$keys[$k]] = $res[$v];
			}
		}
		return $result;
	}

	public function setMultiple($values, $ttl = 0) {
		$toSet = [];
		foreach ($values as $k => $v) {
			$toSet[$this->getKey($k)] = $v;
		}
		$this->handler->set($toSet, $ttl);
	}

	public function deleteMultiple($keys) {
		foreach ($keys as $k => $v) {
			$keys[$k] = $this->getKey($v);
		}
		$this->handler->delete($keys);
	}

	public function has($key) {
		return $this->get($key) !== null;
	}
}