<?php
namespace SyTest\Http;

use PHPUnit\Framework\TestCase;
use Sy\Http\Dispatcher;
use Sy\Http\Request;
use Sy\Plugin;

class DispatcherTest extends TestCase {
	public function setUp(): void {
		Plugin::clear();
		Dispatcher::init();
	}

	public function tearDown(): void {
		Plugin::clear();
	}

	private function makeRequest($controller, $action) {
		$request = new Request();
		$request->server['REQUEST_METHOD'] = 'GET';
		$request->server['REQUEST_URI'] = '/';
		$request->module = 'index';
		$request->controller = $controller;
		$request->action = $action;
		return $request;
	}

	private function dispatch($request) {
		ob_start();
		Dispatcher::dispatch($request);
		return ob_get_clean();
	}

	public function testDispatchString() {
		$output = $this->dispatch($this->makeRequest('sample', 'index'));
		$this->assertEquals('hello', $output);
	}

	public function testDispatchJson() {
		$output = $this->dispatch($this->makeRequest('sample', 'json'));
		$this->assertEquals('{"a":1}', $output);
	}

	public function testDispatchNullCallsEnd() {
		// nullAction returns null, then end() tries to render a not-exists view
		$output = $this->dispatch($this->makeRequest('sample', 'null'));
		$this->assertEquals('', $output);
	}

	public function testDispatchWithExtension() {
		$request = $this->makeRequest('sample', 'index');
		$request->extension = 'html';
		$output = $this->dispatch($request);
		$this->assertEquals('hello', $output);
	}

	public function testDispatchException() {
		// boomAction throws, default error template rendered (product env)
		$output = $this->dispatch($this->makeRequest('sample', 'boom'));
		$this->assertNotEquals('', $output);
	}

	public function testNotFoundModule() {
		$request = $this->makeRequest('sample', 'index');
		$request->module = 'not-exists';
		$output = $this->dispatch($request);
		$this->assertNotEquals('', $output);
	}

	public function testNotFoundController() {
		$output = $this->dispatch($this->makeRequest('not-exists', 'index'));
		$this->assertNotEquals('', $output);
	}

	public function testNotFoundAction() {
		$output = $this->dispatch($this->makeRequest('sample', 'not-exists'));
		$this->assertNotEquals('', $output);
	}

	public function testBeforeDispatchPluginCanStop() {
		Plugin::register('beforeDispatch', function () {
			return true;
		});
		$output = $this->dispatch($this->makeRequest('sample', 'index'));
		$this->assertEquals('', $output);
	}

	public function testDispatchFailedPluginCanHandleNotFound() {
		Plugin::register('dispatchFailed', function () {
			echo 'handled';
			return true;
		});
		$output = $this->dispatch($this->makeRequest('not-exists', 'index'));
		$this->assertEquals('handled', $output);
	}
}
