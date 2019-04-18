<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/12/28
 * Time: 18:52
 */
namespace HomeGM\Model;
class ConsumeRecordModel extends BaseModel{
    /*绑定数据库*/
    public function __construct(){
        $this->setTable("player_consume");
        parent::__construct();
    }
    /*玩家总消费统计,总消费数量:=1超管, =2代理 =100玩家*/
    public function player_all_buy_money($type){
        $sTime=strtotime(date('Y-m-d'));//结束时间-----今天0点时间
        $msTime=$sTime*1000;//到毫秒的时间戳
        $info=$this->where($this->field["type"]."=$type AND ".$this->field["createTime"]."<".$msTime)->sum("money");
        if($info){
            return $info;
        }else{
            return $info=0;
        }
    }
    /*查玩家的消费记录*/
    public function get_player_buy_record($while,$start_num,$page_num){
        return $this->where($while)->order($this->field["createTime"]." desc")->limit($start_num,$page_num)->select();
    }
    /*查玩家的总条数*/
    public function get_player_buy_record_num($while,$start_num,$page_num){
        return $this->where($while)->order($this->field["createTime"]." desc")->limit($start_num,$page_num)->count();
    }
    /*判断消费记录*/
    public function judge_buy_list($keyword){
        if($keyword){/*判断$keyword是否有值*/
            $while="uId='$keyword' AND type=100";//有值时传查询条件
        }else{
            $while="type=100";//没有值时查整个列表
        }
        return $while;
    }
    /*格式化user_info*/
    public function format_info($info){
        if($info){
            $new_info["createTime"]=$info[$this->field["createTime"]];
            $new_info["id"]=$info[$this->field["id"]];
            $new_info["money"]=$info[$this->field["money"]];
            $new_info["type"]=$info[$this->field["type"]];
            $new_info["uId"]=$info[$this->field["uId"]];
            return $new_info;
        }
        return null;
    }
    /*每日消耗房卡*/
    public function dayConsumeMoney(){
        $startDate=date('Y-m-d',strtotime("-1 day"));//开始时间-----昨天0点时间
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->timeStatistics(strtotime($startDate),strtotime($overDate));
    }
    /*每周消耗房卡*/
    public function weekConsumeMoney(){
        $startDate=date('Y-m-d',strtotime( "previous monday" ));//开始时间-----本周一
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->timeStatistics(strtotime($startDate),strtotime($overDate));
    }
    /*每月消耗房卡*/
    public function monthConsumeMoney(){
        $startDate=date('Y-m-01');//开始时间-----本月一号
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->timeStatistics(strtotime($startDate),strtotime($overDate));
    }
    /*玩家消耗房卡统计*/
    public function timeStatistics($sTime,$oTime){
        $num=$this->where($this->field["createTime"].'>'.($sTime*1000)." AND ".$this->field["createTime"].'<'.($oTime*1000))->sum($this->field["money"]);
        if(!$num)$num=0;
        return $num;
    }
}