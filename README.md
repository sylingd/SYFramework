[English](README_en.md)

# SYFramework

此框架是原为泷涯自用，现开源

目前最新稳定版本为`1.5`，最新测试版本为`2.0.2 Beta`

此项目基于[Apache License 2.0](http://opensource.org/licenses/Apache-2.0)开源

[查看文档](http://framework.sylibs.com)

**extensions为特别定制功能（例如微信），根据需求拷贝相应目录到`framework/tool`下即可使用（部分开源协议可能有所不同，使用时请注意）**

# 环境需求

* php 5.4及以上

**以下列表中，只有你需要使用相关功能时，才必须安装**

* lib\db\YMysqli MySQLi扩展

* lib\db\YMysql PDO扩展和PDO_MySQL驱动

* lib\db\YSqlite PDO扩展和PDO_SQLite驱动

* lib\db\YPg PDO扩展和PDO_PostgreSQL驱动

* lib\db\YRedis [phpredis](https://github.com/phpredis/phpredis)

* lib\db\YMongo Mongo扩展

* lib\db\YMongoDB MongoDB扩展

* tool\YCaptcha GD库

# 其他

* Bug反馈/建议/问题咨询请提交issue

* 本框架自带一示例应用

# ChangeLog

## 2.0.3 Beta

* Add.参数方式运行ConsoleApp

* Fix.YSecurity判断错误

* Fix.YCookie的失效时间不为默认时设置失败

* Fix.不再每次请求均重新生成csrf_token

**此版本暂不稳定，不推荐用于生产环境。更新指南将于正式版发布时一同发布**

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

**此版本暂不稳定，不推荐用于生产环境。更新指南将于正式版发布时一同发布**

## 2.0.1 Beta

* Fix.部分header/Cookie相关函数在HttpServer下的工作

**此版本暂不稳定，不推荐用于生产环境。更新指南将于正式版发布时一同发布**

## 2.0 Beta

* Add.HttpServer支持（通过swoole实现）

**此版本暂不稳定，不推荐用于生产环境。更新指南将于正式版发布时一同发布**

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

* 整体构架和功能设计参考了[Yii](http://www.yiiframework.com/)和[CodeIgniter](http://codeigniter.com)

* YCaptcha参考了项目[ImageVerifyCode](https://git.oschina.net/reevy/ImageVerifyCode)的代码

* YMail参考了[PHPMailer](https://github.com/PHPMailer/PHPMailer)的部分代码（尚未实现）