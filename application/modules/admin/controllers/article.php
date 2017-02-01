<?php
namespace demo\controller;
use \Sy;
use \sy\base\Controller;
class Article extends Controller {
	public function __construct() {
	}
	public function actionIndex() {
		$this->assign('text', 'Admin');
		$this->display('article/index');
	}
	/**
	 * 测试：验证码
	 */
	public function actionCaptcha() {
		$captcha = \sy\tool\Captcha::_i();
		$captcha->create(180, 50, 'white');
		$captcha->setFont(3);
		$captcha->drawPoint(80);
		$captcha->drawArc(5);
		$captcha->write('abcd');
		Sy::setMimeType('png');
		$captcha->show('png');
	}
	/**
	 * 测试：FetchURL
	 */
	public function actionFetchurl() {
		$f = \sy\lib\FetchURL::i();
		$f->connectMethod = 'fsockopen';
		$f->setopt([
			'url' => 'http://127.0.0.1/',
			'postfields' => [
				'a' => 'aaa',
				'b' => 'bbb',
				'f' => '@' . Sy::$rootDir . 'Error.md'
			]
		]);
		xdebug_start_trace();
		print_r($f->exec());
	}
}
