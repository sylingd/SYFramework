<?php
namespace SyTest\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Sy\Cache\Adapter\Yac as SyYac;
use SyTest\Cache\TestUtils;

class YacTest extends TestCase {
	/**
	 * @requires extension yac
	 */
	public function testYac() {
		$handler = new SyYac();
		TestUtils::single($this, $handler);
		TestUtils::multi($this, $handler);
		TestUtils::clear($this, $handler);
	}
}