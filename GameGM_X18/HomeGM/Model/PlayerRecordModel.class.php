<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2018/1/10
 * Time: 14:39
 */
namespace HomeGM\Model;
class PlayerRecordModel extends BaseModel{
    /*绑定数据库*/
    public function __construct(){
        $this->setTable("player_record");
        parent::__construct();
    }
    /*判断战绩查询是否有值*/
    public function judge_record_seek($user_id){
        if($user_id){//判断搜索内容是否有值
            $where=$this->field["p1"]."=".$user_id." OR ".$this->field["p2"]."=".$user_id." OR ".$this->field["p3"]."=".$user_id." OR ".$this->field["p4"]."=".$user_id;
        }else{
            $where="";
        }
        return $where;
    }
    /*查战绩所有数据*/
    public function get_all_record_data($where,$page_num_start,$page_num){
        return $this->where($where)->order($this->field["endTime"]." desc")->limit($page_num_start,$page_num)->select();
    }
    /*查战绩所有条数*/
    public function get_all_record_data_num($where,$page_num_start,$page_num){
        return $this->where($where)->order($this->field["endTime"]." desc")->limit($page_num_start,$page_num)->count();
    }
    /*返回战绩列表*/
    public function return_record_list($info){
        if($info) {
            $data=new \stdClass();
            $data->roomID=$info[$this->field["roomId"]];
            $data->sTime=$info[$this->field["startTime"]];//开始时间
            $data->eTime=$info[$this->field["endTime"]];//结束时间
            return $data;
        }
        return null;
    }

}