<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/12/28
 * Time: 18:51
 */
namespace HomeGM\Controller;
use HomeGM\Model\AdminModel;
use HomeGM\Model\PlayerModel;
use HomeGM\Model\payRecordModel;
use HomeGM\Model\ConsumeRecordModel;
use HomeGM\Model\AgencyRelationModel;
class IndexController extends BaseController{
    /*用户名密码安全登陆*/
    public function login(){
        $user_name=$this->getParamInfo("lName",2,true);/*用户名*/
        $password=$this->getParamInfo("lPasswd",2,true);/*密码*/
        $admin_model=new AdminModel();/*实例化admin表*/
        $user_info=$admin_model->login_name($user_name);/*先查找用户名对应的用户信息*/
        if($user_info){
            $init_id=(int)$user_info["id"];//int，数值化
            $this->lookUserLoginAbnormal($user_info["last_failure"],$init_id);/*检测用户是否有登陆次数超限限制*/
            /*MD5密码*/
            $md5_password=$this->createMD5Password($password,"ADMINMD5");
            if($user_info["grade"]<=5)$md5_password=$this->createMD5Password($password,"AGENCYMD5");
            /*对比密码*/
            if($md5_password==$user_info["loginMD5Passwd"]){
                /*返回数据*/
                $this->returnUserInfo($user_info);
            }else{ /*输错5次无论对错都无法登陆*/
                $failure=$user_info["failure_num"];
                if(!$failure)$failure=0;
                $failure++;
                if($failure>=5){ /*超过5次执行 账号登陆限制*/
                    /*更新 failure 时间*/
                    $admin_model->update_failure_time($init_id,time());
                    /*更新 failure次数为0*/
                    $admin_model->update_failure($init_id,0);
                    $this->returnGameData(1,null,"null1");
                }else{
                    /*更新 failure次数*/
                    $admin_model->update_wrong($init_id,$failure);
                }
                $this->returnGameData(1,null,"null2_".$md5_password."-".$user_info["loginMD5Passwd"]);
            }
        }else{
            $this->returnGameData(1,null,"用户不存在");
        }
    }
    //token登录
    public function token_login(){
        $token=$this->getParamInfo("token",2,true);//token缓存登录
        $admin_model=new AdminModel();
        $user_info=$admin_model->token_get_user_info($token);
        if($user_info){
            if($user_info["login_ip"]==get_client_ip()){ //ip校验
                $this->lookUserLoginAbnormal($user_info["last_failure"],$user_info["id"]);//因为是空所以报错。。。。。。。。。
            }else{
                $user_info=null;
            }
        }
        $this->returnUserInfo($user_info);
    }
    /*openid登录*/
    public function open_id_login(){
        $openId=$this->getParamInfo("openId",2);//登录者的为openid
        $admin_model=new AdminModel();
        $player_model=new PlayerModel();
        $user_info=$player_model->open_id_get_user_info($openId);
        if($user_info){ //查玩家列表是否存在
            $admin_user_info=$admin_model->user_id_get_user_info($user_info[$player_model->field["user_id"]]);
            if($admin_user_info){//查admin表是否存在
                /*检测用户是否有登陆次数超限限制*/
                $this->lookUserLoginAbnormal($admin_user_info["last_failure"],$admin_user_info["id"]);
                /*返回数据*/
                $this->returnUserInfo($admin_user_info);
            }else{
                $this->returnGameData(0,null,"您还不是代理，请联系管理员");
            }
        }else{
            $this->returnGameData(0,null,"该用户不存在");
        }
    }
    /*返回用户信息*/
    public function  returnUserInfo($user_info){
        $rData=new \stdClass();
        $record_model=new payRecordModel();
        if($user_info){
            /*用户基础信息*/
            /*用户id*/
            $rData->userInfo->userId=$user_info["binding_playerId"];
            $rData->userInfo->init_id=$user_info["id"];
            /*用户名*/
            $rData->userInfo->userName=$user_info["userName"];
            /*头像*/
            $rData->userInfo->userHead=$user_info["head"];
            /*等级权限*/
            $rData->userInfo->IDLevel=$user_info["grade"];
            /*phone*/
            $rData->userInfo->phone=$user_info["phone"];
            /*财富*/
            $rData->userInfo->userTreasure=$user_info["money"];
            /*总购买*/
            $rData->buyMoney=$record_model->all_buy($user_info["id"]);
            /*phone*/
            $rData->userInfo->loginIp=$user_info["login_ip"];
            /*加入时间*/
            $rData->userInfo->newTime=$user_info["newTime"];
            /*累计购买总数*/
            if($user_info["grade"]==7)$rData->userInfo->buyMoney=$record_model->agency_all_buy_money($user_info["id"],3);
            else $rData->userInfo->buyMoney=$record_model->agency_all_buy_money($user_info["id"],2);
            /*累计出售总数*/
            $rData->userInfo->sell_player_money=$record_model->agency_all_sell_money($user_info["id"],1);
            $rData->userInfo->sell_agency_money=$record_model->agency_all_sell_money($user_info["id"],2);
            /*校验登录token*/
            $rData->userInfo->token=$this->add_login_token($user_info["id"]);
            /*权限信息*/
            $rData->menuList=json_decode($user_info["jlist"]);
            if((int)$user_info["grade"]==8)$rData->menuList=C('MENU_LIST');
            $this->returnGameData(1,$rData);
        }else{
            $this->returnGameData(1,null,"用户不存在");
        }
    }
    /*登录token生成*/
    public function add_login_token($init_id){
        /*实例化 表model*/
        $admin_model=new AdminModel();
        /*记录ip*/
        $data["login_ip"]=get_client_ip();
        /*生成新的token*/
        $data["login_token"]=md5(rand(10000,999999).C('ADMINMD5').rand(10000,999999));
        /*更新登录时间*/
        $data["newTime"]=date("Y-m-d H:i:s",time());
        /*记录token失效时间*/
        $data["login_token_out_time"]=date("Y-m-d H:i:s",time()+7200);
        if($admin_model->update_token($init_id,$data)){
            return $data["login_token"];
        }
        return null;
    }
}