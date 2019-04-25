<?php
/**
 * Coltroller基本类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Base
 * @link https://www.sylibs.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy;

use Sy\App;
use Sy\DI\Container;
use Sy\Http\Security;
use Sy\Http\TemplateInterface;

abstract class ControllerAbstract {
	protected $_sy_auto = true;
	/**
	 * 注册一个模板变量
	 * 
	 * @access public
	 * @param string $k 名称
	 * @param mixed $v 值
	 */
	protected function assign($k, $v) {
		$this->getTemplate()->assign($k, $v);
	}
	/**
	 * 清空模板变量
	 * 
	 * @access public
	 */
	protected function clearAssign() {
		$this->getTemplate()->clearAssign();
	}
	/**
	 * 渲染模板
	 * @access public
	 * @param string $name
	 */
	protected function display($name) {
		if (App::$config->get('csrf')) {
			$this->getTemplate()->assign('_csrf_token', Security::csrfGetHash());
		}
		$clazz = str_replace(App::$cfgNamespace, '', get_class($this), 1);
		$clazz_info = split('\\', $clazz, 3);
		$module = $clazz[1];
		$fullPath = APP_PATH . 'Module/' . $module . '/View/' . $name . '.' . App::$config->get('template.extension', 'phtml');
		echo $this->getTemplate()->render();
	}
	public function disableView() {
		$this->_sy_auto = false;
	}
	public function getTemplate() {
		static $template = null;
		if ($template === null) {
			$template = Container::getInstance()->get(TemplateInterface::class);
		}
		return $template;
	}
	public function end($request) {
		if ($this->_sy_auto && App::$config->get('template.auto', true)) {
			$this->display($request->controller . '/' . $request->action);
		}
	}
}
