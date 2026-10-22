<?php
/**
 * 文件
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

class File extends CacheAbstract {
	/** @var string $path Cache file directory */
	private $path;

	public function __construct() {
		$this->path = App::$config->get('cache.path');
		if ($this->path === '@TMP') {
			$this->path = sys_get_temp_dir();
		}
		if (strpos($this->path, '@APP') === 0) {
			$this->path = APP_PATH . substr($this->path, 5);
		}
		if (!is_dir($this->path)) {
			mkdir($this->path, 0777, true);
		}
		$this->path = str_replace('\\', '/', $this->path);
		if (substr($this->path, -1) !== '/') {
			$this->path .= '/';
		}
	}

	private function getKey($key) {
		return md5($key);
	}

	private function getPath($key) {
		$name = $this->getKey($key);
		return $this->path . $name;
	}

	public function get(string $key, $default = null): mixed {
		$fullpath = $this->getPath($key);
		if (!is_file($fullpath)) {
			return $default;
		}
		$res = file_get_contents($fullpath);
		if ($res === false) {
			return $default;
		}
		// The first 10 bytes is expire timestamp
		$expire = substr($res, 0, 10);
		if ($expire != 0 && $expire <= time()) {
			$this->delete($key);
			return $default;
		}
		return unserialize(substr($res, 10));
	}

	public function set(string $key, mixed $value, $ttl = null): bool {
		if ($ttl === null) {
			$time = 0;
		} else {
			$time = time() + self::getSeconds($ttl);
		}
		$path = $this->getPath($key);
		$content = str_pad($time, 10, '0', STR_PAD_LEFT) . serialize($value);
		file_put_contents($path, $content);
		return true;
	}

	public function ttl(string $key, $ttl): bool {
		$path = $this->getPath($key);
		if (!is_file($path)) {
			return false;
		}
    $fp = fopen($path, 'r+');
    if (!$fp) {
			throw new \RuntimeException("Cannot open file: {$path}");
    }
    try {
			fwrite($fp, str_pad(time() + self::getSeconds($ttl), 10, '0', STR_PAD_LEFT));
    } finally {
      fclose($fp);
    }
		return true;
	}

	public function delete(string $key): bool {
		return @unlink($this->getPath($key));
	}

	public function clear(): bool {
		$dh = opendir($this->path);
		while ($f = readdir($dh)) {
			if (!is_file($this->path . $f)) {
				continue;
			}
			@unlink($this->path . $f);
		}
		closedir($dh);
		return true;
	}

	public function getMultiple(iterable $keys, mixed $default = null): iterable {
		$result = [];
		foreach ($keys as $v) {
			$res = $this->get($v);
			if ($res === null) {
				$result[$v] = $default;
			} else {
				$result[$v] = $res;
			}
		}
		return $result;
	}

	public function setMultiple(iterable $values, $ttl = null): bool {
		foreach ($values as $k => $v) {
			$this->set($k, $v, $ttl);
		}
		return true;
	}

	public function deleteMultiple(iterable $keys): bool {
		foreach ($keys as $v) {
			$this->delete($v);
		}
		return true;
	}

	public function has(string $key): bool {
		return is_file($this->getPath($key));
	}
}