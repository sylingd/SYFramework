[简体中文](README.md)

# SYFramework

The framework was creating for ShuangYa. Now it is an open source project

The latest stable version is `1.5`, The latest beta version is `2.0.4 Beta`

Open source under [Apache License 2.0](http://opensource.org/licenses/Apache-2.0)

[View Document](http://framework.sylibs.com)

**The files in "extensions" directory are some special functions (such as wechat) You can copy them to `framework/tool` for using (Notice: Some codes is release under other license)**

# Requirement

* php 5.5 +

**In the following list, You must install the follow extension only when you need to use the functions**

* lib\db\YMysqli MySQLi extension

* lib\db\YMysql PDO extension and PDO_MySQL driver

* lib\db\YSqlite PDO extension and PDO_SQLite driver

* lib\db\YPg PDO extension and PDO_PostgreSQL driver

* lib\db\YRedis [phpredis](https://github.com/phpredis/phpredis)

* lib\db\YMongo Mongo extension

* lib\db\YMongoDB MongoDB extension

* tool\YCaptcha GD extension

# Other

* Please submit issue for Bug feedback, Suggestions and other questions

* This framework comes with a sample application 

# ChangeLog

**Notice: Beta  version is not stable, Please do not using in product environment.**

**Notice:  The release notes only come with the stable version**

## 2.0.7 Beta

* Add. Support for Yaconf and QConf

* Add. Auto switch on xdebug trace while debug mode

* Add. More callback method for TCP&UDP

* Add. A simple heartbeat checker

* Fix. YCookie not work on Swoole

* Fix. CSRF protection not work on Swoole

* Fix. URL parser not work well in some special circumstances

* Fix. Known syntax error

* Mod. Swoole Application will not auto enable ob

* Mod. Sy::createUrl will not return the full URL which is include domain, protocol and port

## 2.0.6 Beta

* Adjustment frame

1. Use Service/DAO/Controller/View to stratify the app

2. All swoole application start with HttpServer

3. User can listen different port to provide different service

4. Remove taskObj limit

* Fix.DoraRPC not work

* Fix.Using routeParam in Console App

* Fix.A security risk

## 2.0.5 Beta

* Add.DoraRPC

## 2.0.4 Beta

* Add.A simple Hook in framework

* Add.Aysnc task support(Swoole)

* Add.Perfect support for SSL and HTTP2(HttpServer)

* Add.WorkerStart event callback support(HttpServer)

* Fix.Can not properly handle URL when GET is empty(HttpServer)

* Mod.Mandatory use curl when URL is not a http protocol link(YFetchURL)

* Del.Some old features

## 2.0.3 Beta

* Add.Run ConsoleApp with param way

* Fix.Error judgment with YSecurity

* Fix.Fail to set Coookie when the expire time is not default(YCookie)

* Fix.Every request are no longer regenerate csrf_token

## 2.0.2 Beta

* Add.Async MySQL Client

* Add.You can defining rewrite rules with HttpServer (But it is better to realize by nginx)

* Fix.Not add _csrf_token to template by normal way when the auto csrf is enable

* Fix.The has something wrong urlencode when using createUrl

* Fix.The ipv6 valid function not work

* Fix.Sometimes YMongo maybe return a wrong result

* Fix.Remove the param $key in YMysqli

* Fix.Some BUGs with WeChat extension

* Mod.Move the entrance to public directory

* Del.Alias route

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

# 鸣谢

* The overall architecture and functional design reference [Yii](http://www.yiiframework.com/) and [CodeIgniter](http://codeigniter.com)

* YCaptcha reference [ImageVerifyCode](https://git.oschina.net/reevy/ImageVerifyCode)