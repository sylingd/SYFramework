<?php
/**
 * Redis
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

class Redis extends CacheAbstract {
	/** @var object $connection Connection */
	private $connection;

	public function __construct() {
		if (!class_exists('Redis', FALSE)) {
			throw new RequirementException('Class "Redis" is required');
		}
		if (!App::$config->has('redis')) {
			throw new Exception('No redis config found');
		}
		$config = App::$config->get('redis');
		$this->connection = new \Redis;
		$this->connection->connect($config['host'], $config['port']);
		if (!empty($config['password'])) {
			$this->connection->auth($config['password']);
		}
		if (isset($config['index'])) {
			$this->connection->select($config['index']);
		}
	}

	public function getConnection() {
		return $this->connection;
	}

	public function get(string $key, $default = null): mixed {
		$result = $this->connection->get($key);
		return ($result === false || $result === null) ? $default : unserialize($result);
	}

	public function set(string $key, mixed $value, $ttl = null): bool {
		if ($ttl !== null) {
			return $this->connection->setEx($key, self::getSeconds($ttl), serialize($value));
		} else {
			return $this->connection->set($key, serialize($value));
		}
	}

	public function ttl(string $key, $ttl): bool {
		return $this->connection->expire($key, self::getSeconds($ttl));
	}

	public function delete(string $key): bool {
		return $this->connection->del($key);
	}

	public function clear(): bool {
		return $this->connection->flushDb();
	}

	public function getMultiple(iterable $keys, $default = null): array {
		$result = $this->connection->mGet($keys);
		foreach ($result as $k => $v) {
			if ($v === false || $v === null) {
				$result[$k] = $default;
			} else {
				$result[$k] = unserialize($v);
			}
		}
		return array_combine($keys, $result);
	}

	public function setMultiple(iterable $values, $ttl = null): bool {
		foreach ($values as $k => $v) {
			$values[$k] = serialize($v);
		}
		$this->connection->mSet($values);
		if ($ttl !== null) {
			foreach ($values as $k => $v) {
				$this->connection->expire($k, $ttl);
			}
		}
		return true;
	}

	public function deleteMultiple(iterable $keys): bool {
		return $this->connection->del($keys);
	}

	public function has(string $key): bool {
		return $this->connection->exists($key);
	}
}