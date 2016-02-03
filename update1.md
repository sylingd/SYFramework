# 1.1

## 不向下兼容的更新

* 移除YMysqli、YMysql、YSqlite的`key`参数，改为自动返回结果

* 支持自动加载namespace匹配的应用自有类库

* 移除部分老旧函数

## 升级指南

### 涉及以上三个数据库类的，做以下更改：


	//原代码
	$r = YMysql::i()->query('test', $sql, $data)->getAll('test');
	//修改为
	$r = YMysql::i()->query($sql, $data);
	//原代码
	YMysql::i()->query('test', $sql, $data);
	while ($row = YMysql::i()->getArray('test')) {
		//some codes here
	}
	//修改为
	$r = YMysql::i()->query($sql, $data);
	foreach ($r as $row) {
		//some codes here
	}


### 使用自动加载

以`myClass`类为例

* 在config.php中加入应用的namespace：`'appNamespace' => 'demo'`

* 将`myClass.php`放入`libs`目录下

* 在应用中即可使用：`$myClass = new \demo\libs\myClass`

### 不支持的函数和解决方案

* Controller中不再支持`$this->load_model`，改为`$this->loadModel`，此更改仅更名，使用方法等与原有函数完全相同