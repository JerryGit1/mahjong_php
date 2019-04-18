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
class ConsoleController extends BaseController{
    //为统计账号提供的接口
    public function statistics_app(){
        $admin_model=new AdminModel();//实例化admin表
        $pay_record_model=new payRecordModel();//实例化充值列表
        $player_model=new PlayerModel();//实例化玩家列表
        $consume_record_model=new ConsumeRecordModel();//实例化玩家列表
        $rData=new \stdClass();//实例化返回数据
            /*玩家数据*/
            $rData->maxUserNum=$player_model->count();//总玩家
            $rData->dayNewAddUserNum=$player_model->player_day_new();//玩家日新增
            $rData->weekNewAddUserNum=$player_model->player_week_new();//玩家周新增
            $rData->monthNewAddUserNum=$player_model->player_month_new();//玩家月新增
            $rData->userPayTotalMoney=$pay_record_model->player_all_pay_money(1);//玩家总充值，查state等于1即可，=2给代理充，=3超管自已给自己充
            $rData->userConsumeTotalMoney=$consume_record_model->player_all_buy_money(100);//玩家总消费
            $rData->dayConsumeMoney=$consume_record_model->dayConsumeMoney();  //昨日消耗房卡
            $rData->weekConsumeMoney=$consume_record_model->weekConsumeMoney();//本周消耗房卡
            $rData->monthConsumeMoney=$consume_record_model->monthConsumeMoney();//本月消耗房卡
            $rData->plus_agency_count=$admin_model->plus_agency_count();//大区代理数
            $rData->s_agency_count=$admin_model->s_agency_count();//s级代理数
            $rData->min_agency_count=$admin_model->min_agency_count();//小代理数
            $rData->dayNewAddUserAgency=$admin_model->agency_day_new();//代理日新增
            $rData->weekNewAddUserAgency=$admin_model->agency_week_new();//代理周新增
            $rData->monthNewAddUserAgency=$admin_model->agency_month_new();//代理月新增
            $rData->agencyAllPayManey=$pay_record_model->player_all_buy_money(2,10001);//代理总充值
            $rData->dayConsumeMoneyAgency=$pay_record_model->dayConsumeMoneyAgency(2,10001);//代理日充值房卡
            $rData->weekConsumeMoneyAgency=$pay_record_model->weekConsumeMoneyAgency(2,10001);//代理周充值房卡
            $rData->monthConsumeMoneyAgency=$pay_record_model->monthConsumeMoneyAgency(2,10001);//代理月充值房卡
            $this->returnGameData(1,$rData);
    }
    /*管理中心基本数据*/
    public function index_manage_center_page(){
        $this->jurisdiction_limit(7);//权限限定
        $init_id=$this->getParamInfo("userId",2,true);//登录者的init_id
        $admin_model=new AdminModel();//实例化admin表
        $pay_record_model=new payRecordModel();//实例化充值列表
        $player_model=new PlayerModel();//实例化玩家列表
        $consume_record_model=new ConsumeRecordModel();//实例化玩家列表
        $user_info=$admin_model->init_id_get_user_info($init_id);//查登录者代理的一整条信息
        if($user_info){
            $rData=new \stdClass();//实例化返回数据
            $rData->adminPayTotalMoney=$pay_record_model->agency_pay_list_money($init_id,3);//总投入房卡
            $money=$admin_model->init_id_get_user_info($init_id);//查登录者代理的一整条信息
            $buyMoney=$pay_record_model->all_buy($init_id);
            $rData->money=$money["money"];//房卡
            $rData->buyMoney=$buyMoney;//总购买
            /*总出售*/
            $rData->sell_player_money=$pay_record_model->agency_all_sell_money($init_id,1);//给玩家的出售记录
            $rData->sell_agency_money=$pay_record_model->agency_all_sell_money($init_id,2);//给代理的出售记录
            if($user_info["grade"]>=7){
                /*玩家数据*/
                $rData->maxUserNum=$player_model->count();//总玩家
                $rData->dayNewAddUserNum=$player_model->player_day_new();//玩家日新增
                $rData->weekNewAddUserNum=$player_model->player_week_new();//玩家周新增
                $rData->monthNewAddUserNum=$player_model->player_month_new();//玩家月新增
                $rData->userPayTotalMoney=$pay_record_model->player_all_pay_money(1);//玩家总充值，查state等于1即可，=2给代理充，=3超管自已给自己充
                $rData->userConsumeTotalMoney=$consume_record_model->player_all_buy_money(100);//玩家总消费
                $rData->dayConsumeMoney=$consume_record_model->dayConsumeMoney();  //昨日消耗房卡
                $rData->weekConsumeMoney=$consume_record_model->weekConsumeMoney();//本周消耗房卡
                $rData->monthConsumeMoney=$consume_record_model->monthConsumeMoney();//本月消耗房卡
                /*代理数据*/
                $rData->maxUserAgency=$admin_model->count();//总代理
                $rData->dayNewAddUserAgency=$admin_model->agency_day_new();//代理日新增
                $rData->weekNewAddUserAgency=$admin_model->agency_week_new();//代理周新增
                $rData->monthNewAddUserAgency=$admin_model->agency_month_new();//代理月新增
                $rData->agencyAllPayManey=$pay_record_model->player_all_pay_money(2);//代理总充值
                $rData->dayConsumeMoneyAgency=$pay_record_model->dayConsumeMoneyAgency(2,10001);//代理日充值房卡
                $rData->weekConsumeMoneyAgency=$pay_record_model->weekConsumeMoneyAgency(2,10001);//代理周充值房卡
                $rData->monthConsumeMoneyAgency=$pay_record_model->monthConsumeMoneyAgency(2,10001);//代理月充值房卡
            }
            $this->returnGameData(1,$rData);
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
    /*统计信息*/
    public function Statistics_data(){
        $this->jurisdiction_limit(7);//权限限定
        $init_id=$this->getParamInfo("token_userId",2,true);//登录者的init_id
        $admin_model=new AdminModel();
        $user_info=$admin_model->init_id_get_user_info($init_id);
        if($user_info){//判断当前登录者在不在
            if($user_info["grade"]>=6){//判断权限是否符合要求
                $url=C('STATISTIC_URL');
                $param=new \stdClass();
                $param->p_name=$this->getParamInfo("proName",2,true);/*在线人数统计*/
                $param->o_name=$this->getParamInfo("OpeName",2,true);/*访问量统计*/
                $param->s_type=$this->getParamInfo("type",2,true);/*分享统计*/
                $param->s_date=$this->getParamInfo("s_date",2,true);/*代理购买统计*/
                $param->e_date=$this->getParamInfo("e_date",2,true);/*玩家充值统计*/
                $p_children=$this->getParamInfo("children",0,true);/*开房消费统计*/
                if($p_children){
                    $param->p_children=$p_children;
                }
                $rData=service_http_post_sync($url,$param);
                $rData=json_decode($rData);
                $this->returnGameData(1,$rData->info,$rData->message);
            }else{
                $this->returnGameData(1,null,"权限不足");
            }
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }
}

