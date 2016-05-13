<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<html>
<head>
	<title><?=$class['name']?>类 - SYFramework</title>
	<?php Sy::view('common/header'); ?>
</head>
<body>
	<div class="container">
		<h1><?=$class['name']?>类</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">基本描述</h3>
			</div>
			<div class="panel-body">
				<ul>
					<li>文件：<?=$file?>.php</li>
					<li>命名空间：<?=$class['namespace']?></li>
				</ul>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">变量</h3>
			</div>
			<div class="panel-body">
				<table class="table table-border">
					<thead><tr><th>名称</th><th>描述</th></tr></thead>
					<tbody>
					<?php
					foreach ($class['var'] as $k => $v) {
						echo '<tr><td>', $k, '</td><td>', $v, '</td></tr>';
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">函数列表</h3>
			</div>
			<div class="panel-body">
				<ul>
					<?php
					$keys = array_keys($class['method']);
					foreach ($keys as $k) {
						echo '<li><a href="#method_', $k, '">', $k, '</a></li>';
					}
					?>
				</ul>
			</div>
		</div>
		<?php
		foreach ($class['method'] as $name => $method) {
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title" id="method_<?=$name?>"><?=$name?>：<?=$method['name']?></h3>
			</div>
			<div class="panel-body">
				<p><b>描述：</b><?=$method['description']?></p>
				<p><b>作用域：</b><?=$method['access']?></p>
				<?php
				if (isset($method['param'])) {
				?>
				<p><b>参数</b></p>
				<table class="table table-border">
					<thead><tr><th>名称</th><th>类型</th><th>描述</th></tr></thead>
					<tbody>
				<?php
					foreach ($method['param'] as $paramName => $paramContent) {
						echo '<tr><td>', $paramName, '</td><td>', $paramContent['type'], '</td><td>', $paramContent['description'], '</td></tr>';
					}
				?>
					</tbody>
				</table>
				<?php
				} else {
					echo '<p><b>参数：</b>无</p>';
				}
				if (isset($method['return'])) {
					echo '<p><b>返回：</b>[ ', $method['return']['type'], ' ] ', $method['return']['description'], '</p>';
				} else {
					echo '<p><b>返回：</b>无</p>';
				}
				?>
			</div>
		</div>
		<?php
		}
		?>
	</div>
	<?php Sy::view('common/footer'); ?>
</body>
</html>