<?php

/**
 * FTP支持类
 * 
 * @author ShuangYa
 * @package SYFramework
 * @category Tool
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

namespace sy\tool;
use Sy;
use \sy\base\SYException;

class YFtp {
	protected $config;
	protected $link = null;
	/**
	 * 构造函数，自动连接
	 * @access public
	 * @param array $config FTP选项
	 */
	public function __construct($config) {
		if (!function_exists('ftp_connect')) {
			throw new SYException('Ext "FTP" is required', '10023');
		}
		if (!isset($config['port'])) {
			$config['port'] = 21;
		}
		//默认为被动模式
		if (!isset($config['pasv'])) {
			$config['pasv'] = true;
		}
		if (false === ($this->link = ftp_connect($config['host'], $config['port']))) {
			throw new SYException('Can not connect to FTP Server', '10040');
		}
		//登录
		if (isset($config['user'])) {
			if (!ftp_login($this->link, $config['user'], $config['password'])) {
				throw new SYException('Can not login to FTP Server', '10041');
			}
		}
		if ($config['pasv']) {
			ftp_pasv($this->link, true);
		}
		$this->config = $config;
	}
	/**
	 * 切换目录
	 * @access public
	 * @param string $dir
	 * @param boolean $auto_create 是否自动创建
	 * @return boolean
	 */
	public function chdir($dir, $auto_create = false) {
		if (ftp_chdir($this->link, $dir) === false) {
			if ($auto_create) {
				$this->mkdir($dir);
				if (ftp_chdir($this->link, $dir) === false) {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}
	/**
	 * 创建文件夹
	 * @access public
	 * @param string $dir
	 * @param string $permissions 权限
	 * @return	boolean
	 */
	public function mkdir($dir, $permissions = null) {
		if (empty($path)) {
			return false;
		}
		if (ftp_mkdir($this->link, $dir) === false) {
			return false;
		}

		if ($permissions !== null) {
			$this->chmod($dir, $permissions);
		}
		return true;
	}
	/**
	 * 上传文件
	 * @access public
	 * @param string $from 本地文件
	 * @param string $to 目标文件
	 * @param string $mode 传输模式
	 * @param string $permissions 权限
	 * @return boolean
	 */
	public function upload($from, $to, $mode = 'auto', $permissions = null) {
		if (!is_file($from)) {
			return false;
		}
		//自动模式
		if ($mode === 'auto') {
			$ext = $this->getExt($from);
			$mode = $this->getType($ext);
		}
		$mode = ($mode === 'ascii') ? FTP_ASCII : FTP_BINARY;
		if (ftp_put($this->link, $to, $from, $mode) === false) {
			return false;
		}
		if ($permissions !== null) {
			$this->chmod($to, $permissions);
		}
		return true;
	}
	/**
	 * 下载文件
	 * @access public
	 * @param string $from 远程文件
	 * @param string $to 本地文件
	 * @param string $mode 传输模式
	 * @return boolean
	 */
	public function download($from, $to, $mode = 'auto') {
		//自动模式
		if ($mode === 'auto') {
			$ext = $this->getExt($from);
			$mode = $this->getType($ext);
		}
		$mode = ($mode === 'ascii') ? FTP_ASCII : FTP_BINARY;
		if (ftp_get($this->link, $to, $from, $mode) === false) {
			return false;
		}
		return true;
	}
	/**
	 * 重命名/移动一个文件
	 * @access public
	 * @param string $old
	 * @param string $new
	 * @return boolean
	 */
	public function rename($old, $new) {
		if (ftp_rename($this->link, $old, $new) === false) {
			return false;
		}
		return true;
	}
	/**
	 * 删除文件
	 * @access public
	 * @param string $path
	 * @return boolean
	 */
	public function del($path) {
		if (ftp_delete($this->link, $path) === false) {
			return false;
		}
		return true;
	}
	/**
	 * 删除文件夹
	 * @access public
	 * @param string $path
	 * @return	boolean
	 */
	public function rmdir($path) {
		$path = rtrim($path, '/') . '/';
		$list = $this->listDir($path);
		if (!empty($list)) {
			for ($i = 0, $c = count($list); $i < $c; $i++) {
				if (!preg_match('#/\.\.?$#', $list[$i]) && !ftp_delete($this->link, $list[$i])) {
					$this->rmdir($list[$i]);
				}
			}
		}
		if (ftp_rmdir($this->link, $filepath) === false) {
			return false;
		}
		return true;
	}
	/**
	 * 设置权限
	 * @access public
	 * @param string $path	
	 * @param string $permissions
	 * @return	boolean
	 */
	public function chmod($path, $permissions) {
		if (ftp_chmod($this->link, $perm, $path) === false) {
			return false;
		}
		return true;
	}
	/**
	 * 列出指定目录的文件
	 * @access public
	 * @param string $path
	 * @return	array
	 */
	public function listDir($path = '.') {
		return ftp_nlist($this->link, $path);
	}
	/**
	 * 获取文件扩展名
	 * @access protected
	 * @param string $filename
	 * @return	string
	 */
	protected function getExt($filename) {
		return (($dot = strrpos($filename, '.')) === false) ? 'txt' : substr($filename, $dot + 1);
	}
	/**
	 * 获取FTP传输类型
	 * @access protected
	 * @param string $ext
	 * @return	string
	 */
	protected function getType($ext) {
		if (in_array($ext, ['txt', 'text', 'php', 'phps', 'php4', 'js', 'css', 'htm', 'html', 'phtml', 'shtml', 'log', 'xml', 'asp', 'jsp'], true)) {
			return 'ascii';
		} else {
			return 'binary';
		}
	}
	/**
	 * 析构函数，自动断开
	 * @access public
	 */
	public function __destruct() {
		if (is_resource($this->link)) {
			@ftp_close($this->link);
			$this->link = null;
		}
	}
}
