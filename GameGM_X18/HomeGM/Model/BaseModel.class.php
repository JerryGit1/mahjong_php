<?php
/**
 * 创建者 伟大的周鹏斌大王
 * 时间 2017/6/1 21:04
 */
namespace HomeGM\Model;
use Think\Model;

class BaseModel extends Model{

    protected $trueTableName ="";
    public  $field;
    /*设置表配置*/
    protected  function setTable($name){
        $table=C('TABLE.'.$name);
        $this->trueTableName=$table["table_name"];
        $this->field=$table["field"];
    }
}