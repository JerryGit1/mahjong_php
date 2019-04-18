<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/12/28
 * Time: 18:50
 */
namespace HomeGM\Controller;
use HomeGM\Model\AdminModel;
use HomeGM\Model\PlayerModel;
use HomeGM\Model\payRecordModel;
use HomeGM\Model\ConsumeRecordModel;
use HomeGM\Model\PlayerRecordModel;
use HomeGM\Model\GameRoomModel;
class PlayerController extends BaseController{
    /*玩家列表*/
    public function player_user_list(){
        $this->verify_token();//安全校验
        $this_page=$this->getParamInfo("page",2,true);//当前页
        $page_num=$this->getParamInfo("pageNum",2,true);//每页数量
        $init_id=$this->getParamInfo("token_userId",2,true);//登录者的user_id
        $keyword=$this->getParamInfo("keyword",0,true);//要搜索的玩家id
        $admin_model=new AdminModel();
        $user_info=$admin_model->init_id_get_user_info($init_id);
        if($user_info){
            $user_model=new PlayerModel();
            $max_num=0;//总条数
            //每页起始序列
            $while=$user_model->get_seek_while_str($keyword);//获取查询条件;
            $start_page_num=($this_page-1)*$page_num;
            $user_info_list=$user_model->get_player_list($while,$start_page_num,$page_num);
            $max_num=$user_model->get_player_list_num($while,$start_page_num,$page_num);
            $max_page=ceil($max_num/$page_num);//总页数
            $rData=new \stdClass();
            $rData->list=array();
            foreach ($user_info_list as $info){
                $u_info=$user_model->return_format_info($info,$user_info["grade"]);
                array_push($rData->list,$u_info);//相当于js中的push。
            }
            $rData->maxPage=$max_page;
            $rData->maxNum=$max_num;
            $this->returnGameData(1,$rData);
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*查询用户详情*/
    public function get_user_info(){
        $this->verify_token();//安全校验
        $user_id=$this->getParamInfo("userId",2,true);//搜索的玩家id
        $init_id=$this->getParamInfo("token_userId",2,true);//登录者的user_id
        $user_model=new PlayerModel();//实例化玩家列表
        $user_info=$user_model->user_id_get_user_info($user_id);//查找被搜索代理的一整条信息
        if($user_info) {
            $admin_model = new AdminModel();
            $admin_info = $admin_model->init_id_get_user_info($init_id);
            $new_user_info = $user_model->return_format_info($user_info, $admin_info["grade"]);
            $admin_user_info = $admin_model->user_id_get_user_info($user_id);
            if ($admin_user_info) {
                $new_user_info["agency_money"] = $admin_user_info["money"];
            } else {
                $new_user_info["agency_money"] = "该玩家不是代理";
            }
            $this->returnGameData(1,$new_user_info);
        }else{
            $this->returnGameData(0,null,"用户不存在");
        }
    }
    /*充值*/
    public function pay_user(){
        $this->verify_token();//安全校验
        $pay_money=$this->getParamInfo("pay_money",2,true);//充值金额
        $seek_user_id=$this->getParamInfo("player_uId",2,true);//搜索的玩家id
        $user_id=$this->getParamInfo("userId",2,true);//代理的userid
        $init_id=$this->getParamInfo("token_userId",2,true);//代理id
        $player__model=new \HomeGM\Model\PlayerModel();//实例化玩家列表
        $admin_model=new \HomeGM\Model\AdminModel();//实例化代理列表
        $pay_record=new payRecordModel();//实例化充值列表
        $user_info=$player__model->user_id_get_user_info($seek_user_id);//查询用户信息
        $admin_info=$admin_model->user_id_get_user_info($user_id);//查询代理信息
        if($admin_info&&$user_info){//判断玩家或代理存在不
            if($player__model->judge_black_user($user_info)){//判断是否是黑名单
                if($admin_info['money']>=$pay_money){//判断代理的钱>=要给玩家充值的钱
                    $new_agency_money=$admin_model->check_new_agency_money($user_id,$pay_money);//计算代理money
                    $new_player_money=$player__model->check_new_player_money($seek_user_id,$pay_money);//计算玩家money
                    if($new_player_money&&$new_agency_money){
                        $pay_record->write_full_record($pay_money,$seek_user_id,$init_id,1);/*添加到充卡记录*/
                        statistic_user_pay($pay_money);//充值统计
                        $this->returnGameData(1,"ok");/*返回数据*/
                    }else{
                        $this->returnGameData(0,null,"充值失败，请重试");
                    }
                }else{
                    $this->returnGameData(0,null,"余额不足，请联系上级代理");
                }
            }else{
                $this->returnGameData(0,null,"该用户已被拉黑，请联系管理员");
            }
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*充值记录列表--------充值记录搜索*/
    public function pay_record_list_user(){
        $this->verify_token();//安全校验
        $user_id=$this->getParamInfo("agencyId",2,true);//代理user_id
        $page=$this->getParamInfo("page",2,true);//当前页
        $page_num=$this->getParamInfo("pageNum",2,true);//每页数量
        $init_id=$this->getParamInfo("token_userId",2,true);//代理init_id
        $keyword=$this->getParamInfo("playerId",0,true);//搜索的用户id(user_id)
        $admin_model=new AdminModel($user_id);
        $agency_info=$admin_model->user_id_get_user_info($user_id);
        if($agency_info){//判断代理是否存在
            $payRecordModel=new payRecordModel();//实例化充值记录列表
            $max_num=0;//一共有多少条记录
            $start_num=($page-1)*$page_num;//起始序列数
            $list=array();//返回数据的数组
            $list=$payRecordModel->judge_agency_grade_return_list($agency_info['grade'],$start_num,$init_id,$keyword,$page_num);
            $max_num=$payRecordModel->judge_agency_grade_return_num($agency_info['grade'],$init_id,$keyword);
            $max_page=ceil($max_num/$page_num);//计算总页数，ceil向上取整
            $rData=new \stdClass();//实例化返回数据
            $rData->list=array();//声明返回的数组
            $player_model=new PlayerModel();//实例化玩家列表
            foreach ($list as $info){//遍历数据
                $u_info=$player_model->user_id_get_user_info($info['p_uId']);//game_user玩家列表，查头像昵称，重新赋值字段
                if($u_info){ //判断能不能查到
                    $info["userName"]=$u_info[$player_model->field["userName"]];
                    $info["head"]=$u_info[$player_model->field["head"]];
                }
                if($agency_info['grade']>=6){ /*配置代理商id,等级>=6时可以看到所有充值记录*/
                    $c_agency_info=$admin_model->init_id_get_user_info($info["a_uId"]);
                    $info["a_uId"]=$c_agency_info["binding_playerId"];
                    $info["aUserName"]=$c_agency_info["userName"];
                }
                array_push( $rData->list,$info);//相当于js中的push。
            }
            $rData->maxNum=$max_num;//总记录
            $rData->maxPage=$max_page;//总页数
            $this->returnGameData(1,$rData);//返回数据
        }else{
            $this->returnGameData(0,"非法操作");
        }
    }
    /*消费记录*/
    public function player_buy_list(){
        $this->jurisdiction_limit(6);//权限限定
        $this_page=$this->getParamInfo("page",2,true);//当前页
        $page_num=$this->getParamInfo("pageNum",2,true);//每页数量
        $keyword=$this->getParamInfo("userId",0,true);//搜索内容
        $init_id=$this->getParamInfo("token_userId",2,true);//登录者的init_id
        $admin_model=new AdminModel();//实例化admin表
        $player_model=new PlayerModel();
        $user_info=$admin_model->init_id_get_user_info($init_id);//查登录者的init_id
        if($user_info){//判断init是否存在
            $max_num=0;//初始化变量总条数
            $start_num=($this_page-1)*$page_num;//每页起始序列
            $consume_record_model=new ConsumeRecordModel();
            $while=$consume_record_model->judge_buy_list($keyword);
            $user_info_list=$consume_record_model->get_player_buy_record($while,$start_num,$page_num);//查列表
            $max_num=$consume_record_model->get_player_buy_record_num($while,$start_num,$page_num);//查总数
            $max_page=ceil($max_num/$page_num);//ceil向上取整
            $rData=new \stdClass();//实例化返回数据
            $rData->list=array();//声明数组
            foreach ($user_info_list as $info){
                $new_user_info=$consume_record_model->format_info($info);
                if($new_user_info){
                    $u_info=$player_model->user_id_get_user_info($info['uId']);//game_user玩家列表，查头像昵称，重新赋值字段
                    if($u_info){//判断能不能查到
                        $new_user_info["userName"]=$u_info[$player_model->field["userName"]];
                        $new_user_info["head"]=$u_info[$player_model->field["head"]];
                    }
                    array_push( $rData->list,$new_user_info);//相当于js中的push。
                }
            }
            $rData->maxNum=$max_num;//总记录
            $rData->maxPage=$max_page;//总页数
            $this->returnGameData(1,$rData);//返回数据
        }else{
            $this->returnGameData(0,"非法操作");
        }
    }
    /*黑名单列表*/
    public function black_name_list(){
        $this->jurisdiction_limit(6);//权限限定
        $this_page=$this->getParamInfo("page",2,true);//当前页
        $page_num=$this->getParamInfo("pageNum",2,true);//每页数量
        $keyword=$this->getParamInfo("keyword",0,true);//搜索条件
        $init_id=$this->getParamInfo("token_userId",2,true);//登录者的init_id
        $admin_model=new AdminModel();//实例化admin表
        $player_model=new PlayerModel();//实例化玩家列表
        $user_info=$admin_model->init_id_get_user_info($init_id);//查登录者id是否存在
        $max_num=0;//总条数
        $start_page_num=($this_page-1)*$page_num;//每页起始序列
        $while=$player_model->judge_black_while($keyword);
        $get_black_list=$player_model->get_player_list($while,$start_page_num,$page_num);
        $max_num=$player_model->get_player_list_num($while,$start_page_num,$page_num);
        $all_page=ceil($max_num/$page_num);//总页数
        $rData=new \stdClass();//实例化返回数据
        $rData->list=array();//声明数组
        foreach ($get_black_list as $info){
            $new_info=$player_model->return_format_info($info,$user_info["grade"]);
            array_push($rData->list,$new_info);
        }
        $rData->maxNum=$all_page;//总页数
        $rData->maxPage=$max_num;//总条数
        $this->returnGameData(1,$rData);
    }
    /*黑名单操作----加入黑名单*/
    public function set_user_black_list(){
        $this->jurisdiction_limit(6);//权限限定
        $user_id=$this->getParamInfo("userId",2,true);//要加入黑名单的玩家
        $black=$this->getParamInfo("black",2,true);//前端传来的黑转白，白转黑参数
        $player_model=new PlayerModel();
        $set_user_info=$player_model->set_user_black($user_id,$black);
        if($set_user_info){
            $this->returnGameData(1,"操作成功");
        }
        $this->returnGameData(0,"操作失败");
    }
    /*战绩列表-------战绩查询*/
    public function record_list_query(){
        $this_page=$this->getParamInfo("page",2,true);//当前页
        $page_num=$this->getParamInfo("pageNum",2,true);//每页数量
        $init_id=$this->getParamInfo("token_userId",2,true);//登录者的init_id
        $user_id=$this->getParamInfo("userId",0,true);//要搜索的玩家id
        $admin_model=new AdminModel();//实例化admin表
        $user_info=$admin_model->init_id_get_user_info($init_id);//查init_id是否存在
        if($user_info){
            if($user_info["grade"]>=6){
                $max_num=0;
                $player_record_model=new PlayerRecordModel();//实例化战绩列表
                $page_num_start=($this_page-1)*$page_num;//每页起始序列
                $where=$player_record_model->judge_record_seek($user_id);
                $list=$player_record_model->get_all_record_data($where,$page_num_start,$page_num);/*查所有战绩数据*/
                $max_num=$player_record_model->get_all_record_data_num($where,$page_num_start,$page_num);/*总条数*/
                $page_max_num=ceil($max_num/$page_num);//总页数
                $rData=new \stdClass();//声明大数组
                $rData->list=array();//声明数组
                $room_model=new GameRoomModel();//实例化room表
                $player_model=new PlayerModel();//实例化玩家列表
                foreach ($list as $info){
                    $data=$player_record_model->return_record_list($info);
                    if($data){
                        $data->room_user_id=$room_model->get_owner($data->roomID);
                        $data->player=array();
                        for ($a=1;$a<=4;$a++){
                            $new_info=array(
                                "uName"=>$player_model->get_record_user_name($info[$player_record_model->field["p$a"]]),
                                "score"=>$info[$player_record_model->field["p$a"."Score"]],
                                "uId"=>$info[$player_record_model->field["p$a"]],
                            );
                            array_push($data->player,$new_info);
                        }
                        array_push($rData->list,$data);
                    }
                }
                $rData->maxPage=$page_max_num;
                $rData->maxNum=$max_num;
                $this->returnGameData(1,$rData);
            }else{
                $this->returnGameData(0,"权限不足");
            }
        }else{
            $this->returnGameData(0,"非法操作");
        }
    }
}