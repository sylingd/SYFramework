# SYFramework

此框架是泷涯自用，有的地方类似Yii

此项目基于[Apache License 2.0](http://opensource.org/licenses/Apache-2.0)开源

**extensions为特别定制功能（例如微信），根据需求拷贝相应目录到framework/tool下即可使用（部分开源协议可能有所不同，使用时请注意）**

# 环境需求

* php 5.4及以上 非安全模式（PS.部分组件在安全模式下仍可运行，但并不意味着框架兼容安全模式）

* 部分组件需要curl库，数量可能较多，不一一列举

# 其他组件所需支持

**以下列表中，只有你需要使用相关功能时，才必须安装**

* lib\db\YMysqli MySQLi扩展

* lib\db\YMysql PDO扩展和PDO_MySQL驱动

* lib\db\YSqlite PDO扩展和PDO_SQLite驱动

* lib\db\YRedis [phpredis](https://github.com/phpredis/phpredis)

* lib\db\YMongo MongoDB驱动

* tool\YCaptcha GD库

# 其他

* 查看更多帮助文档请点击Wiki

* Bug反馈/建议/问题咨询请提交issue

* 本框架自带一示例应用，内容其实是文档

# 鸣谢

**PS：这里的参考真的只是参考，不是抄袭，喷子绕道**

* 整体构架和功能设计参考了[Yii](http://www.yiiframework.com/)和[CodeIgniter](http://codeigniter.com)

* YCaptcha参考了项目[ImageVerifyCode](https://git.oschina.net/reevy/ImageVerifyCode)的代码

* YMail参考了[PHPMailer](https://github.com/PHPMailer/PHPMailer)的部分代码（尚未实现）