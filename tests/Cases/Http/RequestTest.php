<?php
namespace SyTest\Http;

use PHPUnit\Framework\TestCase;
use Sy\Http\Request;
use Sy\Http\Session;

class RequestTest extends TestCase {
	private $request;

	public function setUp(): void {
		$this->request = new Request();
	}

	public function testMethodCheck() {
		$methods = [
			'GET' => 'isGet', 'POST' => 'isPost', 'HEAD' => 'isHead',
			'PUT' => 'isPut', 'PATCH' => 'isPatch', 'OPTIONS' => 'isOptions',
			'DELETE' => 'isDelete',
		];
		foreach ($methods as $method => $checker) {
			$this->request->server['REQUEST_METHOD'] = $method;
			foreach ($methods as $otherChecker) {
				$this->assertSame($otherChecker === $checker, $this->request->$otherChecker(), "$otherChecker for $method");
			}
		}
	}

	public function testMagicStore() {
		$this->request->foo = 'bar';
		$this->assertEquals('bar', $this->request->foo);
		$this->assertTrue(isset($this->request->foo));
		unset($this->request->foo);
		$this->assertFalse(isset($this->request->foo));
	}

	public function testMagicReadsRequest() {
		$_REQUEST['magic_key'] = 'from-request';
		$this->assertEquals('from-request', $this->request->magic_key);
		$this->assertTrue(isset($this->request->magic_key));
		// store takes precedence
		$this->request->magic_key = 'from-store';
		$this->assertEquals('from-store', $this->request->magic_key);
		// __unset removes from both store and request
		unset($this->request->magic_key);
		$this->assertFalse(isset($this->request->magic_key));
	}

	public function testRawContent() {
		$this->assertIsString($this->request->rawContent());
	}

	public function testSession() {
		$session = $this->request->session();
		$this->assertInstanceOf(Session::class, $session);
		$this->assertSame($session, $this->request->session());
	}
}
