<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2018/1/10
 * Time: 14:58
 */
namespace HomeGM\Model;
class GameRoomModel extends BaseModel {
    public function __construct(){
        $this->setTable("game_room");
        parent::__construct();
    }
    /*查房主*/
    public function get_owner($data){
        return $this->where($this->field["roomId"]."=".$data)->getField($this->field["userId"]);
    }
}