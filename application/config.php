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
	//是否启用URL重写
	'rewrite' => TRUE,
	//URL后缀，仅rewrite启用时有效
	'rewriteExt' => 'html',
	//自定义重写规则
	//此处@root作用与YHtml::css中@root作用相同
	'rewriteRule' => [
		// 'article/view' => '@root/article/view/{{id}}.html',
		// 'article/list' => '@root/article/list/{{id}}-{{page}}.html',
		// 'user/view' => '/member/view-{{id}}.html'
		'document/start' => '@root/start/{{title}}.html',
		'document/class' => '@root/class/{{f}}.html',
	],
	//反向解析规则，仅HttpServer需要
	'rewriteParseRule' => [
		[
			'#^/class/(.*?).html$#', //匹配规则
			'document/class', //Controller名称
			'f' //参数，与$1、$2等相对
		]
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
	//console支持
	//格式：['console函数/方法所在文件', '初始化函数（支持格式同call_user_func）']
	'console' => [
		'default' => ['worker.php', 'Worker::init'],
		'test' => ['worker.php', 'Worker::test']
	],
	//Swoole基本选项
	'swoole' => [
		'ip' => '0.0.0.0', //监听IP，仅监听本地为127.0.0.1，监听所有地址为0.0.0.0
		'port' => '80', //监听端口
		'cmd' => [ //控制台配置
			'http' => TRUE,
			'tcp' => [
				'ip': '127.0.0.1',
				'port': '9501'
			]
		],
		'pidPath' => '/tmp',
		'http' => [
			'advanced' => [ //关于Swoole的高级选项，一般没有特别说明的，不需要改动
				'daemonize' => TRUE,
				'dispatch_mode' => 3,
				'package_max_length' => 2097152, //1024 * 1024 * 2
				'buffer_output_size' => 3145728, //1024 * 1024 * 3
				'pipe_buffer_size' => 33554432, //1024 * 1024 * 32
				'open_tcp_nodelay' => 1,
				'heartbeat_check_interval' => 5,
				'heartbeat_idle_time' => 10,
				'open_cpu_affinity' => 1,
				'reactor_num' => 2, //建议设置为CPU核数 x 2
				'worker_num' => 4, //守护进程数，详情见http://wiki.swoole.com/wiki/page/275.html
				'task_worker_num' => 2, //Task进程数，详情见http://wiki.swoole.com/wiki/page/276.html
				'max_request' => 0, //必须设置为0
				'task_max_request' => 4000,
				'backlog' => 3000,
				'log_file' => '/tmp/sw_server.log',//swoole系统日志，任何代码内echo都会在这里输出
				'task_tmpdir' => '/tmp/swtasktmp/',//task 投递内容过长时，会临时保存在这里，请将tmp设置使用内存
				'pid_path' => '/tmp/'
			],
			'ssl' => [
				'enable' => FALSE, //HTTPS开关
				'key' => 'ssl.key',
				'cert' => 'ssl.crt'
			],
			'http2' => FALSE //HTTP2协议支持，如果开启HTTP2，则HTTPS也必须开启
		],
		'rpc' => [
			'http' => [ //关于Swoole的高级选项，一般没有特别说明的，不需要改动
				'daemonize' => TRUE,
				'dispatch_mode' => 3,
				'package_max_length' => 2097152, //1024 * 1024 * 2
				'buffer_output_size' => 3145728, //1024 * 1024 * 3
				'pipe_buffer_size' => 33554432, //1024 * 1024 * 32
				'open_tcp_nodelay' => 1,
				'heartbeat_check_interval' => 5,
				'heartbeat_idle_time' => 10,
				'open_cpu_affinity' => 1,
				'reactor_num' => 2, //建议设置为CPU核数 x 2
				'worker_num' => 4, //守护进程数，详情见http://wiki.swoole.com/wiki/page/275.html
				'task_worker_num' => 2, //Task进程数，详情见http://wiki.swoole.com/wiki/page/276.html
				'max_request' => 0, //必须设置为0
				'task_max_request' => 4000,
				'backlog' => 3000,
				'log_file' => '/tmp/sw_server.log',//swoole系统日志，任何代码内echo都会在这里输出
				'task_tmpdir' => '/tmp/swtasktmp/',//task 投递内容过长时，会临时保存在这里，请将tmp设置使用内存
				'pid_path' => '/tmp/'
			],
			'tcp' => [
				'port' => '9567', //监听端口
				'advanced' => [ //关于Swoole的高级选项，一般没有特别说明的，不需要改动
					'open_length_check' => 1,
					'package_length_type' => 'N',
					'package_length_offset' => 0,
					'package_body_offset' => 4,
					'package_max_length' => 2097152, // 1024 * 1024 * 2,
					'buffer_output_size' => 3145728, //1024 * 1024 * 3,
					'pipe_buffer_size' => 33554432, // 1024 * 1024 * 32,
					'open_tcp_nodelay' => 1,
					'backlog' => 3000,
				]
			]
		],
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