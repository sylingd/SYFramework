<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<head>
	<title>路由（Router）</title>
	<?=YHtml::meta()?>
	<?=YHtml::css('@root/public/css/bootstrap.min.css')?>
</head>
<body>
	<div class="container">
		<h1>路由（Router）</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">基本路由</h3>
			</div>
			<div class="panel-body">
				<p>路由由<code>Controller名/action名</code>构成，例如：<code>home/hello</code></p>
				<p>以<code>home/hello</code>为例，程序将会进行下面的操作</p>
				<ul>
					<li>将其解析为<code>home</code>和<code>hello</code></li>
					<li>查找虚拟路由表（后面解释），将home解释为真实路由（如果使用了虚拟路由表）</li>
					<li>检查Controller列表，查看是否存在此Controller</li>
					<li>从应用目录引入Controller并实例化</li>
					<li>调用方法<code>actionHello</code></li>
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