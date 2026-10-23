<?php
namespace SyTest\Cache;

use PHPUnit\Framework\TestCase;
use Sy\Cache\Yac as SyYac;

class YacTest extends TestCase {
  public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if (!class_exists('Yac', FALSE)) {
			self::markTestSkipped('跳过：缺少 yac 扩展');
		}
	}

	public function testYac() {
		$handler = new SyYac();
		TestUtils::single($this, $handler);
		TestUtils::multi($this, $handler);
		TestUtils::clear($this, $handler);
	}
}