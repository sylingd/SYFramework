<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<html>
<head>
	<title>使用HttpServer - SYFramework</title>
	<?php Sy::view('common/header'); ?>
</head>
<body>
	<div class="container">
		<h1>使用HttpServer</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">什么是HttpServer</h3>
			</div>
			<div class="panel-body">
				<p>HttpServer通过Swoole实现php的持久化运行，具有以下优点：</p>
				<ul>
					<li>不再依赖于nginx/Apache，可在一定程度上减轻服务器负担</li>
					<li>不同于传统的一个cgi进程对应一个请求，一个Worker进程内可能并行处理多个请求</li>
					<li>不会重复初始化框架、类库</li>
					<li>支持异步模式，所有涉及IO的操作（如数据库存取、文件读写、网址抓取）均可异步进行</li>
					<li>底层为纯C编写，可充分利用服务器资源</li>
				</ul>
				<p>同时也有以下缺点：</p>
				<ul>
					<li>对HTTP协议不保证完美支持，推荐使用nginx作为前端，将请求proxy至php</li>
					<li>相对于传统的php编程，部分地方需要改变以适应异步处理</li>
				</ul>
			</div>
		</div><!-- /.panel -->
	</div>
	<?php Sy::view('common/footer'); ?>
</body>
</html>