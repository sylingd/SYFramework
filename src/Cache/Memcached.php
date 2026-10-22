<?php
/**
 * Memcached支持类
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

class Memcached extends CacheAbstract {
	/** @var object $connection Connection */
	private $connection;

	public function __construct() {
		if (!class_exists('Memcached', FALSE)) {
			throw new RequirementException('Class "Memcached" is required');
		}
		if (!App::$config->has('memcached')) {
			throw new Exception('No memcached config found');
		}
		$config = App::$config->get('memcached');
		$this->connection = new \Memcached;
		if (isset($config['server']) && is_array($config['server'])) {
			$this->connection->addServers($config['server']);
		} else {
			$this->connection->addServer($config['host'], $config['port']);
		}
	}

	public function getConnection() {
		return $this->connection;
	}

	public function get(string $key, mixed $default = null): mixed {
		$result = $this->connection->get($key);
		if (!is_string($result) || $this->connection->getResultCode() === \Memcached::RES_NOTSTORED) {
			return $default;
		}
		return unserialize($result);
	}

	public function set(string $key, mixed $value, $ttl = 0): bool {
		return $this->connection->set($key, serialize($value), self::getSeconds($ttl));
	}


	public function ttl(string $key, $ttl): bool {
		return $this->connection->touch($key, self::getSeconds($ttl));
	}
	
	public function delete(string $key): bool {
		return $this->connection->delete($key);
	}

	public function clear(): bool {
		return $this->connection->flush();
	}

	public function getMultiple(iterable $keys, mixed $default = null): array {
		$result = $this->connection->getMulti($keys);
		foreach ($keys as $key) {
			if (isset($result[$key])) {
				$result[$key] = unserialize($result[$key]);
			} else {
				$result[$key] = $default;
			}
		}
		return $result;
	}

	public function setMultiple(iterable $values, $ttl = 0): bool {
		foreach ($values as $k => $v) {
			$values[$k] = serialize($v);
		}
		return $this->connection->setMulti($values, $ttl);
	}

	public function deleteMultiple(iterable $keys): bool {
		$result = $this->connection->deleteMulti($keys);
		return !in_array(false, $result, true);
	}

	public function has(string $key): bool {
		return $this->get($key) !== null;
	}
}