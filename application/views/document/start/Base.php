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
						<li>BaseSY.php 基本类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'BaseSY'])?>">详细说明</a></li>
						<li>base<ul>
							<li>Controller.php Controller基本类，应用的Controller都继承此类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'base/Controller'])?>">详细说明</a></li>
							<li>SYException.php 异常类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'base/SYException'])?>">详细说明</a></li>
							<li>SYDBException.php 数据库异常类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'base/SYDBException'])?>">详细说明</a></li>
							<li>i18n.php 国际化语言支持类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'base/i18n'])?>">详细说明</a></li>
							<li>YPod.php PDO基本类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'base/YPod'])?>">详细说明</a></li>
						</ul></li>
						<li>lib<ul>
							<li>YForm.php 表单相关类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/YForm'])?>">详细说明</a></li>
							<li>YHtml.php HTML输出相关类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/YHtml'])?>">详细说明</a></li>
							<li>YSecurity.php 安全相关类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/YSecurity'])?>">详细说明</a></li>
							<li>YCookie.php Cookie相关操作 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/YCookie'])?>">详细说明</a></li>
							<li>YFetchURL.php 抓取URL <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/YFetchURL'])?>">详细说明</a></li>
							<li>db 数据库相关<ul>
								<li>YRedis.php Redis支持类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/db/YRedis'])?>">详细说明</a></li>
								<li>YMysqli.php MySQL支持类，使用MySQLi驱动 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/db/YMysqli'])?>">详细说明</a></li>
								<li>YMysql.php MySQL支持类，使用PDO驱动 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/db/YMysql'])?>">详细说明</a></li>
								<li>YSqlite.php SQLite支持类，使用PDO驱动 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/db/YSqlite'])?>">详细说明</a></li>
								<li>YMongo.php MongoDB支持类，使用Mongo扩展驱动 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/db/YMongo'])?>">详细说明</a></li>
								<li>YMongoDB.php MongoDB支持类，使用MongoDB扩展驱动 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/db/YMongoDB'])?>">详细说明</a></li>
							</ul></li>
							<li>cache 缓存相关<ul>
								<li>YMemcached.php Memcached支持类 <a href="<?=Sy::createUrl(['document/class', 'file' => 'lib/cache/YMemcached'])?>">详细说明</a></li>
							</ul></li>
						</ul></li>
						<li>tool<ul>
							<li>YCaptcha.php 用于快速生成验证码图片</li>
							<li>YFtp.php FTP支持</li>
						</ul></li>
						<li>data<ul>
							<li>mimeTypes.php 文件扩展名对应的MimeType</li>
							<li>httpStatus.php HTTP状态码</li>
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