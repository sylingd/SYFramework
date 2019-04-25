<?php
namespace SyTest\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Cache\Adapter\Yac as YesfYac;
use SyTest\Cache\TestUtils;

class YacTest extends TestCase {
	/**
	 * @requires extension yac
	 */
	public function testYac() {
		$handler = new YesfYac();
		TestUtils::single($this, $handler);
		TestUtils::multi($this, $handler);
		TestUtils::clear($this, $handler);
	}
}