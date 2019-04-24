[English](README_en.md)

# SYFramework

此框架是原为泷涯自用，现开源，框架基本遵循Module、Model、Controller、View的分层

目前最新稳定版本为`1.5`，最新测试版本为`2.0.8 Beta`

此项目基于[Apache License 2.0](http://opensource.org/licenses/Apache-2.0)开源

[查看文档](http://framework.sylibs.com)

**extensions为特别定制功能（例如微信），根据需求拷贝相应目录到`framework/tool`下即可使用（部分开源协议可能有所不同，使用时请注意）**

The framework was creating for ShuangYa. Now it is an open source project

The latest stable version is `1.5`, The latest beta version is `2.0.4 Beta`

Open source under [Apache License 2.0](http://opensource.org/licenses/Apache-2.0)

**The files in "extensions" directory are some special functions (such as wechat) You can copy them to `framework/tool` for using (Notice: Some codes is release under other license)**

[View Document](http://framework.sylibs.com)

# 环境需求

* php 5.5及以上

**以下列表中，只有你需要使用相关功能时，才必须安装**

* lib\db\Mysqli MySQLi扩展

* lib\db\Mysql PDO扩展和PDO_MySQL驱动

* lib\db\Sqlite PDO扩展和PDO_SQLite驱动

* lib\db\Pg PDO扩展和PDO_PostgreSQL驱动

* lib\db\Redis [phpredis](https://github.com/phpredis/phpredis)

* lib\db\Mongo Mongo扩展

* lib\db\MongoDB MongoDB扩展

* tool\Captcha GD库

# 其他

* Bug反馈/建议/问题咨询请提交issue

* Please submit issue for Bug feedback, Suggestions and other questions

* 本框架自带一示例应用

* This framework comes with a sample application 

# ChangeLog

**注意：Beta版本不保证稳定，不推荐用于生产环境**

**注意：升级指南只与正式版一同发布**

## 2.0.8 Beta

* Add. 一个简单的模板引擎

* Add. Model自动加载

* Mod. Plugin和Router作为独立的class存在

* Mod. 结构改为Module、Model、Controller、View

* Mod. 移除类库的“Y”前缀

* Del. 所有Swoole特性

## 2.0.7 Beta

* Add. Yaconf和QConf的支持

* Add. 自动使用xdebug进行调试

* Add. TCP、UDP支持更多回调方法

* Add. 简单的心跳包检查

* Fix. YCookie在Swoole下运行不正常

* Fix. CSRF保护在Swoole下运行不正常

* Fix. URL解析在某些特殊情况下不正常

* Fix. 已知语法错误

* Mod. Swoole应用不在自动启动ob

* Mod. Sy::createUrl不再返回包括域名在内的完整URL

## 2.0.6 Beta

* 修改构架

1. 使用Service/DAO/Controller/View分层

2. 所有swoole应用均基于HttpServer创建

3. 用户可以监听不同的端口来提供不同的服务

4. 移除taskObj限制

* Fix.DoraRPC无法工作

* Fix.在控制台程序中使用routeParam

* Fix.一处潜在的安全隐患

## 2.0.5 Beta

* Add.DoraRPC组件

## 2.0.4 Beta

* Add.框架层的简单Hook

* Add.异步任务支持(Swoole)

* Add.完善SSL和HTTP2的支持(HttpServer)

* Add.WorkerStart事件回调支持(HttpServer)

* Fix.GET为空时无法正常处理URL的BUG(HttpServer)

* Mod.请求协议不为HTTP时强制使用CURL(YFetchURL)

* Del.一些老旧特性

## 2.0.3 Beta

* Add.参数方式运行ConsoleApp

* Fix.YSecurity判断错误

* Fix.YCookie的失效时间不为默认时设置失败

* Fix.不再每次请求均重新生成csrf_token

## 2.0.2 Beta

* Add.异步MySQL

* Add.HttpServer可以自定义Rewrite规则（但推荐通过nginx实现）

* Fix.自动csrf没有通过常规方式加入模板

* Fix.生成URL时错误的urlencode

* Fix.ipv6验证函数不工作

* Fix.YMongo在一些情况下返回错误

* Fix.YMysqli移除多余参数$key

* Fix.微信扩展的几个BUG

* Mod.将入口文件移至public目录

* Del.Alias路由

## 2.0.1 Beta

* Fix.部分header/Cookie相关函数在HttpServer下的工作

## 2.0 Beta

* Add.HttpServer支持（通过swoole实现）

## 1.5

* Fix.报错信息异常的BUG

* Fix.YMongo、YMongoDB、YRedis初始化异常的BUG

[详细](update1.md#1-5)

## 1.4

* Fix.securityCode的命名错误

* Fix.CLI模式下的报错信息显示不正常

* Fix.关于SYDBException未找到的BUG

* Fix.YRedis::set和YRedis::get不会处理key的BUG

[详细](update1.md#1-4)

## 1.3

* Add.PostgreSQL支持

* Add.增强对ConsoleApp的支持

* Fix.YMysql和YMysqli中getOne函数返回格式不正确的BUG

* Fix.YMongoDB中存在的几个BUG

[详细](update1.md#1-3)

## 1.2

* Mod.Controller和Model的命名方式

[详细](update1.md#1-2)

## 1.1

* Add.自动加载namespace匹配的应用自有类库

* Del.YMysqli、YMysql、YSqlite的`key`参数

* Del.部分老旧函数

[详细](update1.md#1-1)

## 1.0

* SYFramework开启版本号更新，每次更新都会写明更新内容和不向下兼容的更新