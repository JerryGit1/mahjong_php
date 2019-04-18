<?php
/**
 * 创建者 伟大的周鹏斌大王
 * 时间 2017/6/1 22:06
 *初始化所有表
 */
namespace HomeGM\Controller;
use HomeGM\Model;
use HomeGM\Model\AdminModel;

class InitTableController extends BaseController {
    private $model;
    public function index(){
        $this->model=M();
        $this->createAdmin();
        $this->agencyRelation();
        $this->systemMessages();
        $this->systemNotice();
        $this->feedbackMessage();
        $this->player_userInfo();
        $this->player_blacklist();
        $this->player_payRecord();
        $this->player_consumeRecord();
        $this->player_record();
        $this->agency_buyRecord();
    }
        /*管理员 代理基础信息表*/
    private function createAdmin(){
        $tableName="gm_admin_userinfo";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    createTime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    userName VARCHAR (300) comment '用户名',
                    phone VARCHAR(11) comment '手机号',
                    loginName VARCHAR(16) comment '登录名',
                    loginPasswd VARCHAR (16) comment '密码',
                    loginMD5Passwd VARCHAR (32) comment 'MD5密码',
                    login_ip VARCHAR (15) comment '登录IP记录',
                    login_token VARCHAR (32) comment 'MD5token',
                    login_token_out_time TIMESTAMP NULL comment 'MD5token失效时间',
                    grade INT (2) comment '账号等级',
                    head VARCHAR(300) comment '头像ID',
                    binding_playerId INT(9) comment '绑定的用户id',
                    money INT(11)DEFAULT 0 comment '财富',
                    jlist VARCHAR(5000) comment '权限列表',
                    newTime TIMESTAMP NULL comment '最近登录时间',
                    last_failure TIMESTAMP NULL comment '上次密码错误时间',
                    failure_num INT(2) comment '密码错误次数',
                    test_agency INT(11) comment '是否是测试账号',
                    PRIMARY KEY(id)
                 ) AUTO_INCREMENT=10000";
        //执行创建
        $this->createGameTable($sql,$tableName);
        /*实例化 表model*/
        $admin_model = new AdminModel();
        $info=$admin_model->where("grade=8")->find();
        if(!$info){
            //设置究极管理员
            $userInfo["userName"] = "周鹏斌大王";
            $userInfo["phone"] = "13126631266";
            $userInfo["grade"] = 8;
            $userInfo["head"] = "1";
            $userInfo["loginName"] = "AH_zhoudw";
            $userInfo["loginPasswd"] = "13126631266";
            $userInfo["loginMD5Passwd"] =$this->createMD5Password("13126631266","ADMINMD5");
            $userInfo["jlist"] = json_encode(C('MENU_LIST'));
            $admin_model->add($userInfo);
            echo "设置究极管理员1";
        }
        echo "1-管理员 代理基础信息表 ok";
    }
    /*代理关系表*/
    private function agencyRelation(){
        $tableName="gm_agency_relation";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    uId INT comment '代理ID',
                    superUId INT comment '上级代理ID',
                    createTime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        if(!$this->createGameTable($sql,$tableName)){
        }
        echo "2-代理关系表 ok";
    }
    /*系统消息*/
    private function systemMessages(){
        $tableName="gm_game_systemmessages";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    content VARCHAR(200) comment '内容',
                    contact_as VARCHAR(150) comment '联系我们',
                    uId INT comment '发消息的管理员id',
                    state INT comment '状态1/0',
                    createTime TIMESTAMP  NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "3-系统消息表 ok";
    }
    /*系统公告*/
    private function systemNotice(){
        $tableName="gm_game_systemnotice";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    content VARCHAR(200) comment '内容',
                    uId INT comment '管理员id',
                    state INT comment '状态1/0',
                    createTime TIMESTAMP  NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "4-系统公告表 ok";
    }
    /*反馈消息*/
    private function feedbackMessage(){
        $tableName="gm_game_feedbackmessage";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    content VARCHAR(200) comment '内容',
                    uId INT comment '用户ID',
                    phone VARCHAR(11) comment '手机号',
                    createTime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "5-反馈消息表 ok";
    }
    /*玩家信息表*/
    private function player_userInfo(){
        $tableName="gm_player_userinfo";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    uId VARCHAR(50) comment 'openId/用户id',
                    userName VARCHAR(50) comment '用户名',
                    head VARCHAR(200) comment '头像',
                    money INT comment '财富',
                    id_state INT NOT NULL DEFAULT 1 comment '账号状态 0 黑名单 1正常用户',
                    createTime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "6-玩家信息表 ok";
    }
    /*玩家黑名单*/
    private function player_blacklist(){
        $tableName="gm_player_blacklist";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    uId INT comment '内容',
                    createTime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "7-玩家黑名单表 ok";
    }
    /*玩家or代理充值记录*/
    private function player_payRecord(){
        $tableName="gm_player_payrecord";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    p_uId INT comment '玩家/代理游戏ID',
                    money INT(6) comment '充值金额',
                    a_uId INT comment '管理员/代理原始ID',
                    state INT comment '充值类型 1代理给玩家充值 2代理给下级代理充值 3超管自己充值',
                    createTime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "8-玩家or代理充值记录表 ok";
    }
    /*玩家消费记录*/
    private function player_consumeRecord(){
        $tableName="gm_player_consumerecord";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    uId INT comment '玩家/用户ID',
                    money INT(6) comment '充值金额',
                    type INT(1) comment '消费类型',
                    createTime BIGINT(20)  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "9-玩家消费记录表 ok";
    }
    /*代理购买记录*/
    private function agency_buyRecord(){
        $tableName="gm_agency_buyrecord";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    uId INT comment '玩家/用户ID',
                    money INT(7) comment '购买/充值金额',
                    state INT(1) comment '购买状态',
                    createTime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "10-代理购买记录表 ok";
    }
    /*代理购买记录*/
    private function player_record(){
        $tableName="gm_player_record";
        //创建表sql语法
        $sql="CREATE TABLE $tableName
                (
                    id INT NOT NULL AUTO_INCREMENT comment '自增id',
                    roomId INT(8) comment '房间号',
                    p1 INT(7) comment '玩家1',
                    p1Name VARCHAR(100) comment '玩家1昵称',
                    p1score INT(5) comment '玩家1分数',
                    p2 INT(7) comment '玩家2',
                    p2Name VARCHAR(100) comment '玩家2昵称',
                    p2score INT(5) comment '玩家2分数',
                    p3 INT(7) comment '玩家3',
                    p3Name VARCHAR(100) comment '玩家3昵称',
                    p3score INT(5) comment '玩家3分数',
                    p4 INT(7) comment '玩家4',
                    p4Name VARCHAR(100) comment '玩家4昵称',
                    p4score INT(5) comment '玩家4分数',
                    startTime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP  comment '创建时间',
                    PRIMARY KEY(id)
                 )";
        //执行创建
        if(!$this->createGameTable($sql,$tableName)){

        }
        echo "11-代理购买记录表 ok";
    }
    /*建*/
    private function createGameTable($sql,$tableName)
    {
        //需要的表是否存在
        if($this->model->execute("SHOW TABLES LIKE '$tableName'")<=0){
            if($this->model->execute($sql)){
                return 1;
            }
            return 1;
        }
        return 0;
    }
}