<?php

/**
 * 文档
 * 
 * @author ShuangYa
 * @package Demo
 * @category Controller
 * @link http://www.sylingd.com/
 */

namespace demo\controller;

use \Sy;
use \sy\base\Controller;
use \sy\lib\YHtml;
class Document extends Controller {
	public function __construct() {
	}
	/**
	 * Hello页面
	 */
	public function actionHello() {
		// $this->loadModel('test', 't');
		// $url_to_css = $this->t->foo('@root/public/style.css');
		Sy::setMimeType('html');
		Sy::view('document/hello', ['url' => $url_to_css]);
	}
	/**
	 * 开始页面
	 */
	public function actionStart() {
		Sy::setMimeType('html');
		Sy::view('document/start/' . $_GET['title']);
	}
	/**
	 * 类库说明
	 */
	public function actionClass() {
		Sy::setMimeType('html');
		$f = $_GET['f'];
		if (strpos($f, '.') !== FALSE) {
			exit;
		}
		$path = Sy::$appDir . 'data/class/' . $f . '.json';
		if (!is_file($path)) {
			exit;
		}
		$class = json_decode(file_get_contents($path), 1);
		Sy::view('document/class', ['class' => $class, 'file' => $f]);
	}
	/**
	 * 测试：验证码
	 */
	public function actionCaptcha() {
		$captcha = \sy\tool\Ycaptcha::_i();
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
		$f = \sy\lib\YFetchURL::i();
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
