<?php
namespace SyApp;

class Console {
	public static function run() {
		$GLOBALS['is_console_run'] = 1;
	}
}