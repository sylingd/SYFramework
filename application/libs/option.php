<?php

/**
 * 类库示例
 * 
 * @author ShuangYa
 * @package Demo
 * @category Library
 * @link http://www.sylingd.com/
 */

namespace demo\libs;
use \sy\lib\YHtml;

class option {
	public $option = NULL;
	static $_instance = NULL;
	static public function _i() {
		if (self::$_instance === NULL) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	/**
	 * Set
	 * @access public
	 */
	public function set($a) {
		$this->val = $a;
		return 'Set to ' . YHtml::encode($a);
	}
	/**
	 * Get
	 * @access public
	 */
	public function get() {
		return $this->val;
	}
}
