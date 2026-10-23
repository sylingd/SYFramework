<?php
namespace SyTest\Config\Adapter;

use PHPUnit\Framework\TestCase;
use Sy\App;
use Sy\Config\Adapter\Yaf;

class YafTest extends TestCase {
  public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if (!extension_loaded('yaf')) {
			self::markTestSkipped('跳过：缺少 yaf 扩展');
		}
	}
	public function testAll() {
		$env = App::getEnv();
		App::setEnv('gce');
		// Load
		$config = new Yaf(APP_PATH . 'Config/config.ini');
		$this->assertEquals('gce', $config->get('connection.my.user'));
		$this->assertEquals('/product', $config->get('path2'));
		$this->assertEquals('/base', $config->get('path'));
		$this->assertEquals([
			'host' => '127.0.0.1',
			'port' => '3306',
			'user' => 'gce',
			'password' => 'gce'
		], $config->get('connection.my'));
		$this->assertEquals('127.0.0.1', $config->get('connection.my.host'));
		// Finish
		App::setEnv($env);
	}
}
