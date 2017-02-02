<?php

/**
 * 应用设置
 * 
 * @author ShuangYa
 * @package Demo
 * @category Config
 * @link http://www.sylingd.com/
 */

return [
	//项目名称，不同项目请保证此处不相同
	'name' => 'demo',
	'appNamespace' => 'demo',
	//调试模式
	'debug' => TRUE,
	//App根目录
	'dir' => __DIR__,
	//编码
	'charset' => 'utf-8',
	//默认语言
	'language' => 'zh-CN',
	//加密Key，被YSecurity::securityCode使用
	'cookieKey' => 'test',
	//加密Key，被YSecurity::password使用
	//请在开发过程中定下，实际过程中修改可能导致不可预料的后果
	'securityKey' => 'test',
	//是否默认开启CSRF验证
	'csrf' => FALSE,
	//路由相关配置
	'router' => [
		'type' => 'supervar',
		'module' => 'index',
		'modules' => ['admin', 'index']
	],
	//是否启用URL重写
	'rewrite' => [
		'enable' => TRUE,
		'ext' => 'html', //URL后缀，仅rewrite启用时有效
		//自定义重写规则此处@root作用与YHtml::css中@root作用相同
		'rule' => [
			// 'item_view_comment' => '@root/item/{{id}}/comment.html'
		],
	],
	//会被Autoload加载的class列表
	'class' => [
		'demo\libs\option' => '@app/libs/option.php'
	],
	//console支持
	//格式：['console函数/方法所在文件', '初始化函数（支持格式同call_user_func）']
	'console' => [
		'default' => ['worker.php', 'Worker::init'],
		'test' => ['worker.php', 'Worker::test']
	],
	//Cookie相关
	'cookie' => [
		'prefix' => '',
		'expire' => 7200,
		'path' => '@app/',
		'domain' => $_SERVER['HTTP_HOST']
	],
	//Redis支持
	'redis' => [
		'host' => '127.0.0.1',
		'port' => '6379',
		'password' => '',
		'prefix' => 'pre_'
	],
	//MongoDB支持
	'mongo' => [
		'host' => '127.0.0.1',
		'port' => '6379',
		'user' => '',
		'password' => '',
		'prefix' => 'pre_'
	],
	//MySQL支持
	'mysql' => [
		'host' => '127.0.0.1',
		'port' => '3306',
		'user' => 'root',
		'password' => 'root',
		'name' => 'test',
		'prefix' => 'pre_'
	],
	//SQLite支持
	'sqlite' => [
		'version' => 'sqlite3',
		'path' => '@app/data/db.sq3'
	]
];