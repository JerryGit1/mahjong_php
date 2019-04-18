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
use HomeGM\Model\AgencyRelationModel;
class AgencyController extends BaseController{
    /*代理列表*/
    public function agency_user_list(){
        $this->jurisdiction_limit(6);//权限限定
        $init_id=$this->getParamInfo("agency_id",2,true);//登录者的init_id
        $this_page=$this->getParamInfo("page",2,true);//当前页
        $page_num=$this->getParamInfo("pageNum",2,true);//每页数量
        $keyword=$this->getParamInfo("keyword",0,true);//搜索的代理id
        $admin_model=new AdminModel();/*实例化admin表*/
        $user_info=$admin_model->init_id_get_user_info($init_id);//查当前登录的代理是否存在
        if($user_info){
            $max_num=0;//一共多少条
            $start_page_num=($this_page-1)*$page_num;//每页的起始序列
            $max_page_num=ceil($max_num/$page_num);//总页数
            $while=$admin_model->get_agency_seek($keyword);
            $user_info_list=$admin_model->get_agency_list($while,$start_page_num,$page_num);//查列表
            $max_num=$admin_model->get_agency_list_num($while,$start_page_num,$page_num);//查总条数
            $rData=new \stdClass();/*实例化返回数据*/
            $rData->list=array();
            foreach ($user_info_list as $info){//遍历返回的代理列表
                array_push($rData->list,$info);//相当于js中的push。
            }
            $rData->maxNum=$max_num;//总条数
            $rData->maxPage=$max_page_num;//总页数
            $this->returnGameData(1,$rData);//返回数据到前端
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*id搜索玩家身份是代理还是玩家*/
    public function get_user_info(){
        $this->verify_token();//安全校验
        $user_id=$this->getParamInfo("playerId",2,true);//接收被搜索的代理id
        $player_model=new PlayerModel();//实例化玩家列表
        $user_info=$player_model->get_player_init_id($user_id);//查代理id存不存在
        $rData=new \stdClass();/*实例化返回数据*/
        if($user_info){
            $rData=$player_model->return_agency_data($user_info);
            //查找是不是代理
            $admin_model=new AdminModel();
            $seek_agency_user_info=$admin_model->user_id_get_user_info($user_id);//被查人的代理信息
            if($seek_agency_user_info){//是代理
                $rData->identity="agency";
                $rData->level=$seek_agency_user_info["grade"];
                $rData->money=$seek_agency_user_info["money"];
            }
        }else{
            $this->returnGameData(0,null,"用户不存在");
        }
        $this->returnGameData(1,$rData);
    }
    /*代理充值*/
    public function pay_user(){
        $this->verify_token();//安全校验
        $enter_init_id=$this->getParamInfo("agency_id",2,true);//登录的大区代理init_id
        $pay_money=$this->getParamInfo("pay_money",2,true);//要充值的钱
        $by_pay_init_id=$this->getParamInfo("pay_uId",2,true);//被充值的代理id
        $admin_model=new AdminModel();//实例化一个admin表
        $enter_user_info=$admin_model->init_id_get_user_info($enter_init_id);//先验证登录的大区代理是否存在
        $by_user_info=$admin_model->init_id_get_user_info($by_pay_init_id);//验证代理是否存在
        if($enter_user_info){
            if($by_user_info){
                if($enter_user_info["grade"]==7&&$enter_user_info["id"]==$by_pay_init_id){
                    //超管自己给自己充值
                    $result_info=$admin_model->add_init_id_money($by_pay_init_id,$pay_money);//给7级以上的管理员充钱
                    if($enter_user_info["grade"]>$by_user_info["grade"]){
                        if($enter_user_info["money"]>=$pay_money){
                            $user_info=$admin_model->delete_init_id_money($enter_init_id,$pay_money);//扣除充值者的余额
                        }else{
                            $this->returnGameData(0,null,"您的余额不足");
                        }
                    }
                    if($result_info||$user_info){
                        $pay_record=new payRecordModel();
                        $pay_record->write_full_record($pay_money,$by_pay_init_id,$enter_init_id,3);/*添加到大区代理的转账记录*/
                        statistic_user_pay($pay_money);//充值统计
                        $this->returnGameData(1,"ok");/*返回数据*/
                    }else{
                        $this->returnGameData(0,null,"充值失败，请稍后重试");
                    }
                }else{
                    //如果等级为超管以上的话，可以自己给自己充钱，
                    $user_money=$admin_model->get_pay_money_save($enter_init_id,$pay_money);//验证充值的money是否超过库存money
                    if($user_money){
                        $user_info=$admin_model->delete_init_id_money($enter_init_id,$pay_money);//扣除充值者的代理余额
                        $result_info=$admin_model->add_init_id_money($by_pay_init_id,$pay_money);//给代理充钱
                        if($user_info&&$result_info){
                            $pay_record=new payRecordModel();
                            $pay_record->write_full_record($pay_money,$by_pay_init_id,$enter_init_id,2);/*添加到大区代理的转账记录*/
                            statistic_user_pay($pay_money);//充值统计
                            $this->returnGameData(1,"ok");/*返回数据*/
                        }else{
                            $this->returnGameData(0,null,"充值失败，请稍后重试");
                        }
                    }else{
                        $this->returnGameData(0,null,"您的余额不足");
                    }

                }
            }else{
                $this->returnGameData(0,null,"代理不存在");
            }
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*充值记录列表------------充值记录搜索*/
    public function pay_record_list(){
        $this->verify_token();//安全校验
        $enter_user_id=$this->getParamInfo("agency_id",2,true);//登录的代理user_id
        $this_page=$this->getParamInfo("page",2,true);//当前页数
        $page_num=$this->getParamInfo("pageNum",2,true);//每页数量
        $keyword=$this->getParamInfo("select_agency_id",0,true);//要搜索的那个代理id(user_id)
        $init_id=$this->getParamInfo("token_userId",2,true);//登录者的init_id
        $admin_model=new AdminModel();//实例化admin表
        $user_info=$admin_model->user_id_get_user_info($enter_user_id);//验证代理是否存在
        if($user_info){//判断user_info
            $all_list_num=0;//总条数
            $start_num=($this_page-1)*$page_num;//计算出每页数量的起始序列
            $pay_record=new payRecordModel();//实例化充值记录表
            $list=array();//声明数组
            $keyword_init_id=$admin_model->keyword_seek_init_id($keyword);
            $list=$pay_record->seek_agency_select($user_info["grade"],$keyword_init_id,$init_id,$start_num,$page_num);
            $all_list_num=$pay_record->seek_agency_count($user_info["grade"],$keyword_init_id,$init_id,$start_num,$page_num);
            $all_page=ceil($all_list_num/$page_num);//总页数ceil:向上取整
            $rData=new \stdClass();/*实例化返回数据*/
            $rData->list=array();
            foreach ($list as $info){
                /*查头像和昵称game_user玩家列表*/
                $new_info=$admin_model->format_user_info2($info['p_uId'],$user_info["grade"],$info['state'],$info['a_uId']);
                if($new_info){
                    $new_info["money"]=$info["money"];
                    $new_info["createTime"]=$info["createTime"];
                }
                array_push($rData->list,$new_info);//相当于js中的push。
            }
            $rData->maxNum=$all_list_num;//一共多少条记录
            $rData->maxPage=$all_page;//总页数
            $rData->monthNum=$pay_record->get_month_all_agency_num($user_info["grade"],$init_id);//state为2时的本月总出售;//本月总出售
            $rData->monthTotalMoney=$pay_record->get_month_agency_all_total_num($user_info["grade"],$init_id);//state为2时的本月出售合计金额;//本月出售合计金额
            $this->returnGameData(1,$rData);
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*购买记录列表*/
    public function buy_record_list(){
        $this->verify_token();//安全校验
        $user_id=$this->getParamInfo("agency_id",2,true);//登录的大区代理user_id
        $keyword=$this->getParamInfo("keyword",0,true);//被搜索的代理id
        $this_page=$this->getParamInfo("page",2,true);//登录的大区代理user_id
        $page_num=$this->getParamInfo("pageNum",2,true);//登录的大区代理user_id
        $admin_model=new AdminModel();
        $agencyInfo=$admin_model->user_id_get_user_info($user_id);
        $a_r_model=new payRecordModel();/*实例化 表model*/
        if($agencyInfo){/*总字段数*/
            $init_id=$agencyInfo['id'];
            $whereStr=$a_r_model->buy_record_list($agencyInfo["grade"],$init_id);
            //充值类型 1代理给玩家充值 2代理给下级代理充值 3超管自己充值',
            $maxNum=$a_r_model->statistics_agency_num($whereStr);
            $pageMaxNum=ceil($maxNum/$page_num);/*总页数*/
            $list=$a_r_model->statistics_agency($whereStr,$this_page,$page_num);
            $rData=new \stdClass();
            $rData->list=array();
            foreach ($list as $r_info) {
                /*查找代理信息*/
                $u_info=$admin_model->init_id_get_user_info((int)$r_info["a_uId"]);
                if($u_info){
                    $r_info["userName"]=$u_info["userName"];
                    $r_info["head"]=$u_info["head"];
                    $r_info["userId"]=$u_info["binding_playerId"];
                    array_push($rData->list,$r_info);
                }
            }
            //查询月出售次数//查询月出售金额
            $rData->monthNum=$a_r_model->monthBuyNum($init_id,$agencyInfo["grade"]);
            $rData->monthTotalMoney=$a_r_model->monthBuyMoney($init_id,$agencyInfo["grade"]);
            $rData->maxPage=$pageMaxNum;
            $rData->maxNum=$maxNum;
            $this->returnGameData(1,$rData,$init_id);
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*搜索代理id*/
    public function get_init_info(){
        $this->verify_token();//安全校验
        $init_id=$this->getParamInfo("agency_id",2,true);//登录者的init_id
        $user_id=$this->getParamInfo("pay_uId",2,true);//被搜索的user_id
        $admin_model=new AdminModel();//实例化dmin表
        $pay_record_model=new payRecordModel();//实例化充值列表
        $init_info=$admin_model->init_id_get_user_info($init_id);//查登录者的代理是否存在
        $user_info=$admin_model->user_id_get_user_info($user_id);//查被搜索的user_id
        $rData=new \stdClass();//实例化返回数据
        if($init_info){//判断登录者的代理是否存在
            if($user_info){//判断被搜索的user_id是否存在
                //判断登录者与代理是否为上下级关系,超管可以自己为自己充钱
                if($init_info["grade"]>$user_info["grade"]){
                    $rData=$admin_model->seek_agency_id($user_info);
                    //房卡
                    $money=$admin_model->user_id_get_user_info($user_id);//查登录者代理的一整条信息
                    $rData->money=$money["money"];
                    $rData->test_agency=$money["test_agency"];
                    //总购买
                    $buyMoney=$pay_record_model->all_buy($user_id);
                    $rData->buyMoney=$buyMoney;
                    /*总出售*/
                    $rData->sell_player_money=$pay_record_model->agency_all_sell_money($init_id,1);//给玩家的出售记录
                    $rData->sell_agency_money=$pay_record_model->agency_all_sell_money($init_id,2);//给代理的出售记录
                    $this->returnGameData(1,$rData);
                }else if(
                    ["grade"]&&$user_info["grade"]>=7){
                    $rData=$admin_model->seek_agency_id($user_info);
                    $this->returnGameData(1,$rData);
                }else{
                    $this->returnGameData(0,null,"权限不足");
                }
            }else{
                $this->returnGameData(0,null,"用户不存在");
            }
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*代理商添加，设为代理接口*/
    public function set_up_agency(){
        $this->verify_token();//安全校验
        $agency_init_id=$this->getParamInfo("agency_id",2,true);//登录者的init_id
        $player_seek_user_id=$this->getParamInfo("playerId",2,true);//被搜索的user_id,要设为代理的id
        $player_seek_level=$this->getParamInfo("level",2,true);//要设置的代理等级
        $admin_model=new AdminModel();//实例化admin表
        $admin_user_info=$admin_model->init_id_get_user_info($agency_init_id);//验证登录者init_id是否存在
        $Player_model=new PlayerModel();//实例化玩家列表
        $player_user_info=$Player_model->user_id_get_user_info($player_seek_user_id);//查找被搜索玩家的是否存在
        if($admin_user_info){//判断init_id是否存在
            if($player_user_info){ //判断被搜索的玩家是否存在
                if($admin_user_info["grade"]>=5){ //5级以上允许操作
                    $grade=$admin_model->judge_agency_grade_search($admin_user_info["grade"],$player_seek_level);//设置为代理，代理等级
                    $Jurisdiction=$admin_model->judge_agency_jurisdiction_search($admin_user_info["grade"],$player_seek_level);
                    //如果登录者为究管，则无法涉及代理
                    if($admin_user_info["grade"]==8){$this->returnGameData(0,null,"究管无法涉及代理");}
                    $user_info["grade"]=$grade;//等级赋值
                    $user_info["jlist"]=$Jurisdiction;//权限赋值
                    $admin_player_seek_user_id=$admin_model->user_id_get_user_info($player_seek_user_id);//查找被搜索的user_id是否为代理身份
                    if(!$admin_player_seek_user_id) {
                        $user_info["binding_playerId"] = $player_user_info[$Player_model->field["user_id"]];
                        $user_info["userName"] = $player_user_info[$Player_model->field["userName"]];
                        $user_info["head"] = $player_user_info[$Player_model->field["head"]];
                        $user_info["test_agency"] = "1";
                        $plus_agency=$admin_model->add($user_info);//添加代理
                        if($plus_agency){
                            $relation_model=new AgencyRelationModel();/*添加到代理关系表*/
                            if($relation_model->add_relation($plus_agency,$agency_init_id)){
                                $this->returnGameData(1,"ok");
                            }
                        }else{
                            $this->returnGameData(0,null,"添加失败，请稍后重试");
                        }
                    }else{
                        $this->returnGameData(0,null,"该玩家已经是代理");
                    }
                }else{
                    $this->returnGameData(0,null,"权限不足");
                }
            }else{
                $this->returnGameData(0,null,"用户不存在");
            }
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*设为s极的接口*/
    public function s_agency_change(){
        $this->verify_token();//安全校验
        $user_id_seek=$this->getParamInfo("user_id_seek",2,true);//被搜索的代理id
        $grade=$this->getParamInfo("grade",2,true);//变更后的代理等级--5
        if($user_id_seek){
            $admin_model=new AdminModel();
            $user_info=$admin_model->user_id_get_user_info($user_id_seek);//查找该用户是否存在
            if($user_info){
                $s_agency_change=$admin_model->s_agency_change_grade_jlist($user_id_seek,$grade);
                if($s_agency_change){
                    $this->returnGameData(0,null,"变更成功");
                }else{
                    $this->returnGameData(0,null,"变更失败");
                }
            }else{
                $this->returnGameData(0,null,"用户不存在");
            }
        }
    }
    /*设为小代理的接口*/
    public function m_agency_change(){
        $this->verify_token();//安全校验
        $user_id_seek=$this->getParamInfo("user_id_seek",2,true);//被搜索的代理id
        $grade=$this->getParamInfo("grade",2,true);//变更后的代理等级--4
        if($user_id_seek){
            $admin_model=new AdminModel();
            $user_info=$admin_model->user_id_get_user_info($user_id_seek);
            if($user_info){
                $m_agency_change=$admin_model->m_agency_change_grade_jlist($user_id_seek,$grade);
                if($m_agency_change){
                    $this->returnGameData(0,null,"变更成功");
                }else{
                    $this->returnGameData(0,null,"变理失败");
                }
            }else{
                $this->returnGameData(0,null,"用户不存在");
            }
        }
    }
    /*确认扣除房卡接口*/
    public function enter_deduction(){
        $this->verify_token();//安全校验
        $deduct_id=$this->getParamInfo("deduct_id",2,true);//扣除者的用户id
        $deduct_money=$this->getParamInfo("deduct_money",2,true);//要扣除的金额
        $deduct_select=$this->getParamInfo("deduct_select",2,true);//扣除身份
        $transfer_id=$this->getParamInfo("transfer_id",2,true);//要转入的用户id
        $transfer_money=$this->getParamInfo("transfer_money",2,true);//要转入的金额
        $transfer_select=$this->getParamInfo("transfer_select",2,true);//转入身份
        $login_user_id=$this->getParamInfo("token_userId",2,true);//登录者的user_id
        $admin_model=new AdminModel();//实例化admin表
        $player_model=new PlayerModel();//实例化玩家列表
        $login_user_info=$admin_model->init_id_get_user_info($login_user_id);//查登陆者的一整条信息
        if($login_user_info["grade"]>=6){ //登录者等级大于等于大区代理时允许操作
            /*判断扣除金额是玩家身份还是代理身份*/
            if($deduct_select=="deduct_player"){ //扣除身份为玩家时
                if($transfer_select=="transfer_player"){//扣除身份为玩家转入身份为代理时
                    /*扣除玩家列表中的money*/
                    $deduct_transfer_success=$player_model->deduction_player_money($deduct_id,$deduct_money);
                    /*转入到另一个玩家身份，扣除成功后再转入，扣除不成功不转入*/
                    if($deduct_transfer_success){
                        $transfer_transfer_success=$player_model->add_player_money($transfer_id,$transfer_money);
                    }
                }else if($transfer_select=="transfer_agency"){
                    /*扣除玩家列表中的money*/
                    $deduct_transfer_success=$player_model->deduction_player_money($deduct_id,$deduct_money);
                    /*转入到另一个玩家身份，扣除成功后再转入，扣除不成功不转入---------------------------*/
                    if($deduct_transfer_success){
                        $user_info=$admin_model->user_id_get_user_info($transfer_id);/*查该代理是否存在*/
                        if($user_info){ //判断代理身份是否存在
                            $transfer_transfer_success=$admin_model->add_player_money($transfer_id,$transfer_money);//转入代理列表中的money
                        }else{
                            $this->returnGameData(0,null,"转入的用户还不是代理");
                        }
                    }
                }
            }else if($deduct_select=="deduct_agency"){ //扣除的金额为代理身份时
                if($transfer_select=="transfer_player"){
                    /*扣除代理列表中的money---------------------------*/
                    $user_info=$admin_model->user_id_get_user_info($deduct_id);/*查该代理是否存在*/
                    if($user_info){ //判断代理身份是否存在
                        $deduct_transfer_success=$admin_model->deduction_player_money($deduct_id,$deduct_money);//扣除代理列表中的money
                    }else{
                        $this->returnGameData(0,null,"扣除的用户还不是代理");
                    }
                    /*转入到另一个玩家身份，扣除成功后再转入，扣除不成功不转入*/
                    if($deduct_transfer_success){
                        $transfer_transfer_success=$player_model->add_player_money($transfer_id,$transfer_money);
                    }
                }else if($transfer_select=="transfer_agency"){
                    /*扣除代理列表中的money---------------------------*/
                    $user_info=$admin_model->user_id_get_user_info($deduct_id);/*查该代理是否存在*/
                    if($user_info){ //判断代理身份是否存在
                        $deduct_transfer_success=$admin_model->deduction_player_money($deduct_id,$deduct_money);//扣除代理列表中的money
                    }else{
                        $this->returnGameData(0,null,"扣除的用户还不是代理 请联系管理员");
                    }
                    /*转入到另一个玩家身份，扣除成功后再转入，扣除不成功不转入---------------------------*/

                    if($deduct_transfer_success){
                        $user_info=$admin_model->user_id_get_user_info($transfer_id);/*查该代理是否存在*/
                        if($user_info){ //判断代理身份是否存在
                            $transfer_transfer_success=$admin_model->add_player_money($transfer_id,$transfer_money);//转入代理列表中的money
                        }else{
                            $this->returnGameData(0,null,"转入的用户还不是代理");
                        }
                    }
                }
            }
            if($deduct_transfer_success){
                if($transfer_transfer_success){
                    $this->returnGameData(0,null,"扣除成功");
                }else{
                    $this->returnGameData(0,null,"扣除失败");
                }
            }else{
                $this->returnGameData(0,null,"扣除用户的金额不足 无法扣除");
            }
        }else{
            $this->returnGameData(0,null,"权限不足");
        }
    }
    /*设置为测试代理的接口*/
    public function set_up_test_agency(){
        $this->verify_token();//安全校验
        $user_id=$this->getParamInfo("user_id",2,true);//要设置的代理id
        $test_num=$this->getParamInfo("test_num",2,true);//要设置的测试号
        $admin_model=new AdminModel();//实例化admin表
        /*设置为测试代理*/
        $info=$admin_model->set_up_test_agency($user_id,$test_num);
        if($info){
            $this->returnGameData(0,null,"操作成功");
        }else{
            $this->returnGameData(0,null,"操作失败");
        }
    }




}

