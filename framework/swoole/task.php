<?php

/**
 * 任务相关类
 * 推送任务需要实例化此类
 * 此类禁止继承
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Swoole
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015-2016 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\swoole;

final class task {
	private $type;
	private $data = NULL;
	/**
	 * 构造函数
	 * @access public
	 * @param string $type Task类型
	 * @param mixed $data Task携带的数据，仅支持string/int/float/array/object，不支持resource
	 */
	public function __construct(string $type, $data = NULL) {
		$this->type = $type;
		$this->data = $data;
	}
	/**
	 * getter
	 */
	public function getType() {
		return $this->type;
	}
	public function getData() {
		return $this->data;
	}
}