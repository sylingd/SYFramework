<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<html>
<head>
	<title>Hello</title>
	<?php Sy::view('common/header'); ?>
</head>
<body>
	<div class="container">
		<h1>Hello</h1>
		<p>欢迎来到SYFramework</p>
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