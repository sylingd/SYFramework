<?php

/**
 * 用于验证格式的正则表达式
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Data
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

return [
	'email' => "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",
	'ipv4' => function ($ip) {
		$ip = explode('.', $ip);
		if (count($ip) !== 4) {
			return FALSE;
		}
		foreach ($ip as $one) {
			if ($one == '' || !is_int($one) || (int)$one > 255 || (int)$one < 0) {
				return FALSE;
			}
		}
		return TRUE;
	}
	, 'ipv6' => function ($str) {
		$collapsed = FALSE;
		$chunks = array_filter(preg_split('/(:{1,2})/', $str, NULL, PREG_SPLIT_DELIM_CAPTURE));
		if (current($chunks) == ':' || end($chunks) == ':') {
			return FALSE;
		}
		while ($seg = array_pop($chunks)) {
			if ($seg[0] == ':') {
				if (strlen($seg) > 2) {
					return FALSE;
				}
				if ($seg == '::') {
					if ($collapsed) {
						return FALSE;
					}
					$collapsed = TRUE;
				}
			} elseif (preg_match("/[^0-9a-f]/i", $seg) || strlen($seg) > 4) {
				return FALSE;
			}
		}
		return $collapsed;
	}
	, 'date' => function($date) {
		if (!preg_match('/^([\d]{4})-([\d]{1,2})-([\d]{1,2})$/', $date)) {
			return FALSE;
		}
		if (strtotime($date) === FALSE) {
			return FALSE;
		}
		return TRUE;
	}
];
