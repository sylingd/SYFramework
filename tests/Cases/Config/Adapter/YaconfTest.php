<?php
namespace SyTest\Config\Adapter;

use PHPUnit\Framework\TestCase;
use Sy\Config\Adapter\Yaconf;

/**
 * Requires the yaconf extension and yaconf.directory pointing to
 * tests/App/yaconf (set in CI). Locally without the extension this
 * test is skipped; in CI it must run for real.
 */
class YaconfTest extends TestCase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if (!extension_loaded('yaconf')) {
			self::markTestSkipped('跳过：缺少 yaconf 扩展');
		}
	}

	public function testGetAndHas() {
		$config = new Yaconf();
		$this->assertSame('SYFramework', $config->get('name'));
		$this->assertSame('1.0.0', $config->get('version'));
		$this->assertTrue($config->has('name'));
		$this->assertFalse($config->has('not_exists'));
		$this->assertNull($config->get('not_exists'));
		$this->assertSame('default', $config->get('not_exists', 'default'));
	}

	public function testTypePreserved() {
		$config = new Yaconf();
		// yaconf returns ini values as strings, keep booleans truthy
		$this->assertSame('42', $config->get('answer'));
		$this->assertEquals(true, $config->get('debug'));
	}

	public function testAppNamePrefix() {
		$config = new Yaconf('app');
		$this->assertSame('AppSYFramework', $config->get('name'));
		$this->assertTrue($config->has('name'));
		// without app name, keys are not prefixed
		$this->assertSame('SYFramework', (new Yaconf())->get('name'));
	}

	public function testMagicAccessors() {
		$config = new Yaconf();
		$this->assertSame('SYFramework', $config->name);
		$this->assertTrue(isset($config->name));
		$this->assertFalse(isset($config->not_exists));
	}
}
