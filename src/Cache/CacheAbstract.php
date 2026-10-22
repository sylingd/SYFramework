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
  protected static function getSeconds(int|\DateInterval $ttl): int {
    if ($ttl instanceof \DateInterval) {
      return ($ttl->days * 86400)
        + $ttl->h * 3600
        + $ttl->i * 60
        + $ttl->s;
    }
    return $ttl;
  }
}