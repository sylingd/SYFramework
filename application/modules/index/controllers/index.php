<?php
namespace demo\controller;
use \Sy;
use \sy\base\Controller;
use \sy\base\Router;
class Index extends Controller {
	public function actionIndex() {
		$this->assign('text', 'Hello world');
		$this->assign('url', Router::createUrl(['admin/article/index', 'word' => 'some words']));
		$this->display('index/index');
	}
}