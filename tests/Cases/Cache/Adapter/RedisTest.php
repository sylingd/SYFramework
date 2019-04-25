<?php
namespace SyTest\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Sy\Cache\Adapter\Redis as SyRedis;
use SyTest\Cache\TestUtils;

class RedisTest extends TestCase {
	public function testRedis() {
		$handler = new SyRedis();
		TestUtils::single($this, $handler);
		TestUtils::multi($this, $handler);
		TestUtils::clear($this, $handler);
	}
}