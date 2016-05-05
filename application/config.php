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
	'appNamespace' => 'demo',
	//调试模式
	'debug' => TRUE,
	//App根目录，相对于framework目录
	'dir' => '../application/',
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
		'document',
		'admin/user',
		'admin/setting'
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
		'doc' => 'document',
		'aduser' => 'admin/user'
	],
	//console支持
	//格式：['console函数/方法所在文件', '初始化函数（支持格式同call_user_func）']
	'console' => [
		'default' => ['worker.php', 'Worker::init'],
		'test' => ['worker.php', 'Worker::test']
	],
	//HttpServer
	//通过swoole实现
	'httpServer' => [
		'ip' => '0.0.0.0', //监听IP，仅监听本地为127.0.0.1，监听所有地址为0.0.0.0
		'port' => '80', //监听端口
		'ssl' => [
			'enable' => FALSE, //HTTPS开关
			'key' => 'ssl.key',
			'cert' => 'ssl.cert'
		],
		'http2' => FALSE, //HTTP2协议支持，如果开启HTTP2，则HTTPS也必须开启
		'worker_num' => 4, //守护进程数，详情见http://wiki.swoole.com/wiki/page/275.html
		'global' => FALSE, //自动注册超全局变量，用于保障兼容性，推荐关闭
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