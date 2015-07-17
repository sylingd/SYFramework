<?php

/**
 * 测试
 * 
 * @author ShuangYa
 * @package Demo
 * @category Controller
 * @link http://www.sylingd.com/
 */

namespace demo\controller;
use \sy\base\Controller;
use \sy\lib\YHtml;
use \euser\libs\option as YOp;

class CTest extends Controller {
	public function __construct() {
	}
	/**
	 * Hello页面
	 */
	public function actionHello() {
		$this->load_model('test', 't');
		$url_to_css = $this->t->foo('@root/public/style.css');
		Sy::setMimeType('html');
		Sy::template('test/hello', ['url' => $url_to_css]);
	}
}
