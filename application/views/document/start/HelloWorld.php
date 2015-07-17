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
	<h1>Hello World</h1>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Step1.建立应用</h3>
		</div>
		<div class="panel-body">
			<ol>
				<li>在<code>index.php</code>的同级目录下新建一个文件夹，这里可以取名叫<code>myapp</code></li>
				<li>复制<code>application/config.php</code>到myapp下，打开<code>config.php</code>，解释如下：</li>
				<pre>return [
	//应用名称，可随意修改
	'appName' => 'Demo',
	//调试模式，一般来说，开发的时候设置为TRUE，实际部署设置为FALSE
	'debug' => TRUE,
	//App根目录，相对于framework目录，这里就应该是'../myapp/'
	'dir' => '../myapp/',
	//编码，一般来说不用修改
	'charset' => 'utf-8',
	//语言
	'language' => 'zh-CN',
	//是否启用URL重写，根据各自需要修改
	'rewrite' => FALSE,
	//URL后缀，仅rewrite启用时有效
	'rewriteExt' => 'html',
	//自定义重写规则
	//此处@root作用与YHtml::css中@root作用相同
	'rewriteRule' => [
		'article/view' => '@root/article/view/{{id}}.html',
		'article/list' => '@root/article/list/{{id}}-{{page}}.html',
		'user/view' => 'member/view-{{id}}.html'
	],
	//Controller列表，只有存在于列表中的，才会被调用
	'controller' => [
		'home'
	],
	//默认的Router，当直接访问index.php时使用
	'defaultRouter' => 'home/hello',
	//会被Autoload加载的class列表
	'class' => [
		'demo\libs\option' => '@app/libs/option.php'
	],
	//虚拟路由表
	//例如'user'=>'eu_user'
	'alias' => [
	],
	'redis' => [
		'host' => '127.0.0.1',
		'port' => '6379',
		'password' => '',
		'prefix' => 'pre_'
	],
	'mysql' => [
		'host' => '127.0.0.1',
		'port' => '3306',
		'user' => 'root',
		'password' => 'root',
		'name' => 'test',
		'prefix' => 'pre_'
	]
];</pre>
			</ol>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Step2.编写Controller和模板</h3>
		</div>
		<div class="panel-body">
			<ol>
				<li>在<code>myapp</code>下建立两个目录：<code>view、controllers</code></li>
				<li>在<code>controllers</code>下新建一个文件，取名为<code>home.php</code>，<strong>并加入到<code>config.php</code>的controller列表中去</strong>，修改文件，写入以下内容：</li>
				<pre><?=YHtml::encode('<?php')?>

use \sy\base\Controller;
use \sy\lib\YHtml;
//注意：Controller类命名规则为：大写字母C+首字母大写的Controller名
//例如本例中，Controller名为home，则Controller类名为CHome
class CHome extends Controller {
	public function __construct() {
	}
	//供公开调用的方法必须以action开头，action后连接<strong>首字母大写</strong>的方法名
	public function actionHello() {
		//必须：发送Content-type的header，避免乱码和其他情况
		Sy::setMimeType('html');
		//加载模板，home/hello对应的是view/home/hello.php，以此类推
		Sy::view('home/hello');
	}
}
</pre>
			</ol>
		</div>
	</div>
</body>