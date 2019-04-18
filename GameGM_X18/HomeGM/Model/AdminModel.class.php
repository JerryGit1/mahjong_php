<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/12/28
 * Time: 19:32
 */
namespace HomeGM\Model;
class AdminModel extends BaseModel{
    protected $tableName ="admin_userinfo";
    /*原始init_id查代理信息*/
    public function init_id_get_user_info($init_id){
        $info=$this->where("id=".$init_id)->find();//根据登录的id查找数据库代理的方法
        return $info;
    }
    /*游戏user_id查代理信息*/
    public function user_id_get_user_info($user_id){
        return $this->where("binding_playerId='$user_id'")->find();
    }
    /*token查代理信息*/
    public function token_get_user_info($token){
        return $this->where("login_token='$token'")->find();
    }
    /*根据代理等级查>=7的管理员*/
    public function grade_user_info($user_info_grade){
        return $this->where("grade<".$user_info_grade." AND grade>5")->getField("id,grade,userName,phone,jlist,createTime,binding_playerId");
    }
    /*验证token是否相等*/
    public function verify_token($v_token,$info){
        if($v_token==$info["login_token"]){
            return true;//token真实
        }
        return false;
    }
    /*判断登录时间是否超时*/
    public function verify_overtime($info){
        $t_token_out_time=$info["login_token_out_time"];//查找表里的时间
        if(time()<strtotime($t_token_out_time)){//判断时间是否超时
            return true;
        }
        return false;
    }
    /*更新token登录时间*/
    public function update_token($init_id,$data){
        return $this->where("id=$init_id")->setField($data);
    }
    /*更新 密码输入错误次数*/
    public function update_wrong($id,$num){
        $this->where("id=$id")->setField("failure_num",$num);
    }
    /*用户名验证--登录*/
    public function login_name($user_name){
        return $this->where("loginName='$user_name'")->find();
    }

    /*扣代理的钱*/
    public function check_new_agency_money($user_id,$pay_money){
        /*扣除相应的代理金额*/
        $user_id=$this->where("binding_playerId=".$user_id)->setDec("money",$pay_money);/*查找代理money*/
        return $user_id;
    }
    /*查找代理是否存在*/
    public function get_admin_init_id($user_id){
        $info=$this->where("binding_playerId=".$user_id)->find();/*查代理列表中代理在不在*/
        if($info){
            return true;
        }
        return false;
    }
    /*更新 禁止操作时间 因为账号输入错误5次*/
    public function update_failure_time($id,$time){
        $this->where("id=$id")->setField("last_failure",date("Y-m-d H:i:s",$time));
    }
    /*更新 密码输入错误次数*/
    public function update_failure($id,$num){
        $this->where("id=$id")->setField("failure_num",$num);
    }

    /*验证给代理充值的money是否超过库存money*/
    public function get_pay_money_save($enter_init_id,$pay_money){
        $info=$this->where("id=".$enter_init_id)->getField("money");/*验证要充值的money是否大于库存money*/
        $money=(int)$info;
        if($money>=$pay_money){
            return true;
        }
        return false;
    }
    /*扣除大区代理财富*/
    public function delete_init_id_money($enter_init_id,$pay_money){
        $user_info=$this->where("id=".$enter_init_id)->setDec("money",$pay_money);
        return $user_info;
    }
    /*给要充值的代理充钱*/
    public function add_init_id_money($by_pay_init_id,$pay_money){
        $result_info=$this->where("id=".$by_pay_init_id)->setInc("money",$pay_money);
        return $result_info;
    }
    /*添加代理*/
    public function add_agency_table($user_id,$default_head,$jlist,$level,$login_name,$login_Password,$phone,$user_name,$md5_password){
        $data["binding_playerId"]=$user_id;
        $data["head"]=$default_head;
        $data["jlist"]=$jlist;
        $data["grade"]=$level;
        $data["loginName"]=$login_name;
        $data["loginPasswd"]=$login_Password;
        $data["loginMD5Passwd"]=$md5_password;
        $data["phone"]=$phone;
        $data["userName"]=$user_name;
        $data["test_agency"]=1;
        $info=$this->add($data);
        return $info;
    }
    /*登录名不可一致*/
    public function judge_agency_login_name_is_add($login_name,$init_id=null){
        $str="loginName='$login_name'";
        if($init_id)$str.=" AND id!=$init_id";
        /*登陆名字是否存在*/
        if($this->where($str)->getField("id")){
            return false;
        }
        return true;
    }
    /*添加大区代理时user_id不可重复*/
    public function judge_agency_user_id_is_add($user_id){
        $user_info=$this->where("binding_playerId=".$user_id)->find();
        if($user_info){
            return false;
        }
        return true;
    }
    /*修改大区代理时user_id不可重复*/
    public function get_plus_agency_user_id($user_id,$init_id){
        $user_info=$this->where("id!=$init_id AND binding_playerId=$user_id")->getField("id");
        if($user_info){
            return false;
        }
        return true;
    }
    /*查代理是否大于指定等级*/
    public function judge_agency_grade($init_id,$grade){
        $info=$this->where("id=".$init_id)->getField("grade");
        if($info>=$grade){
            return true;
        }
        return false;
    }
    /*查指定代理的等级*/
    public function get_appoint_agency_grade($init_id){
        $user_info=$this->where("id=".$init_id)->limit(1)->getField("grade");
        if($user_info){
            return (int)$user_info;//转为数字格式
        }
    }
    /*查代理等级*/
    public function get_agency_grade_seven($grade){
        return $this->where("grade=".$grade)->find();
    }
    /*模糊查询大区代理*/
    public function seek_daqu_agency($seek_keyword,$user_id){
        $user_grade=$this->get_appoint_agency_grade($user_id);//获取当前代理等级
        $id=(int)$seek_keyword;//转为数字
        $de_keyword=urlencode($seek_keyword);//乱码转义 昵称查不到
        return $this->where("grade<$user_grade AND grade>5 AND (id=$id OR userName LIKE '%$de_keyword%' OR phone='$de_keyword' OR binding_playerId=$id)")->getField("id,grade,userName,phone,jlist,createTime,binding_playerId");
    }
    /*代理日新增*/
    public function agency_day_new(){
        $startDate=date('Y-m-d',strtotime("-1 day"));//开始时间-----昨天0点时间
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->newlyIncreasedNum(strtotime($startDate),strtotime($overDate));
    }
    /*代理周新增*/
    public function agency_week_new(){
        $startDate=date('Y-m-d',strtotime( "previous monday" ));//开始时间-----本周一
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->newlyIncreasedNum(strtotime($startDate),strtotime($overDate));
    }
    /*代理月新增*/
    public function agency_month_new(){
        $startDate=date('Y-m-01');//开始时间-----本月一号
        $overDate=date('Y-m-d');//结束时间-----今天0点时间
        return $this->newlyIncreasedNum(strtotime($startDate),strtotime($overDate));
    }
    /*新增代理统计*/
    public function newlyIncreasedNum($sTime,$oTime){
        $num=$this->where("UNIX_TIMESTAMP(createTime)>".$sTime." AND UNIX_TIMESTAMP(createTime)<".$oTime." AND grade=4")->count();
        if(!$num)$num=0;
        return $num;
    }
    /*更新大区代理信息*/
    public function update_plus_agency($init_id,$user_info){
        return $this->where("id=".$init_id)->save($user_info);//直接改，修改大区代理数据
    }
    /*查代理列表中搜索条件是否存在*/
    public function get_agency_seek($keyword){
        if($keyword){ /*判断$keyword是否有值*/
            $while="binding_playerId='$keyword' OR id='$keyword'";//有值时传要搜索user_id,模糊查询init_id
        }else{
            $while="";//没有值代表搜索整个列表
        }
        return $while;
    }
    /*查代理列表*/
    public function get_agency_list($while,$start_page_num,$page_num){
        return $this->where($while)->order("createTime desc")->limit($start_page_num,$page_num)->select();
    }
    /*查代理列表总条数*/
    public function get_agency_list_num($while,$start_page_num,$page_num){
        return $this->where($while)->order("createTime desc")->limit($start_page_num,$page_num)->count();
    }
    /*查代理id，返回数据*/
    public function seek_agency_id($user_info){
        if($user_info){
            $rData=new \stdClass;
            $rData->head=$user_info["head"];
            $rData->createTime=$user_info["createTime"];
            $rData->grade=$user_info["grade"];
            $rData->init_id=$user_info["id"];
            $rData->login_ip=$user_info["login_ip"];
            $rData->newTime=$user_info["newTime"];
            $rData->userName=$user_info["userName"];
            $rData->id=$user_info["binding_playerId"];
            $rData->money=$user_info["money"];
            return $rData;
        }
        return null;
    }
    /*keyword查询用户id*/
    public function keyword_seek_init_id($keyword){
        if($keyword){
            $keyword_init_info=$this->user_id_get_user_info($keyword);//查代理列表中的user_id
            return $keyword_init_info["id"];
        }
        return null;
    }
    /*格式化充值记录列表信息*/
    public function format_user_info2($p_uId,$grade,$state=null,$a_uId=null){
        $u_info=$this->init_id_get_user_info($p_uId);
        $new_info=array();
        if($u_info){
            $new_info["userName"]=$u_info["userName"];
            $new_info["p_uId"]=$u_info["binding_playerId"];
            $new_info["head"]=$u_info["head"];
            $new_info["level"]=$u_info["grade"];
            $new_info["money"]=$u_info["money"];
            $new_info["state"]=$state;
        }
        if($grade>=7&&$a_uId){ //判断代理等级为超管时，可看到所有代理的充值记录
            $a_info=$this->init_id_get_user_info($a_uId);
            $new_info["agencyName"]=$a_info["userName"];
            $new_info["a_uId"]=$a_info["binding_playerId"];
        }
        return $new_info;
    }
    /*判断被搜索的代理等级*/
    public function judge_agency_grade_search($grade,$player_seek_level){
        if($grade==6||$grade==7){ //如果代理等级等于6或7，只可设置s级代理
            $grade=$player_seek_level;//设置的代理等级为5或者4
        }else if($grade==5){ //如果登录者是s级代理
            $grade=4;//设置的权限只可是小代理
        }
        return $grade;
    }
    /*判断被搜索的代理权限*/
    public function judge_agency_jurisdiction_search($grade,$player_seek_level){
        if($grade==6||$grade==7){ //如果代理等级等于6或7，只可设置s级代理或者小代理
            if($player_seek_level==5){//当选择的等级等为5时
                $Jurisdiction=json_encode(C("AGENCY_S_AUTH"));//获取s级代理的权限
            }else if($player_seek_level==4){
                $Jurisdiction=json_encode(C("AGENCY1_AUTH"));//获取小代理的权限
            }
        }else if($grade==5){ //如果登录者是s级代理
            $Jurisdiction=json_encode(C("AGENCY1_AUTH"));
        }
        return $Jurisdiction;
    }
    /*改变该代理的权限和等级为5级标准*/
    public function s_agency_change_grade_jlist($user_id_seek,$grade){
        $s_agency_change_jlist=json_encode(C("AGENCY_S_AUTH"));//获取s级代理的权限
        $user_info_jlist=$this->where("binding_playerId=".$user_id_seek)->setField('jlist',"$s_agency_change_jlist");
        $user_info_grade=$this->where("binding_playerId=".$user_id_seek)->setField('grade',$grade);
        if($user_info_jlist !== false&&$user_info_grade!== false){
            return true;
        }else{
            return false;
        }
    }
    /*改变该代理的权限和等级为4级标准*/
    public function m_agency_change_grade_jlist($user_id_seek,$grade){
        $m_agency_change_jlist=json_encode(C("AGENCY1_AUTH"));
        $user_info_jlist=$this->where("binding_playerId=".$user_id_seek)->setField('jlist',"$m_agency_change_jlist");
        $user_info_grade=$this->where("binding_playerId=".$user_id_seek)->setField('grade',$grade);
        if($user_info_jlist!==false&&$user_info_grade!==false){
            return true;
        }else{
            return false;
        }
    }
    /*扣除指定的代理money*/
    public function deduction_player_money($deduct_id,$deduct_money){
        $user_info=$this->user_id_get_user_info($deduct_id);
        if(intval($user_info["money"])>=intval($deduct_money)){
            $this->where("binding_playerId=".$deduct_id)->setDec("money",$deduct_money);
            return true;
        }
        return false;
    }
    /*转入到指定的代理money*/
    public function add_player_money($deduct_id,$deduct_money){
        return $this->where("binding_playerId=".$deduct_id)->setInc("money",$deduct_money);
    }
    /*大区代理数*/
    public function plus_agency_count(){
        return $this->where("grade=6 AND test_agency=1")->count();
    }
    /*s级代理数*/
    public function s_agency_count(){
        return $this->where("grade=5 AND test_agency=1")->count();
    }
    /*小代理数*/
    public function min_agency_count(){
        return $this->where("grade=4 AND test_agency=1")->count();
    }
    /*设置为测试代理接口*/
    public function set_up_test_agency($user_id,$test_num){
        /*设置test_agency为0*/
        $info=$this->where("binding_playerId=".$user_id)->setField("test_agency",$test_num);
        if($info){
            return true;
        }else{
            return false;
        }
    }




}