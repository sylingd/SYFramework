<?php
/**
 * 扩展的CacheInterface
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

abstract class CacheAbstract implements CacheInterface {
  /**
   * 设置缓存过期时间
   * 
   * @access public
   * @param string $key 缓存键
   * @param int|\DateInterval $ttl 过期时间
   * @return bool
   */
  protected static function getSeconds(int|\DateInterval $ttl): int {
    if ($ttl instanceof \DateInterval) {
      return ($ttl->days * 86400)
        + $ttl->h * 3600
        + $ttl->i * 60
        + $ttl->s;
    }
    return $ttl;
  }

  /**
   * 获取缓存或执行查询
   * 
   * @access public
   * @param string $key 缓存键
   * @param callable $query 查询函数
   * @return mixed
   */
  public function getOrQuery(string $key, callable $query, null|int|\DateInterval $ttl = null): mixed {
    if ($this->has($key)) {
      return $this->get($key);
    }

    $data = call_user_func($query);
    $this->set($key, $data, $ttl);
    return $data;
  }
}