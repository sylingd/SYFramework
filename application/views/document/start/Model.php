<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<html>
<head>
	<title>编写Model</title>
	<?php Sy::view('common/header'); ?>
</head>
<body>
	<div class="container">
		<h1>编写Model</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">总则</h3>
			</div>
			<div class="panel-body">
				<p>Model的命名空间一律为<code>应用命名空间\model</code></p>
				<p>Model类名首字母一律大写，例如<code>Member</code></p>
			</div>
		</div><!-- /.panel -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Model规范</h3>
			</div>
			<div class="panel-body">
				<p>Model一般用于接受Controller的调用，并与数据库等进行实际的交互</p>
				<p>一般一个Model只操作一张表</p>
				<p>授权校验一般不在Model层进行</p>
			</div>
		</div><!-- /.panel -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Model内容</h3>
			</div>
			<div class="panel-body">
				<p>Model必须包含以下代码，用于实现单例化</p>
				<pre>protected static $_instance = NULL;
public static function i() {
	if (static::$_instance === NULL) {
		static::$_instance = new static;
	}
	return static::$_instance;
}</pre>
				<p>Controller可包含一个名为<code>__construct</code>、作用域为<code>public</code>的方法，用于初始化</p>
			</div>
		</div><!-- /.panel -->
	</div>
	<?php Sy::view('common/footer'); ?>
</body>
</html>