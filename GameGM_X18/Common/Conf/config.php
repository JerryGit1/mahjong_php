<?php
// 异常错误报错级别,
return array(
    'ADMINMD5'=>"zhoudw",/*管理员密码md5的字符*/
    'AGENCYMD5'=>"AHZHOU",/*代理密码md5的字符*/
    'DEFAULT_FILTER' =>  'strip_tags,stripslashes',/*全局post get过滤*/
    'MENU_LIST'=>array(/*总的菜单和功能  大功能--二级功能*/
        array("name"=>"console","list"=>[]),
        array("name"=>"jurisdiction","list"=>["list","add"]),
        array("name"=>"game","list"=>["systemNotice","feedback","systemMessages","monitor","onlineRoom"]),
        array("name"=>"player","list"=>["list","blacklist","payRecord","consumeRecord","scoreRecord","pay"]),
        array("name"=>"agency","list"=>["list","add","buyRecord","sellRecord","pay","grade","reduce"]),
        array("name"=>"jerry","list"=>["index","add","userList","pay","recordList","seek"]),
        array("name"=>"fling","list"=>["list","bindingUserList","manage","whitelist","userAffiliation"]),
    ),
    'AGENCY_S_AUTH'=>array(/*S级代理标准权限*/
        array("name"=>"console","list"=>[]),
        array("name"=>"player","list"=>["pay","payRecord"]),
        array("name"=>"jerry","list"=>["index","add","userList","pay","recordList"]),
        array("name"=>"agency","list"=>["list","add","buyRecord","sellRecord","pay"])
    ),
    'AGENCY1_AUTH'=>array(/*代理标准权限*/
        array("name"=>"console","list"=>[]),
        array("name"=>"player","list"=>["pay","payRecord"]),
        array("name"=>"jerry","list"=>["index","add","userList","pay","recordList"]),
        array("name"=>"agency","list"=>["list","buyRecord","sellRecord","pay"])
    ),
    "TABLE"=>array(
        "game_systemmessages"=>array(
            "table_name"=>"GAME_SYSTEM_MESSAGE",/*系统消息。。表*/
            "field"=>array(
                "id"=>"ID",/*字段名字*/
                "content"=>"CONTENT",/*消息 openid*/
                "contact_as"=>"CONTENT_US",/*关于我们*/
                "uId"=>"UID",/*添加者*/
                "state"=>"STATE",/*状态*/
                "createTime"=>"CREATE_TIME",/*创建时间*/
            )
        ),
        "game_feedbackmessage"=>array(
            "table_name"=>"GAME_FEEDBACK",/*玩家反馈。。表*/
            "field"=>array(
                "id"=>"ID",/*字段名字*/
                "content"=>"CONTENT",/*消息 openid*/
                "phone"=>"TEL",/*关于我们*/
                "uId"=>"USER_ID",/*添加者*/
                "createTime"=>"CREATE_TIME",/*创建时间*/
            )
        ),
        "game_systemnotice"=>array(
            "table_name"=>"GAME_NOTICE",/*系统公告跑马灯。。表*/
            "field"=>array(
                "id"=>"ID",/*字段名字*/
                "content"=>"CONTENT",/*消息 openid*/
                "uId"=>"UID",/*添加者*/
                "state"=>"TYPE",/*状态*/
                "createTime"=>"CREATETIME",/*创建时间*/
            )
        ),
        "player"=>array(
            "table_name"=>"GAME_USER",/*用户信息表*/
            "field"=>array(
                "id"=>"ID",/*字段名字*/
                "user_id"=>"USER_ID",/*用户ID*/
                "openId"=>"OPEN_ID",/*用户微信 openid*/
                "userName"=>"USER_NAME",/*用户名*/
                "head"=>"USER_IMG",/*用户头像*/
                "money"=>"MONEY",/*财富 房卡 钻石 等等*/
                "id_state"=>"BLACK",/*状态 0黑名单 1正常用户*/
                "createTime"=>"SIGN_UP_TIME",/*创建时间*/
            )
        ),
        "player_consume"=>array(
            "table_name"=>"gm_player_consumerecord",/*用户消费消耗房卡 钻石。。表*/
            "field"=>array(
                "id"=>"id",/*字段名字*/
                "uId"=>"uId",/*用户id openid*/
                "money"=>"money",/*财富 房卡 钻石 等等*/
                "type"=>"type",/*消费类型 具体游戏具体定义*/
                "createTime"=>"createTime",/*创建时间*/
            )
        ),
        "player_record"=>array(
            "table_name"=>"GAME_PLAY_RECORD",/*用户消费消耗房卡 钻石。。表*/
            "field"=>array(
                "id"=>"ID",/*字段名字*/
                "roomId"=>"ROOM_ID",/*房间号*/
                "p1"=>"EAST_USER_ID",/*用户1*/
                "p1Name"=>"EAST_USER_MONEY_RECORD",/*用户1姓名*/
                "p1Score"=>"EAST_USER_MONEY_REMAIN",/*用户1分数*/

                "p2"=>"SOUTH_USER_ID",
                "p2Name"=>"SOUTH_USER_MONEY_RECORD",
                "p2Score"=>"SOUTH_USER_MONEY_REMAIN",

                "p3"=>"WEST_USER_ID",
                "p3Name"=>"WEST_USER_MONEY_RECORD",
                "p3Score"=>"WEST_USER_MONEY_REMAIN",

                "p4"=>"NORTH_USER_ID",
                "p4Name"=>"NORTH_USER_MONEY_RECORD",
                "p4Score"=>"NORTH_USER_MONEY_REMAIN",

                "startTime"=>"START_TIME",
                "endTime"=>"END_TIME"
            )
        ),
        "game_room"=>array(
            "table_name"=>"GAME_ROOM",/*用户消费消耗房卡 钻石。。表*/
            "field"=>array(
                "id"=>"ID",/*字段名字*/
                "roomId"=>"ROOM_ID",/*房间号*/
                "user_id1"=>"USER_ID1",/*用户1*/
                "user_id2"=>"USER_ID2",/*用户2*/
                "user_id3"=>"USER_ID3",/*用户3*/
                "user_id4"=>"USER_ID4",/*用户4*/
                "online"=>"IS_PLAYING",/*1游戏中 0结束*/
                "userId"=>"CREATE_ID",/*房主id*/
                "circleNum"=>"CIRCLE_NUM",/*圈数*/
                "xiao_ju"=>"XIAO_JU",/*小局数*/
                "createTime"=>"CREATE_TIME",/*时间*/
            )
        )
    )
);
