<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<head>
	<title>Hello</title>
	<?=YHtml::meta()?>
	<?=YHtml::css('@root/public/css/bootstrap.min.css')?>
</head>
<body>
	<div class="container">
		<h1>基本构架</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">框架目录和文件</h3>
			</div>
			<div class="panel-body">
				<ul>
					<li>index.php 入口文件，在其中设置应用设置所在目录即可</li>
					<li>framework 框架目录<ul>
						<li>sy.php 入口文件</li>
						<li>BaseSY.php 基本类</li>
						<li>base<ul>
							<li>Controller.php Controller基本类，应用的Controller都继承此类</li>
							<li>SYException.php 异常类</li>
							<li>SYDBException.php 数据库异常类</li>
						</ul></li>
						<li>lib<ul>
							<li>YForm.php 表单相关类</li>
							<li>YHtml.php HTML输出相关类</li>
							<li>YRedis.php Redis支持类</li>
							<li>YMysqli.php MySQL支持类，使用MySQLi驱动</li>
							<li>YPdo_mysql.php MySQL支持类，使用PDO驱动</li>
						</ul></li>
						<li>data<ul>
							<li>mimeTypes.php 文件扩展名对应的MimeType</li>
							<li>validString.php 用于验证字符串的函数和正则表达式</li>
						</ul></li>
					</ul></li>
				</ul>
			</div>
		</div><!-- /.panel -->
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"></h3>
			</div>
			<div class="panel-body">
			</div>
		</div><!-- /.panel -->
	</div>
</body>