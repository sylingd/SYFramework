<?php
return [
	'product' => [
		'namespace' => 'SyApp\\',
		'database' => 'mysql',
		'router' => [
			'extension' => true
		],
		'template' => [
			'engine' => 'Sy\\Http\\Template',
			'auto' => true,
			'extension' => 'phtml'
		],
		'cache' => [
			'path' => '@TMP',
			'prefix' => 'sy_'
		],
		'redis' => [
			'host' => '127.0.0.1',
			'port' => '6379'
		],
		'mysql' => [
			'host' => '127.0.0.1',
			'port' => 3306,
			'user' => 'root',
			'password' => 'root',
			'database' => 'test'
		],
		'memcached' => [
			'host' => '127.0.0.1',
			'port' => '11211'
		],
		'modules' => ['index'],
		'module' => 'index',
		'charset' => 'UTF-8'
	]
];