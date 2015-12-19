<?php

/**
 * 多级Controller演示
 * 
 * @author ShuangYa
 * @package Demo
 * @category Controller
 * @link http://www.sylingd.com/
 */

use \sy\base\Controller;
use \sy\lib\YHtml;
class CUser extends Controller {
	public function __construct() {
	}
	/**
	 * Hello页面
	 */
	public function actionHello() {
		echo 'Hello';
	}
}
