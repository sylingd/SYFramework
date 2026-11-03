<?php
namespace SyApp\Module\Index\Controller;

use Sy\ControllerAbstract;

class Sample extends ControllerAbstract {
	public function indexAction($request) {
		return 'hello';
	}
	public function jsonAction($request) {
		return ['a' => 1];
	}
	public function nullAction($request) {
		return null;
	}
	public function boomAction($request) {
		throw new \RuntimeException('boom');
	}
}
