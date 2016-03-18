<?php

/**
 * Worker入口文件
 * 
 * @author ShuangYa
 * @package Forum
 * @category Base
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 */

require (__DIR__ . '/framework/sy.php');

$config = __DIR__ . '/application/config.php';

Sy::createConsoleApplication($config);
