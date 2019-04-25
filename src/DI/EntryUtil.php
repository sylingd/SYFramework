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
use Sy\DB\DBInterface;
use Sy\DB\Mysql;
use Sy\DB\Postgre;
use Sy\DB\Sqlite;
use Sy\Http\Template;
use Sy\Http\TemplateInterface;

class EntryUtil {
	public static function controller($module, $controller) {
		return App::$cfgNamespace . 'Module\\' . $module . '\\Controller\\' . ucfirst($controller);
	}
	public static function initAlias() {
		$database = App::$config->get('database');
		$db_class = null;
		switch ($database) {
			case 'mysql':
				$db_class = Mysql::class;
				break;
			case 'postgre':
				$db_class = Postgre::class;
				break;
			case 'sqlite':
				$db_class = Sqlite::class;
				break;
		}
		Container::getInstance()->set(DBInterface::class, $db_class);
		$template = App::$config->get('template.engine', Template::class);
		Container::getInstance()->set(TemplateInterface::class, $template);
		Container::getInstance()->setMulti($template, Container::MULTI_CLONE);
	}
}