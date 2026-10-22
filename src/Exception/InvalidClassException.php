<?php
/**
 * 尝试创建无效的类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Exception
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2015-2026 ShuangYa
 * @license Apache License 2.0
 */
namespace Sy\Exception;

use Psr\Container\ContainerExceptionInterface;

class InvalidClassException extends Exception implements ContainerExceptionInterface {
}