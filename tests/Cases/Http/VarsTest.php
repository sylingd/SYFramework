<?php
namespace SyTest\Http;

use PHPUnit\Framework\TestCase;
use Sy\Http\Vars;

class VarsTest extends TestCase {
	public function testGetMimeType() {
		$this->assertEquals('text/html', Vars::getMimeType('html'));
		// case-insensitive
		$this->assertEquals('text/html', Vars::getMimeType('HTML'));
		$this->assertEquals('application/json', Vars::getMimeType('json'));
		$this->assertNull(Vars::getMimeType('ext-not-exists'));
	}

	public function testGetStatus() {
		$this->assertEquals('HTTP/1.1 404 Not Found', Vars::getStatus(404));
		$this->assertEquals('HTTP/1.1 302 Found', Vars::getStatus('302'));
		// unknown status code has no text
		$this->assertEquals('HTTP/1.1 999', Vars::getStatus(999));
	}

	public function testGetStatusProtocol10() {
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
		try {
			$this->assertEquals('HTTP/1.0 404 Not Found', Vars::getStatus(404));
		} finally {
			unset($_SERVER['SERVER_PROTOCOL']);
		}
	}

	public function testSetMimeType() {
		// header() is a no-op in CLI, ensure the mime type resolution does not throw
		Vars::setMimeType('html');
		Vars::setMimeType('json');
		Vars::setMimeType('ext-not-exists');
		$this->expectNotToPerformAssertions();
	}
}
