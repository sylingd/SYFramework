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
	'appName' => 'Demo',
	//调试模式
	'debug' => TRUE,
	//App根目录，相对于framework目录
	'dir' => '../application/',
	//编码
	'charset' => 'utf-8',
	//语言
	'language' => 'zh-CN',
	//加密Key，被YSecurity::securityCode使用
	'securityKey' => 'test',
	//是否默认开启CSRF验证
	'csrf' => FALSE,
	//是否启用URL重写
	'rewrite' => FALSE,
	//URL后缀，仅rewrite启用时有效
	'rewriteExt' => 'html',
	//自定义重写规则
	//此处@root作用与YHtml::css中@root作用相同
	'rewriteRule' => [
		'article/view' => '@root/article/view/{{id}}.html',
		'article/list' => '@root/article/list/{{id}}-{{page}}.html',
		'user/view' => 'member/view-{{id}}.html'
	],
	//Controller列表
	'controller' => [
		'document'
	],
	//默认的Router
	'defaultRouter' => 'document/hello',
	//会被Autoload加载的class列表
	'class' => [
		'demo\libs\option' => '@app/libs/option.php'
	],
	//虚拟路由表
	//例如'user'=>'eu_user'
	'alias' => [
		'doc' => 'document'
	],
	//Cookie相关
	'cookie' => [
		'prefix' => '',
		'expire' => 7200,
		'path' => '@app/',
		'domain' => $_SERVER['HTTP_HOST']
	],
	'redis' => [
		'host' => '127.0.0.1',
		'port' => '6379',
		'password' => '',
		'prefix' => 'pre_'
	],
	'mysql' => [
		'host' => '127.0.0.1',
		'port' => '3306',
		'user' => 'root',
		'password' => 'root',
		'name' => 'test',
		'prefix' => 'pre_'
	]
];