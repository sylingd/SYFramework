<!DOCTYPE html>
<head>
	<title>Hello</title>
	<?=YHtml::meta()?>
	<?=YHtml::css('@root/public/css/demo.css')?>
</head>
<body>
	<h1>I am Hello</h1>
	<p><?=$url?></p>
	<p><?=YOp::_i()->set('I am libs')?></p>
	<p><?=YOp::_i()->get?></p>
</body>