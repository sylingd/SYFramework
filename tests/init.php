<?php
define('SY_UNIT', 1);
require (__DIR__ . '/../framework/sy.php');
$config = __DIR__ . '/../application/config.php';
Sy::createApplication(__DIR__, $config);
