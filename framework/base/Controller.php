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

class Controller {
	protected $_m = [];
	/**
	 * 加载Model
	 * @access protected
	 * @param string $modelName
	 * @param string $loadAs
	 */
	protected function load_model($modelName, $loadAs) {
		//是否已经加载
		if (in_array($modelName, $this->_m, TRUE)) {
			return;
		}
		//load
		$appDir = Sy::$appDir;
		$fileName = $appDir . 'models/' . $modelName . '.php';
		if (!is_file($fileName)) {
			throw new SYException('Model ' . $fileName . ' 不存在', '10004');
		}
		require ($fileName);
		$this->_m[] = $modelName;
		//Model名称
		$m_file = 'M' . ucfirst($modelName);
		$this->$loadAs = $m_file::_i();
	}
}
