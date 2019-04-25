<?php
return [
	'product' => [
		'cache' => [
			'path' => '@TMP',
			'prefix' => 'sy_'
		],
		'redis' => [
			'host' => '127.0.0.1',
			'port' => '6379'
		],
		'mysql' => [
			'host' => 'localhost',
			'port' => 3306,
			'user' => 'root',
			'password' => 'root',
			'database' => 'test'
		],
		'memcached' => [
			'host' => 'localhost',
			'port' => '11211'
		],
		'charset' => 'UTF-8'
	]
];