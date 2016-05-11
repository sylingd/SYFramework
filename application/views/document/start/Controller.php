<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<html>
<head>
	<title>编写Controller</title>
	<?php Sy::view('common/header'); ?>
</head>
<body>
	<div class="container">
		<h1>编写Controller</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">总则</h3>
			</div>
			<div class="panel-body">
				<p>Controller一律继承于<code>\sy\base\Controller</code></p>
				<p>Controller的命名空间一律为<code>应用命名空间\controller</code></p>
				<p>Controller类名首字母一律大写，例如<code>Admin</code></p>
				<p><b>注意：HttpServer使用时，Controller有所不同，请参考<a href="<?=Sy::createUrl(['document/start', 'title' => 'HttpServer'])?>">此处</a></b></p>
			</div>
		</div><!-- /.panel -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Controller规范</h3>
			</div>
			<div class="panel-body">
				<p>Controller一般用于数据的交互</p>
				<p><b>不推荐在Controller中直接操作数据库</b></p>
			</div>
		</div><!-- /.panel -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Controller内容</h3>
			</div>
			<div class="panel-body">
				<p>Controller可包含一个名为<code>__construct</code>、作用域为<code>public</code>的方法，用于初始化</p>
				<p>Controller中，名称为actionXxx的可以直接被路由调用，例如<code>admin/login</code>路由函数会自动调用Admin类中的<code>actionLogin</code>函数</p>
				<p>Controller中，名称为actionXxx的方法作用域必须为<code>public</code></p>
			</div>
		</div><!-- /.panel -->
	</div>
	<?php Sy::view('common/footer'); ?>
</body>
</html>