<?php
namespace SyTest\Cache;

use PHPUnit\Framework\TestCase;
use Sy\Cache\Redis as SyRedis;

class RedisTest extends TestCase {
  public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if (!class_exists('Redis', FALSE)) {
			self::markTestSkipped('跳过：缺少 redis 扩展');
		}
	}
	public function testRedis() {
		$handler = new SyRedis();
		TestUtils::single($this, $handler);
		TestUtils::multi($this, $handler);
		TestUtils::clear($this, $handler);
	}
}