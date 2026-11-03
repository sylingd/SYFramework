<?php
namespace SyTest\Http;

use PHPUnit\Framework\TestCase;
use Sy\Http\Cookie;

class CookieTest extends TestCase {
	public function testGetAndExists() {
		$name = uniqid('cookie_');
		$_COOKIE[$name] = 'val';
		$this->assertEquals('val', Cookie::get($name));
		$this->assertNull(Cookie::get($name . '_missing'));
		$this->assertTrue(Cookie::exists($name));
		$this->assertFalse(Cookie::exists($name . '_missing'));
	}

	public function testSetExpires() {
		// session cookie
		Cookie::set(['name' => 'sess', 'value' => 'a', 'expire' => 0]);
		// expire in the past
		Cookie::set(['name' => 'past', 'value' => 'b', 'expire' => -1]);
		// expire in the future
		Cookie::set(['name' => 'future', 'value' => 'c', 'expire' => 3600]);
		// no expire given (session cookie), auto https detection
		Cookie::set(['name' => 'auto', 'value' => 'd']);
		$this->expectNotToPerformAssertions();
	}
}
