<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<html>
<head>
	<title>SYFramework</title>
	<?php Sy::view('common/header'); ?>
</head>
<body>
	<div class="container">
		<h1>Hello, SYFramework</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Hello, SYFramework</h3>
			</div>
			<div class="panel-body">
				<p>欢迎来到SYFramework</p>
				<p>SYFramework是一款轻量级、灵活的php框架</p>
				<p>此项目基于<a href="https://opensource.org/licenses/Apache-2.0" target="_blank" rel="nofollow">Apache License 2.0</a>开源</p>
				<p><a href="https://github.com/sylingd/SYFramework" target="_blank" rel="nofollow">GitHub</a>&nbsp;<a href="https://git.oschina.net/sy/SYFramework" target="_blank" rel="nofollow">Git@OSC</a></p>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">目录</h3>
			</div>
			<div class="panel-body">
				<a class="btn btn-default" href="#start">开始</a>
				<a class="btn btn-default" href="#class">类文件参考</a>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title" id="start">开始</h3>
			</div>
			<div class="panel-body">
				<a class="btn btn-default" href="<?=Sy::createUrl(['document/start', 'title' => 'HelloWorld'])?>">Hello World</a>
				<a class="btn btn-default" href="<?=Sy::createUrl(['document/start', 'title' => 'Base'])?>">基本构架</a>
				<a class="btn btn-default" href="<?=Sy::createUrl(['document/start', 'title' => 'Router'])?>">路由（Router）</a>
				<a class="btn btn-default" href="<?=Sy::createUrl(['document/start', 'title' => 'Controller'])?>">编写Controller</a>
				<a class="btn btn-default" href="<?=Sy::createUrl(['document/start', 'title' => 'Model'])?>">编写Model</a>
				<a class="btn btn-default" href="<?=Sy::createUrl(['document/start', 'title' => 'HttpServer'])?>">使用HttpServer</a>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title" id="class">类文件参考</h3>
			</div>
			<div class="panel-body">
				<a class="btn btn-default" href="<?=Sy::createUrl(['document/class', 'f' => 'BaseSY'])?>">BaseSY（框架基本类）</a>
			</div>
		</div>
	</div>
	<?php Sy::view('common/footer'); ?>
</body>
</html>