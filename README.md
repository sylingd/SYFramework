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

* lib\db\YMysqli MySQLi扩展

* lib\db\YMysql PDO扩展和PDO_MySQL驱动

* lib\db\YSqlite PDO扩展和PDO_SQLite驱动

* lib\db\YPg PDO扩展和PDO_PostgreSQL驱动

* lib\db\YRedis [phpredis](https://github.com/phpredis/phpredis)

* lib\db\YMongo Mongo扩展

* lib\db\YMongoDB MongoDB扩展

* tool\YCaptcha GD库

## Requirement

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

# 其他

* Bug反馈/建议/问题咨询请提交issue

* Please submit issue for Bug feedback, Suggestions and other questions

* 本框架自带一示例应用

* This framework comes with a sample application 

* 可能会有人对`2.0.8`的修改很奇怪，为什么会这样？我认为强行上Swoole，虽然能在一定程度上提高运行效率，但是实际上会让此框架变得更加难以维护。因此，我决定全新编写一个基于Swoole的框架，命名为[Yesf](https://git.oschina.net/sy/Yesf)，能够更多的利用上Swoole的特性并不会感到别扭，开发风格也与此框架有很多差别

* Maybe some people will fill 

* [查看ChangeLog](dosc/changelog.md)