<?php
/**
 * 创建者 伟大的周鹏斌大王
 * 时间 2017/6/2 12:16
 */
namespace HomeGM\Model;
class payRecordModel extends BaseModel{
    protected $tableName = "player_payrecord";
    /*添加新充值的记录*/
    public function write_full_record($pay_money,$seek_user_id,$init_id,$state){
            $data['p_uId'] = $seek_user_id;
            $data['a_uId'] = $init_id;
            $data['money'] = $pay_money;
            $data['state'] = $state;
            $this->add($data);
    }
    /*state为2时的本月总出售，当登录等级为7时为超管，可以看到所有代理的充值记录*/
    public function get_month_all_agency_num($grade,$init_id){
        $total_first_day=strtotime(date('Y-m-01'));//本月开始时间，到秒的时间戳
        if($grade>=7){
            return $this->where("UNIX_TIMESTAMP(createTime)>=".$total_first_day." AND state=2")->count();
        }else{
            return $this->where("UNIX_TIMESTAMP(createTime)>=".$total_first_day." AND state=2 AND a_uId=".$init_id)->count();
        }
    }
    /*state为2时的本月总出售合计金额，当登录等级为7时为超管，可以看到所有代理的money合计*/
    public function get_month_agency_all_total_num($grade,$init_id){
        $total_first_day=strtotime(date('Y-m-01'));//本月开始时间，到秒的时间戳
        if($grade>=7){
            $num=$this->where("UNIX_TIMESTAMP(createTime)>=".$total_first_day." AND state=2")->sum("money");
        }else{
            $num=$this->where("UNIX_TIMESTAMP(createTime)>=".$total_first_day." AND state=2 AND a_uId=".$init_id)->sum("money");
        }
        if($num){
            return $num;
        }else{
            return 0;
        }
    }
    /*查购买记录*/
    public function purchase_list($user_id){
        return $this->where("state=2 AND p_uId=$user_id")->find();
    }
    /*本月购买次数统计*/
    public function monthBuyNum($p_uId,$grade){
        $sTime=date('Y-m-01', strtotime(date("Y-m-d")));
        $oTime=date('Y-m-d', strtotime("$sTime +1 month -1 day"));
        if($grade>=7){
            //超管
            return $this->where("p_uId=".$p_uId.' AND state=3 AND createTime> "'.$sTime.'" AND createTime<"'.$oTime.'"')->count();
        }else{
            return $this->where("p_uId=".$p_uId.' AND state!=3 AND createTime> "'.$sTime.'" AND createTime<"'.$oTime.'"')->count();
        }

    }
    /*本月购买总金额*/
    public function monthBuyMoney($p_uId,$grade){
        $sTime=date('Y-m-01', strtotime(date("Y-m-d")));
        $oTime=date('Y-m-d', strtotime("$sTime +1 month -1 day"));
        if($grade>=7){
            $num= $this->where("p_uId=".$p_uId.' AND state=3 AND createTime> "'.$sTime.'" AND createTime<"'.$oTime.'"')->sum("money");
        }else{
            $num= $this->where("p_uId=".$p_uId.' AND state!=3 AND createTime> "'.$sTime.'" AND createTime<"'.$oTime.'"')->sum("money");
        }
        if(!$num)$num=0;
        return  $num;
    }
    /*管理中心总投入房卡*/
    public function agency_pay_list_money($init_id,$state){
        return $this->where("a_uId=".$init_id." AND state!=".$state)->sum("money");
    }
    /*玩家总充值房卡*/
    public function player_all_pay_money($state){
        $sTime=strtotime(date('Y-m-d'));//结束时间-----今天0点时间，到秒
        return $this->where("state=".$state." AND UNIX_TIMESTAMP(createTime)<".$sTime)->sum("money");
    }
    /*单个代理的累计的购买房卡money*/
    public function  agency_all_buy_money($init_id,$state){
        return $this->where("state=".$state." AND p_uId=".$init_id)->sum("money");
    }
    /*单个代理的累计出售的房卡*/
    public function  agency_all_sell_money($init_id,$state){
        return $this->where("state=".$state." AND a_uId=".$init_id)->sum("money");
    }
    /*购买记录列表*/
    public function buy_record_list($grade,$init_id){
        if($grade>=7){//超管
            $whereStr="state=3 AND p_uId=".$init_id;//自己给自己充值 zpb
        }else{
            $whereStr="state=2 AND p_uId=".$init_id;
        }
        return $whereStr;
    }
    /*统计代理购买记录的数量*/
    public function statistics_agency_num($whereStr){
        return $this->where($whereStr)->count();
    }
    /*统计代理购买记录列表*/
    public function statistics_agency($whereStr,$this_page,$page_num){
        return $this->where($whereStr)->order("id desc")->limit(($this_page-1)*$page_num,$page_num)->select();
    }
    /*判断代理等级返回相应玩家的充卡记录*/
    public function judge_agency_grade_return_list($grade,$start_num,$init_id,$keyword,$page_num){
        if($grade>=6){ //判断当前代理等级,如果>=6可以看所有的充值记录
            if($keyword){
                /*当代理等级大于6时，可以看到所有玩家的充卡记录----------玩家搜索篇*/
                $list=$this->where("state=1 AND p_uId=$keyword")->order("createTime desc")->limit($start_num,$page_num)->select();
            }else{
                /*查所有玩家充卡记录*/
                $list=$this->where("state=1")->order("createTime desc")->limit($start_num,$page_num)->select();
            }
        }else{ //判断当前代理等级,如果<=6只可以看自己充的充值记录
            if($keyword){
                /*当代理等级小于6时，只能看到自己给自己充的记录--------玩家搜索篇*/
                $list=$this->where("state=1 AND a_uId=".$init_id." AND p_uId=".$keyword)->order("createTime desc")->limit($start_num,$page_num)->select();
            }else{
                /*查指定代理充值的玩家记录*/
                return $this->where("state=1 AND a_uId=$init_id")->order("createTime desc")->limit($start_num,$page_num)->select();
            }
        }
        return $list;
    }
    /*判断代理等级返回相应玩家的充卡记录合计*/
    public function judge_agency_grade_return_num($grade,$init_id,$keyword){
        if($grade>=6){ //判断当前代理等级,如果>=6可以看所有的充值记录
            if($keyword){
                /*当代理等级大于6时，可以看到所有玩家的充卡记录--------玩家搜索篇，，统计数量*/
                $max_num=$this->where("state=1 AND p_uId=$keyword")->count();
            }else{
                /*查state=1的记录*/
                $max_num=$this->where("state=1")->count();
            }
        }else{//判断当前代理等级,如果<=6只可以看自己充的充值记录
            if($keyword){
                /*当代理等级<=6时，只能看到自己给自己充的记录---------玩家搜索篇，，统计数量*/
                $max_num=$this->where("state=1 AND a_uId=".$init_id." AND p_uId=".$keyword)->count();
            }else{
                /*查有多少条state=1和自己下面充值的记录*/
                $max_num=$this->where("state=1 AND a_uId=$init_id")->count();
            }
        }
        return $max_num;
    }
    /*查总购买*/
    public function all_buy($init_id){
        return $this->where("p_uId=".$init_id)->sum("money");
    }
    public function seek_agency_select($grade,$keyword_init_id,$init_id,$start_num,$page_num){
        if($grade<7){ //判断代理等级小于7时，只可看到自己为代理的充值记录
            if($keyword_init_id){ //判断$keyword是否存在,如存在则性质为搜索
                $list=$this->where("state=2 AND a_uId=".$init_id." AND p_uId=".$keyword_init_id)->order("createTime desc")->limit($start_num,$page_num)->select();
            }else{
                $list=$this->where("state=2 AND a_uId=$init_id")->order("createTime desc")->limit($start_num,$keyword_init_id)->select();
            }
        }else{ //判断代理等级为超管时，可看到所有代理的充值记录
            if($keyword_init_id){
                $list=$this->where("state=2 AND p_uId=".$keyword_init_id)->order("createTime desc")->limit($start_num,$page_num)->select();
            }else{
                $list=$this->where("state=2")->order("createTime desc")->limit($start_num,$page_num)->select();
            }
        }
        return $list;
    }
    public function seek_agency_count($grade,$keyword_init_id,$init_id,$start_num,$page_num){
        if($grade<7){ //判断代理等级小于7时，只可看到自己为代理的充值记录
            if($keyword_init_id){ //判断$keyword是否存在,如存在则性质为搜索
                return $this->where("state=2 AND a_uId=".$init_id." AND p_uId=".$keyword_init_id)->count();
            }else{
                return $this->where("state=2 AND a_uId=$init_id")->count();
            }
        }else{ //判断代理等级为超管时，可看到所有代理的充值记录
            if($keyword_init_id){
                return $this->where("state=2 AND p_uId=".$keyword_init_id)->count();
            }else{
                return $this->where("state=2")->count();
            }
        }
    }
    /*代理总充值房卡*/
    public function player_all_buy_money($state,$a_uid){
        $sTime=strtotime(date('Y-m-d'));//结束时间-----今天0点时间，到秒
        return $this->where("state=".$state." AND UNIX_TIMESTAMP(createTime)<".$sTime." AND a_uId=".$a_uid)->sum("money");
    }
    /*代理日充值房卡*/
    public function dayConsumeMoneyAgency($state,$a_uid){
        $startDate=strtotime(date('Y-m-d',strtotime("-1 day")));//开始时间-----昨天0点时间
        $overDate=strtotime(date('Y-m-d'));//结束时间-----今天0点时间
        $num=$this->where("state=".$state." AND UNIX_TIMESTAMP(createTime)>".$startDate." AND UNIX_TIMESTAMP(createTime)<".$overDate." AND a_uId=".$a_uid)->sum("money");
        if(!$num)$num=0;
        return $num;
    }
    /*代理周充值房卡*/
    public function weekConsumeMoneyAgency($state,$a_uid){
        $startDate=strtotime(date('Y-m-d',strtotime( "previous monday" )));//开始时间-----本周一
        $overDate=strtotime(date('Y-m-d'));//结束时间-----今天0点时间
        $num=$this->where("state=".$state." AND UNIX_TIMESTAMP(createTime)>".$startDate." AND UNIX_TIMESTAMP(createTime)<".$overDate." AND a_uId=".$a_uid)->sum("money");
        if(!$num)$num=0;
        return $num;
    }
    /*代理月充值房卡*/
    public function monthConsumeMoneyAgency($state,$a_uid){
        $startDate=strtotime(date('Y-m-01'));//开始时间-----本月一号
        $overDate=strtotime(date('Y-m-d'));//结束时间-----今天0点时间
        $num=$this->where("state=".$state." AND UNIX_TIMESTAMP(createTime)>".$startDate." AND UNIX_TIMESTAMP(createTime)<".$overDate." AND a_uId=".$a_uid)->sum("money");
        if(!$num)$num=0;
        return $num;
    }
}