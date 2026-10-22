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

use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

interface CacheInterface extends PsrCacheInterface {
  /**
   * 设置缓存过期时间
   * 
   * @access public
   * @param string $key 缓存键
   * @param int|\DateInterval $ttl 过期时间
   * @return bool
   */
  public function ttl(string $key, int|\DateInterval $ttl): bool;
}
