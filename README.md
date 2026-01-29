Maven-PHP-X服务器

===============

PHP托管的Maven存储库。

我为什么开始这个项目

-------------

作为一名专业的Java开发人员，我使用Maven。在公司网络中，有一个maven存储库，里面有我们自己的人工制品。

当我在写一个爱好项目时，我也想有一个maven存储库。可悲的是，Java托管或VPS的成本比我愿意支付的要高。

然而，我的共享php有足够的空间，每月只需花费1欧元。

正因为如此，我去寻找一个在PHP上运行的Maven存储库，......但我找不到。

项目的目的

----------------------

该项目应该提供一个Maven存储库，同时托管在廉价的共享php主机上。

不需要命令行、cron......并非每个php托管都提供所有这些东西。

呈现特征

----------------

* Maven `settings.xml`生成

*多个存储库

*手动人工制品部署

预期功能（列表不完整）

-----------------

*人工制品自动部署

*管理部分

*安全

*下载统计数据

# 如何部署
php8和mysql8.4

根据[sessions.md](system/guide/kohana/sessions.md#table-schema)部署数据库

重命名application/config/database.php.example为database.php并修改数据

修改application/views/gui/deploy/main.php中MkEncrypt调用为你的密码,为空则跳过密码验证

# 改变

-------

1.（2014年1月6日）开源项目，当前功能：多个存储库，手动工件部署，生成“settings.xml”

2.（2014-01-13）弥补README

3. (2026年1月29日) 汉化,支持php8和mysql8.4,新增密码访问部署页面,新增自动生成pom.xml,修复browse路径错误,完善README文件