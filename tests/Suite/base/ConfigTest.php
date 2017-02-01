<?php
use PHPUnit\Framework\TestCase;
use \sy\base\Config;

class ConfigTest extends PHPUnit_Framework_TestCase {
	const AppName = 'MyApplication';
	const YafDir = '/web/yaconf';
	public function testAll() {
		//test yaconf
		if (extension_loaded('yaconf')) {
			//copy ini file to yaconf dir
			file_put_contents(self::YafDir . '/' . self::AppName . '.ini', file_get_contents(__DIR__ . '/config_sample.ini'));
			sleep(2);
			$config = new Config(Config::YACONF, self::AppName);
			$this->assertEquals('MyTest', $config->get('test'));
			$this->assertEquals('127.0.0.1', $config->get('MySQL.host'));
			$this->assertEquals(NULL, $config->get('a_null_key'));
		}
		//test php
		$config = new Config(__DIR__ . '/config_sample.php', self::AppName);
		$this->assertEquals('MyTest', $config->get('test'));
		$this->assertEquals('127.0.0.1', $config->get('MySQL.host'));
		$this->assertEquals(NULL, $config->get('a_null_key'));
	}
}