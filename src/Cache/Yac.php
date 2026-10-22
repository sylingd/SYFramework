<?php
/**
 * Yac
 * 
 * @see Psr\SimpleCache\CacheInterface
 * @author ShuangYa
 * @package SYFramework
 * @category Cache
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2015-2026 ShuangYa
 * @license Apache License 2.0
 */
namespace Sy\Cache;

use Sy\App;
use Sy\Exception\Exception;
use Sy\Exception\RequirementException;

class Yac extends CacheAbstract {
	/** @var \Yac $handler Yac class instance */
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

	private function getKey(string $key): string {
		return strlen($key) > 32 ? md5($key) : $key;
	}

	public function get(string $key, $default = null): mixed {
		$res = $this->handler->get($this->getKey($key));
		if ($res === false) {
			return $default;
		}
		return $res;
	}

	public function set(string $key, mixed $value, $ttl = 0): bool {
		return $this->handler->set($this->getKey($key), $value, self::getSeconds($ttl));
	}

	public function ttl(string $key, $ttl = 0): bool {
		if (!$this->has($key)) {
			return false;
		}
		return $this->handler->set($this->getKey($key), $this->get($key), self::getSeconds($ttl));
	}

	public function delete(string $key): bool {
		return $this->handler->delete($this->getKey($key));
	}

	public function clear(): bool {
		return $this->handler->flush();
	}

	public function getMultiple(iterable $keys, $default = null): array {
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

	public function setMultiple(iterable $values, $ttl = 0): bool {
		$toSet = [];
		foreach ($values as $k => $v) {
			$toSet[$this->getKey($k)] = $v;
		}
		$this->handler->set($toSet, $ttl);
		return true;
	}

	public function deleteMultiple(iterable $keys): bool {
		foreach ($keys as $k => $v) {
			$keys[$k] = $this->getKey($v);
		}
		$this->handler->delete($keys);
		return true;
	}

	public function has(string $key): bool {
		return $this->get($key) !== null;
	}
}