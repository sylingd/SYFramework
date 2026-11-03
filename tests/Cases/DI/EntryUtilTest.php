<?php
namespace SyTest\DI;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Sy\App;
use Sy\Cache\CacheInterface;
use Sy\Cache\File;
use Sy\Config\Adapter\Arr;
use Sy\DB\DBInterface;
use Sy\DB\Mysql;
use Sy\DB\Sqlite;
use Sy\DI\Container;
use Sy\DI\EntryUtil;
use Sy\Http\Template;
use Sy\Http\TemplateInterface;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

class EntryUtilTest extends TestCase {
	private $origConfig;
	private $origAliases;

	public function setUp(): void {
		$this->origConfig = App::$config;
		$this->origAliases = $this->getAliases();
	}

	public function tearDown(): void {
		App::$config = $this->origConfig;
		$ref = new ReflectionProperty(Container::class, 'getter');
		$ref->setValue(Container::getInstance(), $this->origAliases);
	}

	private function getAliases(): array {
		$ref = new ReflectionProperty(Container::class, 'getter');
		return $ref->getValue(Container::getInstance());
	}

	public function testController() {
		$this->assertEquals('SyApp\Module\Index\Controller\Test', EntryUtil::controller('index', 'test'));
	}

	public function testInitAliasFromDefaultConfig() {
		EntryUtil::initAlias();
		$aliases = $this->getAliases();
		// database=mysql from test app config
		$this->assertEquals(Mysql::class, $aliases[DBInterface::class]);
		// template engine from test app config
		$this->assertEquals(Template::class, $aliases[TemplateInterface::class]);
		// cache.type is not configured
		$this->assertArrayNotHasKey(CacheInterface::class, $aliases);
		$this->assertArrayNotHasKey(PsrCacheInterface::class, $aliases);
	}

	public function testInitAliasWithCustomConfig() {
		App::$config = new Arr([
			'product' => [
				'namespace' => 'SyApp\\',
				'database' => 'sqlite',
				'cache' => ['type' => 'file'],
				'template' => ['engine' => Template::class],
				'charset' => 'UTF-8',
			],
		]);
		EntryUtil::initAlias();
		$aliases = $this->getAliases();
		$this->assertEquals(Sqlite::class, $aliases[DBInterface::class]);
		$this->assertEquals(File::class, $aliases[CacheInterface::class]);
		$this->assertEquals(File::class, $aliases[PsrCacheInterface::class]);
		$this->assertEquals(Template::class, $aliases[TemplateInterface::class]);
	}
}
