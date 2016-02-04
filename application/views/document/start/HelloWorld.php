<?php
use \sy\lib\YHtml;
?>
<!DOCTYPE html>
<head>
	<title>Hello World</title>
	<?=YHtml::meta()?>
	<?=YHtml::css('@root/public/css/bootstrap.min.css')?>
</head>
<body>
	<div class="container">
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
	'appName' => 'Demo',
	//应用所使用的namespace
	'appNamespace' => 'demo',
	//调试模式
	'debug' => TRUE,
	//App根目录，相对于framework目录
	'dir' => '../application/',
	//编码
	'charset' => 'utf-8',
	//默认语言
	'language' => 'zh-CN',
	//加密Key，被YSecurity::securityCode使用
	'cookieKey' => 'test',
	//加密Key，被YSecurity::password使用
	//请在开发过程中定下，实际过程中修改可能导致不可预料的后果
	'securityKey' => 'test',
	//是否默认开启CSRF验证
	'csrf' => FALSE,
	//是否启用URL重写
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
	//Controller列表
	'controller' => [
		'document',
		'admin/user',
		'admin/setting'
	],
	//默认的Router
	'defaultRouter' => 'document/hello',
	//会被Autoload加载的class列表
	'class' => [
		'demo\libs\option' => '@app/libs/option.php'
	],
	//虚拟路由表
	//例如'user'=>'eu_user'
	'alias' => [
		'doc' => 'document',
		'aduser' => 'admin/user'
	],
	//console支持
	//格式：['console函数/方法所在文件', '初始化函数（支持格式同call_user_func）']
	'console' => ['worker.php', 'Worker::Init'],
	//Cookie相关
	'cookie' => [
		'prefix' => '',
		'expire' => 7200,
		'path' => '@app/',
		'domain' => $_SERVER['HTTP_HOST']
	],
	//Redis支持
	'redis' => [
		'host' => '127.0.0.1',
		'port' => '6379',
		'password' => '',
		'prefix' => 'pre_'
	],
	//MongoDB支持
	'mongo' => [
		'host' => '127.0.0.1',
		'port' => '6379',
		'user' => '',
		'password' => '',
		'prefix' => 'pre_'
	],
	//MySQL支持
	'mysql' => [
		'host' => '127.0.0.1',
		'port' => '3306',
		'user' => 'root',
		'password' => 'root',
		'name' => 'test',
		'prefix' => 'pre_'
	],
	//SQLite支持
	'sqlite' => [
		'version' => 'sqlite3',
		'path' => '@app/data/db.sq3'
	]
];
</pre>
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

namespace demo\controller; //此处的demo须与配置文件中的appNamespace一致
use \Sy;
use \sy\base\Controller;
use \sy\lib\YHtml;
//注意：Controller类命名规则为：首字母大写的Controller名
//例如本例中，Controller名为home，则Controller类名为Home
class Home extends Controller {
	public function __construct() {
	}
	//供公开调用的方法必须以action开头，action后连接<strong>首字母大写</strong>的方法名
	public function actionHello() {
		//必须：发送Content-type的header，避免乱码和其他情况
		Sy::setMimeType('html');
		//加载模板，home/hello对应的是view/home/hello.php，以此类推
		Sy::view('home/hello');
	}
}</pre>
					<li>在<code>view</code>下建立<code>home/hello.php</code>，输入内容：</li>
					<pre>Hello World</pre>
				</ol>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">完成</h3>
			</div>
			<div class="panel-body">
				<ol>
					<li>打开浏览器，即可看到Hello World</li>
				</ol>
			</div>
		</div>
	</div>
</body>