# 错误代码

* 10000 未知错误

* 10001 初始化Sy::createApplication时，传入了一个不能识别的配置 App.php

* 10005 使用Sy::createConsoleApplication，但环境不是CLI模式 BaseSY.php

* 10006 使用HTTP2但是没有合适的证书文件 BaseSY.php

* 10010 无法找到Service文件，此异常由应用引起 Stratified.php

* 10011 无法找到DAO文件，此异常由应用引起 Stratified.php

* 10012 无法找到Controller文件，此异常由应用引起 Stratified.php

* 10013 使用了i18n类，但无法找到语言文件 base/i18n.php

* 10020 需要MySQLi扩展 lib/db/YMysqli.php lib/async/YMysql.php

* 10021 需要PDO扩展 base/YPdo.php

* 10022 需要phpredis扩展 lib/db/YRedis.php

* 10023 需要FTP扩展 tool/YFtp.php

* 10024 需要Mongo扩展 lib/db/YMongo.php

* 10025 需要Memcached扩展 lib/cache/YMemcached.php

* 10026 需要MongoDB扩展 lib/db/YMongoDB.php

* 10040 无法连接到FTP服务器 tool/YFtp.php

* 10041 无法登录到FTP服务器 tool/YFtp.php

* 10050 需要CURL或fsockopen lib/YFetchURL.php

* 10051 CURL返回错误 lib/YFetchURL.php

* 10052 无效URL lib/YFetchURL.php

* 10053 socket连接失败 lib/YFetchURL.php

* 10054 socket连接超时 lib/YFetchURL.php

* 10060 需要Swoole扩展（1.8.6+） swoole/*.php

* 10061 未知任务类型 swoole/Server.php

* 10062 配置文件出错 swoole/Server.php

# 扩展错误

* 20000 微信扩展相关错误