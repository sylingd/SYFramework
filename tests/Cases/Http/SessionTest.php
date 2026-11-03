<?php
namespace SyTest\Http;

use PHPUnit\Framework\TestCase;
use Sy\Http\Session;

class SessionTest extends TestCase {
	private $session;

	public function setUp(): void {
		$this->session = new Session();
	}

	public function testId() {
		$this->assertNotEquals('', $this->session->id());
	}

	public function testSetGetHasDelete() {
		$this->session->set('foo', 'bar');
		$this->assertTrue($this->session->has('foo'));
		$this->assertEquals('bar', $this->session->get('foo'));
		$this->session->delete('foo');
		$this->assertFalse($this->session->has('foo'));
		$this->assertNull($this->session->get('foo'));
	}

	public function testArrayAccess() {
		$this->session['k1'] = 'v1';
		$this->assertTrue(isset($this->session['k1']));
		$this->assertEquals('v1', $this->session['k1']);
		unset($this->session['k1']);
		$this->assertFalse(isset($this->session['k1']));
		$this->assertNull($this->session['k1']);
	}

	public function testClear() {
		$this->session->set('a', 1);
		$this->session->set('b', 2);
		$this->session->clear();
		$this->assertFalse($this->session->has('a'));
		$this->assertFalse($this->session->has('b'));
	}
}
