<?php
/*伟大的周鹏斌大王
 *2017/8/10
*/
return array(
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '47.93.61.29', // 服务器地址
    'DB_NAME'   => 'DB_TANGYUAN', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'up72@2037', // 密码
    'DB_PORT'   => 3306, // 端口
    'DB_PREFIX' => 'gm_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集
    'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
    'DB_PARAMS' =>  array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),/*字段 区分大小写*/
    "STATISTIC_NAME"=>"wsw_X18",
    "STATISTIC_URL"=>"http://47.93.61.29:8999/gmserver/open/us.statisticsearch.index.html",/*获取统计信息*/
    "STATISTIC_ADD_URL"=>"http://47.93.61.29:8999/gmserver/open/us.statistic.index.html",/*添加统计*/
    "ONLINE_ROOM_URL"=>"http://www.aoh5.com/login/open/us.login.currentRooms.html",/*在线房间列表*/
);