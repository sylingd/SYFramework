<?php
namespace SyTest;

class ConsoleTest extends TestCase {
	public function testConsole() {
		$this->assertEquals(1, $GLOBALS['is_console_run']);
	}
}