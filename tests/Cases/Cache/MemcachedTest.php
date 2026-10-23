<?php
namespace SyTest\Cache;

use PHPUnit\Framework\TestCase;
use Sy\Cache\Memcached as SyMemcached;

class MemcachedTest extends TestCase {
  public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if (!class_exists('Memcached', FALSE)) {
			self::markTestSkipped('跳过：缺少 memcached 扩展');
		}
	}
	public function testMemcached() {
		$handler = new SyMemcached();
		TestUtils::single($this, $handler);
		TestUtils::multi($this, $handler);
		TestUtils::clear($this, $handler);
	}
}