<?php

/**
 * 邮件类
 * 注意：目前为止，此类正在开发中，暂时不能工作，请勿使用
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

//SMTP支持类
class YMail_SMTP {

}

//主类
class YMail {
	protected $mimeType;
	//发送途径
	protected $mailer;
	/**
	 * 构造函数
	 * @access public
	 */
	public function __construct() {
		$this->setMailer();
	}
	/**
	 * 设置邮件类型
	 * @access public
	 * @param string $type 类型，可选：text,html
	 */
	public function setType($type) {
		switch ($type) {
			case 'html':
				$this->mimeType = Sy::getMimeType('html');
				break;
			case 'text':
				$this->mimeType = Sy::getMimeType('txt');
				break;
		}
	}
	/**
	 * 设置发送途径
	 * @access public
	 * @param string $type 可选：smtp，mail（php自带），sendmail，qmail，为空则从配置读取
	 */
	public function setMailer($type = NULL) {
		if ($type === NULL) {
			$type = Sy::$app['mail']['mailer'];
		}
		$this->mailer = $type;
	}
	/**
	 * 发送一封邮件
	 * @access public
	 * @return boolean
	 */
	public function send() {
		// TODO: 增加对QMail和sendmail的支持
		if ($this->mailer === 'mail') {
			
		} elseif ($this->mailer === 'smtp') {
			
		} else {
			throw new SYException('Unknow mailer', '10011');
		}
	}
}
