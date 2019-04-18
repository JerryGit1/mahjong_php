<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2018/1/16
 * Time: 14:00
 */
namespace HomeGM\Controller;

use HomeGM\Model\AdminModel;

class TableController extends BaseController {


    public function set_grade_s_agency(){
        $js_list_s=json_encode(C('AGENCY_S_AUTH'));/*S级代理标准权限*/
        $js_list_m=json_encode(C('AGENCY1_AUTH'));/*代理标准权限*/
        $admin_model=new AdminModel();
        $s_user_info=$admin_model->where("grade=5")->setField("jlist",$js_list_s);/*S级代理标准权限*/
        $m_user_info=$admin_model->where("grade=4")->setField("jlist",$js_list_m);/*代理标准权限*/
        echo "s级代理".$s_user_info;/*S级代理标准权限---改为代理*/
        echo "小代理".$m_user_info;/*代理标准权限*/
    }



}