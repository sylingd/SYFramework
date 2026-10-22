<?php
/**
 * 循环依赖
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

class CyclicDependencyException extends Exception implements ContainerExceptionInterface {
}