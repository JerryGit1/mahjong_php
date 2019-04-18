<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/12/29
 * Time: 11:49
 */

namespace HomeGM\Model;
class PlayerModel extends BaseModel {
    /*绑定数据库*/
    public function __construct(){
        $this->setTable("player");
        parent::__construct();
    }
    /*通过userid获取用户信息*/
    public function user_id_get_user_info($user_id){
        return $this->where($this->field["user_id"]."='$user_id'")->find();//查询要查找的玩家id
    }
    /*通过openid查用户信息*/
    public function open_id_get_user_info($openId){
        return $this->where($this->field["openId"]."='$openId'")->find();//查询要查找的玩家openid
    }
    /*计算要充值的玩家id*/
    public function check_new_player_money($seek_user_id,$pay_money){
        /*给玩家加钱*/
        $user_id=$this->where($this->field["user_id"]."=".$seek_user_id)->setInc($this->field["money"],$pay_money);
        return $user_id;
    }

    /*用户id获取用户头像,昵称*/
    public function gain_head_name($id){
        return $this->where($this->field["user_id"]."=".$id)->getField("head","userName");
    }
    /*判断玩家是不是黑名单用户*/
    public function judge_black_user($user_info=null,$user_id=null){
        if($user_id){
            $user_info=$this->user_id_get_user_info($user_id);
        }
        if($user_info&&$user_info[$this->field['id_state']]){
            return true;
        }
        return false;
    }
    /*查玩家列表中代理在不在*/
    public function get_player_init_id($user_id){
        return $this->user_id_get_user_info($user_id);/*查玩家列表中代理在不在*/
    }
    /*玩家日新增*/
    public function player_day_new($num=1){
        $startDate=date('Y-m-d',strtotime("-1 day"));//开始时间-----昨天0点时间
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->newlyIncreasedNum(strtotime($startDate),strtotime($overDate));
    }
    /*玩家周新增*/
    public function player_week_new(){
        $startDate=date('Y-m-d',strtotime( "previous monday" ));//开始时间-----本周一
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->newlyIncreasedNum(strtotime($startDate),strtotime($overDate));
    }
    /*玩家月新增*/
    public function player_month_new(){
        $startDate=date('Y-m-01');//开始时间-----本月一号
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->newlyIncreasedNum(strtotime($startDate),strtotime($overDate));
    }
    /*新增玩家统计*/
    public function newlyIncreasedNum($sTime,$oTime){
        $num=$this->where($this->field["createTime"].'>'.($sTime*1000)." AND ".$this->field["createTime"].'<'.($oTime*1000))->count();
        if(!$num)$num=0;
        return $num;
    }
    /*拉入黑名单*/
    public function set_user_black($user_id,$black){
        $info=$this->where($this->field["user_id"]."=".$user_id)->setField($this->field["id_state"],$black);
        if($info){
            return true;
        }
        return false;
    }
    /*根据user_id查user_name*/
    public function get_record_user_name($user_id){
        if($user_id==null){
            return;
        }
        return $this->where($this->field["user_id"]."=".$user_id)->getField($this->field["userName"]);
    }
    /*查用户列表*/
    public function get_player_list($while,$start_page_num,$page_num){
        return $this->where($while)->order($this->field["createTime"]." desc")->limit($start_page_num,$page_num)->select();
    }
    /*查用户列表总数*/
    public function get_player_list_num($while,$start_page_num,$page_num){
        return $this->where($while)->order($this->field["createTime"]." desc")->limit($start_page_num,$page_num)->count();
    }
    /*查玩家列表的查询条件*/
    public function get_seek_while_str($keyword){
        if($keyword){
            $keywordId=(int)$keyword;
            $deKeyword=urlencode($keyword);//字符串转义
            return $this->field['user_id']."=$keywordId OR ".$this->field['userName']." LIKE '%$deKeyword%'OR ".$this->field['openId']."='$keyword'";
        }
        return "";
    }
    /*返回格式化用户信息*/
    public function return_format_info($user_info,$grade){
        if($user_info){
            $new_user_info["createTime"]=$user_info[$this->field["createTime"]];
            $new_user_info["head"]=$user_info[$this->field["head"]];
            $new_user_info["id"]=$user_info[$this->field["user_id"]];
            $new_user_info["money"]=$user_info[$this->field["money"]];
            $new_user_info["black"]=$user_info[$this->field["id_state"]];
            if($grade>=7){
                $new_user_info["openId"]=$user_info[$this->field["openId"]];
            }else{
                $new_user_info["openId"]="*";
            }
            $new_user_info["userName"]=$user_info[$this->field["userName"]];
            return $new_user_info;
        }
        return null;
    }
    /*判断黑名单用户是否存在*/
    public function judge_black_while($keyword){
        if($keyword){//判断$keyword是否有值
            $while=$this->field["id_state"]."=0 AND ".$this->field["user_id"]."='$keyword'";
        }else{
            $while=$this->field["id_state"]."=0";
        }
        return $while;
    }
    /*返回代理搜索数据*/
    public function return_agency_data($user_info){
        if($user_info){
            $rData=new \stdClass();
            $rData->head=$user_info[$this->field["head"]];
            $rData->id=$user_info[$this->field["user_id"]];
            $rData->userName=$user_info[$this->field["userName"]];
            $rData->identity="player";
            return $rData;
        }
    }
    /*扣除玩家金额*/
    public function deduction_player_money($deduct_id,$deduct_money){
        $user_info=$this->user_id_get_user_info($deduct_id);//查出该用户的一整条信息
        if(intval($user_info[$this->field["money"]])>=intval($deduct_money)){//判断该用户账户金额是否高于扣除金额
            $this->where($this->field["user_id"]."=".$deduct_id)->setDec($this->field["money"],$deduct_money);
            return true;
        }
        return false;
    }
    /*转入金额到另一个玩家*/
    public function add_player_money($transfer_id,$transfer_money){
        return $this->where($this->field["user_id"]."=".$transfer_id)->setInc($this->field["money"],$transfer_money);
    }



}