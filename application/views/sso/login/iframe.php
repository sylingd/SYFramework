<!DOCTYPE html>
<head>
	<title>iframe</title>
	<?=YHtml::meta()?>
	<?=YHtml::css('@root/static/css/bootstrap.min.css')?>
	<?=YHtml::css('@root/static/css/style.css')?>
</head>
<body>
	<div>
		<h3 class="text-center">登录</h3>
		<input type="text" class="form-control" id="user" placeholder="用户名"/>
		<input type="password" class="form-control" id="password" placeholder="密码"/>
	</div>
	<?=YHtml::js('@root/static/js/jquery.min.js')?>
	<?=YHtml::js('@root/static/js/script.js')?>
</body>