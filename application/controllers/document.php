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
		$this->load_model('test', 't');
		$url_to_css = $this->t->foo('@root/public/style.css');
		Sy::setMimeType('html');
		Sy::view('document/hello', ['url' => $url_to_css]);
	}
	/**
	 * 开始页面
	 */
	public function actionStart() {
		Sy::setMimeType('html');
		Sy::view('document/start/'.$_GET['title']);
	}
}
