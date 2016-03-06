# SYFramework

此框架是原为泷涯自用，现面向大众开源

目前最新版本为`1.2`

此项目基于[Apache License 2.0](http://opensource.org/licenses/Apache-2.0)开源

**extensions为特别定制功能（例如微信），根据需求拷贝相应目录到`framework/tool`下即可使用（部分开源协议可能有所不同，使用时请注意）**

# 环境需求

* php 5.4及以上 非安全模式（PS.部分组件在安全模式下仍可运行，但并不意味着框架兼容安全模式）

**以下列表中，只有你需要使用相关功能时，才必须安装**

* lib\db\YMysqli MySQLi扩展

* lib\db\YMysql PDO扩展和PDO_MySQL驱动

* lib\db\YSqlite PDO扩展和PDO_SQLite驱动

* lib\db\YRedis [phpredis](https://github.com/phpredis/phpredis)

* lib\db\YMongo Mongo扩展

* lib\db\YMongoDB MongoDB扩展

* tool\YCaptcha GD库

# 其他

* 查看更多帮助文档请点击Wiki

* Bug反馈/建议/问题咨询请提交issue

* 本框架自带一示例应用，内容是文档

# ChangeLog

## 1.2

* 修改Controller和Model的命名方式

[详细](update1.md#1.2)

## 1.1

* 移除YMysqli、YMysql、YSqlite的`key`参数

* 支持自动加载namespace匹配的应用自有类库

* 移除部分老旧函数

[详细](update1.md#1.1)

## 1.0

* SYFramework开启版本号更新，每次更新都会写明更新内容和不向下兼容的更新

# 鸣谢

* 整体构架和功能设计参考了[Yii](http://www.yiiframework.com/)和[CodeIgniter](http://codeigniter.com)

* YCaptcha参考了项目[ImageVerifyCode](https://git.oschina.net/reevy/ImageVerifyCode)的代码

* YMail参考了[PHPMailer](https://github.com/PHPMailer/PHPMailer)的部分代码（尚未实现）