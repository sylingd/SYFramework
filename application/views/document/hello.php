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
	<h1>Hello</h1>
	<p>欢迎来到SYFramework</p>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">目录</h3>
		</div>
		<div class="panel-body">
			<a href="#start">开始</a>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><a name="start"></a>开始</h3>
		</div>
		<div class="panel-body">
			<a href="<?=Sy::createUrl(['document/start', 'title' => 'HelloWorld'])?>">Hello World</a>
			<a href="<?=Sy::createUrl(['document/start', 'title' => 'Base'])?>">基本构架</a>
			<a href="<?=Sy::createUrl(['document/start', 'title' => 'Router'])?>">路由（Router）</a>
		</div>
	</div>
</body>