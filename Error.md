# 错误代码

* 10001 初始化Sy::createApplication时，未传入$config BaseSY.php

* 10002 初始化Sy::createApplication时，传入文件路径，但文件不存在 BaseSY.php

* 10003 初始化Sy::createApplication时，传入了一个不能识别的配置 BaseSY.php

* 10004 自动路由表时，config中存在Controller，但文件无法找到 BaseSY.php

* 10010 无法找到Model文件，此异常由应用引起 base/Controller.php

* 10011 使用了i18n类，但无法找到语言文件 base/i18n.php

* 10020 需要MySQLi扩展 lib/YMysqli.php

* 10021 需要PDO扩展 lib/YPdo_mysql.php

* 10022 需要phpredis扩展 lib/YRedis.php

* 10030 尝试发送一封没有内容的邮件 tool/YMail.php