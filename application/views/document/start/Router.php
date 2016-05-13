<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<html>
<head>
	<title>路由（Router） - SYFramework</title>
	<?php Sy::view('common/header'); ?>
</head>
<body>
	<div class="container">
		<h1>路由（Router）</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">基本路由</h3>
			</div>
			<div class="panel-body">
				<p>路由由<code>Controller名/action名</code>构成，支持多级路由，例如：<code>home/hello/world</code></p>
				<p>以<code>home/hello/world</code>为例，程序将会进行下面的操作</p>
				<ul>
					<li>将其解析为<code>home/hello</code>和<code>world</code></li>
					<li>检查Controller列表，查看是否存在此Controller</li>
					<li>检查此Controller的根目录（controllers/home），查看是否存在基本类（_base.php）如存在则自动引入</li>
					<li>从应用目录引入Controller（controllers/home/hello.php）并实例化</li>
					<li>调用方法<code>actionWorld</code></li>
				</ul>
			</div>
		</div><!-- /.panel -->
	</div>
	<?php Sy::view('common/footer'); ?>
</body>
</html>