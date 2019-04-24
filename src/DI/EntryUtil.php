<?php
/**
 * 部分特殊内容转换
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category DI
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2015-2019 ShuangYa
 * @license https://syframework.sylibs.com/license.html
 */
namespace Sy\DI;

use Sy\App;

class EntryUtil {
	public static function controller($module, $controller) {
		return App::$cfgNamespace . 'Module\\' . $module . '\\Controller\\' . ucfirst($controller);
	}
}