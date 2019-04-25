<?php
namespace SyTest\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Sy\Cache\Adapter\File;
use SyTest\Cache\TestUtils;

class FileTest extends TestCase {
	public function testFile() {
		$handler = new File();
		TestUtils::single($this, $handler);
		TestUtils::multi($this, $handler);
		TestUtils::clear($this, $handler);
	}
}