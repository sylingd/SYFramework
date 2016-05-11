<?php

/**
 * Coltroller基本类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=frameworkr&type=license
 */

namespace sy\base;
use Sy;
use \sy\base\SYException;

abstract class Controller {
	protected $_framework_m = [];
	/**
	 * 加载Model
	 * @access protected
	 * @param string $modelName
	 * @param string $loadAs
	 */
	protected function loadModel($modelName, $loadAs) {
		//是否已经加载
		if (in_array($modelName, $this->_framework_m, TRUE)) {
			return;
		}
		$this->_framework_m[] = $modelName;
		$this->{$loadAs} = Sy::model($modelName);
	}
}
