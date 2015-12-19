<?php

/**
 * 文档
 * 
 * @author ShuangYa
 * @package Demo
 * @category Controller
 * @link http://www.sylingd.com/
 */

use \sy\base\Controller;
use \sy\lib\YHtml;
class CDocument extends Controller {
	public function __construct() {
	}
	/**
	 * Hello页面
	 */
	public function actionHello() {
		$this->loadModel('test', 't');
		$url_to_css = $this->t->foo('@root/public/style.css');
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
}
