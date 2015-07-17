<?php
/**
 * 模块示例
 * 
 * @author ShuangYa
 * @package Demo
 * @category Model
 * @link http://www.sylingd.com/
 */

use \sy\lib\YHtml;

class MTest {
	public function foo($str) {
		return YHtml::encode(YHtml::css($str));
	}
}