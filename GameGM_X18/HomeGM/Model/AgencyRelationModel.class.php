<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/12/28
 * Time: 18:52
 */
namespace HomeGM\Model;
class AgencyRelationModel extends BaseModel{
    protected $tableName = "agency_relation";

    /*代理关系添加*/
    public function add_relation($uId,$superUId){
        if(!$this->getRelation($uId,$superUId)){
            $data["uId"]=$uId;//被添加的代理init_id
            $data["superUId"]=$superUId;//登录者的代理init_id
            return $this->add($data);
        }
        return false;
    }

    /*代理关系获取*/
    public function getRelation($uId,$superUId){
        return $this->where("superUId=".$superUId." AND uId=".$uId)->getField("id");
    }

}