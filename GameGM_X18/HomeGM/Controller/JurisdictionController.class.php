<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2018/1/4
 * Time: 14:54
 */
namespace HomeGM\Controller;
use HomeGM\Model\AdminModel;
use HomeGM\Model\PlayerModel;
use HomeGM\Model\payRecordModel;
use HomeGM\Model\AgencyRelationModel;
class JurisdictionController extends BaseController{
    private $userInfo;//声明全局变量,修改大区代理信息
    /*大区代理列表,当代理等级>=7时可看*/
    public function agency_info_list(){
        $this->jurisdiction_limit(7);//权限限定
        $user_id=$this->getParamInfo("userId",2,true);//登录者的init_id
        $seek_keyword=$this->getParamInfo("keyword",0,true);//搜索的关键字
        $admin_model=new AdminModel();//实例化admin表
        $user_info=$admin_model->init_id_get_user_info($user_id);//查登录者信息
        $user_info_grade=$user_info["grade"];
        if($user_info){//判断登录者是否为空
            if($seek_keyword){//判断搜索的关键字是否有值
                $grade_info=$admin_model->seek_daqu_agency($seek_keyword,$user_id);//模糊查询
            }else{
                $grade_info=$admin_model->grade_user_info($user_info_grade);//查到的大区代理,查等级大于5并小于登录者等级的大区代理
            }
            $rData=new \stdClass();//实例化返回数据
            $rData=array();
            if($grade_info){
                foreach ($grade_info as $info){
                    $info["jurisdiction"]=$info["jlist"];
                    $info["name"]=$info["userName"];
                    $info["time"]=$info["createTime"];
                    $info["userId"]=$info["binding_playerId"];
                    array_push($rData,$info);//相当于js中的push。
                }
                $this->returnGameData(1,$rData);
            }else{
                $this->returnGameData(1,null,"大区代理查不到");
            }
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*获取大区代理权限列表*/
    public function plus_agency_info_list_html(){
        $this->jurisdiction_limit(7);//权限限定
        $this->returnGameData(1,C('MENU_LIST'));
    }
    /*添加大区代理----或超管。大区代理可以设置s级代理*/
    public function plus_agency_info_list(){
        $this->jurisdiction_limit(7);//权限限定
        $init_id=$this->getParamInfo("add_userId",2,true);//登录者的init_id
        $user_id=$this->getParamInfo("gameID",2,true);//添加大区代理时的游戏id
        $default_head=$this->getParamInfo("head",2,true);//大区代理头像
        $jlist=$this->getParamInfo("jlist",2,true);//要给大区代理的权限
        $level=$this->getParamInfo("level",2,true);//大区代理的等级-----------
        $login_name=$this->getParamInfo("loginName",2,true);//大区代理时设置的登录名
        $login_Password=$this->getParamInfo("loginPasswd",2,true);//大区代理时设置的密码
        $phone=$this->getParamInfo("phone",2,true);//大区代理手机号
        $user_name=$this->getParamInfo("userName",2,true);//用户名
        $admin_model=new AdminModel();//实例化admin表
        if(strlen($login_name)>=6&&strlen($login_Password)>=6){
            $is_add=$admin_model->judge_agency_login_name_is_add($login_name);//登录名不可一致
            if($is_add){
                $is_add=$admin_model->judge_agency_user_id_is_add($user_id);//user_id不可重复
                if($is_add){
                    $is_add=$admin_model->judge_agency_grade($init_id,8);//如果登录者为究管的话
                    if($is_add){ //判断登录者等级是否为究管----究管只可以设置超管，
                        $is_add=$admin_model->get_agency_grade_seven(7);//判断超管是否存在
                        if($is_add){ //判断超管是否已经存在
                            $this->returnGameData(0,null,"超管已存在不能有第二个");
                        }else{
                            $md5_password=$this->createMD5Password($login_Password,"ADMINMD5");
                            //加入一条超管信息到admin表
                            $info=$admin_model->add_agency_table($user_id,$default_head,$jlist,7,$login_name,$login_Password,$phone,$user_name,$md5_password);
                        }
                    }else{
                        $md5_password=$this->createMD5Password($login_Password,"ADMINMD5");
                        //加入一条大区代理信息到admin表
                        $info=$admin_model->add_agency_table($user_id,$default_head,$jlist,$level,$login_name,$login_Password,$phone,$user_name,$md5_password);
                    }
                    if($info){
                        //添加到代理关系表
                        $relation_model=new AgencyRelationModel();//实例化关系列表
                        $relation_model->add_relation($info,$init_id);//添加数据到关系列表
                        $this->returnGameData(1,"添加成功");
                    }else{
                        $this->returnGameData(0,null,"添加失败");
                    }
                }else{
                    $this->returnGameData(0,null,"游戏id不可重复");
                }
            }else{
                $this->returnGameData(0,null,"登录名已存在");
            }
        }else{
            $this->returnGameData(0,null,"登录名或密码长度不能小于6位");
        }
    }
    //获取当前大区代理信息*/
    public function get_plus_agency_info(){
        $this->jurisdiction_limit(7);//权限限定
        $change_init_id=$this->getParamInfo("userId",2,true);//被操作的init_id
        $admin_model=new AdminModel();//实例化admin表
        $change_init_info=$admin_model->init_id_get_user_info($change_init_id);
        if($change_init_info){
            $rData=new \stdClass();
            $rData->gameID=$change_init_info["binding_playerId"];
            $rData->grade=$change_init_info["grade"];
            $rData->loginName=$change_init_info["loginName"];
            $rData->loginPasswd=$change_init_info["loginPasswd"];
            $rData->menuList=json_decode($change_init_info["jlist"]);
            $rData->userHead=$change_init_info["head"];
            $rData->userId=$change_init_info["id"];
            $rData->userName=$change_init_info["userName"];
            $rData->userPhone=$change_init_info["phone"];
            $this->returnGameData(1,$rData);
        }else{
            $this->returnGameData(0,null,"该用户不存在");
        }
    }
    /*修改大区代理信息或权限*/
    public function modify_agency_info_jurisdiction(){
        $this->jurisdiction_limit(7);//权限限定
        $init_id=$this->getParamInfo("userId",2,true);//大区的init_id
        $this->userInfo["binding_playerId"]=$this->getParamInfo("gameID",2,true);//大区代理的游戏id
        $this->userInfo["head"]=$this->getParamInfo("head",2,true);//大区代理头像
        $this->userInfo["jlist"]=$this->getParamInfo("jlist",2,true);//大区的权限
        $this->userInfo["grade"]=$this->getParamInfo("level",2,true);//大区的代理等级
        $this->userInfo["loginName"]=$this->getParamInfo("loginName",2,true);//大区的登录名
        $this->userInfo["loginPasswd"]=$this->getParamInfo("loginPasswd",2,true);//大区的密码
        $this->userInfo["phone"]=$this->getParamInfo("phone",2,true);//大区手机号
        $this->userInfo["userName"]=$this->getParamInfo("userName",2,true);//大区的用户名
        $admin_model=new AdminModel();//实例化admin表
        if(strlen($this->userInfo["loginName"])>=6&&strlen($this->userInfo["loginPasswd"])>=6){
            $is_login_names=$admin_model->judge_agency_login_name_is_add($this->userInfo["loginName"],$init_id);//查登录名是否有重复
            if($is_login_names){
                $is_user_ids=$admin_model->get_plus_agency_user_id($this->userInfo["binding_playerId"],$init_id);//查游戏id是否有重复
                if($is_user_ids){
                    $this->userInfo["loginMD5Passwd"]=$this->createMD5Password($this->userInfo["loginPasswd"],"ADMINMD5");
                    $is_add=$admin_model->update_plus_agency($init_id,$this->userInfo);
                    if($is_add){
                        $this->returnGameData(1,"改的好");
                    }else{
                        $this->returnGameData(0,null,"修改失败或无修改内容");
                    }
                }else{
                    $this->returnGameData(0,null,"当前游戏ID已被绑定 请先解绑或换ID");
                }
            }else{
                $this->returnGameData(0,null,"该用户名已存在");
            }
        }else{
            $this->returnGameData(0,null,"登录名或密码长度不能小于6位");
        }
    }
}