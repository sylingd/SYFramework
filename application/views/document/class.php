<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<head>
	<title><?=$class['base']['name']?>类</title>
	<?=YHtml::meta()?>
	<?=YHtml::css('@root/public/css/bootstrap.min.css')?>
</head>
<body>
	<h1><?=$class['base']['name']?>类</h1>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">基本描述</h3>
		</div>
		<div class="panel-body">
			<ul>
				<li>文件：<?=$file?>.php</li>
				<li>命名空间：<?=$class['base']['namespace']?></li>
			</ul>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">函数列表</h3>
		</div>
		<div class="panel-body">
			<ul>
				<li>文件：<?=$file?>.php</li>
				<li>命名空间：<?=$class['namespace']?></li>
			</ul>
		</div>
	</div>
	<?php
	foreach ($class['method'] as $method) {
	?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><a name="method_<?=$method['name']?>"></a><?=$method['title']?>：<?=$method['name']?></h3>
		</div>
		<div class="panel-body">
			<h4>描述</h4>
			<p><?=$method['description']?></p>
			<h4>授权</h4>
			<p><?=$method['access']?></p>
			<h4>参数</h4>
			<?php
			if (isset($method['param'])) {
				
			} else {
				echo '<p>无</p>';
			}
			?>
			<h4>返回</h4>
			<?php
			if (isset($method['return'])) {
				
			} else {
				echo '<p>无</p>';
			}
			?>
			<h4>备注</h4>
			<?php
			if (isset($method['other'])) {
				
			} else {
				echo '<p>无</p>';
			}
			?>
		</div>
	</div>
	<?php
	}
	?>
</body>