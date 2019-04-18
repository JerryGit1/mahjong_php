<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/12/28
 * Time: 18:54
 */
namespace HomeGM\Controller;
use HomeGM\Model\AdminModel;
use Think\Controller;
use Think\Log;

class BaseController extends Controller {
    /*安全防护第一层校验token*/
    public function verify_token(){
        $init_id=$this->getParamInfo("token_userId",2,true);//代理的原始id
        $v_token=$this->getParamInfo("token",2,true);//要校验的token  MD5
        $admin=new AdminModel();
        $info=$admin->init_id_get_user_info($init_id);//查找这人整条信息
        if($info){
            $tf_token=$admin->verify_token($v_token,$info);//验证token是否相等
            $t_token_out_time=$admin->verify_overtime($info);//判断时间是否超时
            if($tf_token){
                /*验证token过没过时间*/
                if(!$t_token_out_time){
                    $this->returnGameData(-1,null,"登录超时 请刷新");
                }
            }else{
                $this->returnGameData(-1,null,"token失效请重新登录");
            }
        }else{
            $this->returnGameData(0,null,"非法操作");
        }
    }

    /**
     * 接收前端参数
     *判断 post get 参数是否存在
     *@param $param {string} 参数名字
     *@param $nullTipsType {int} 不存在提示 信息类型 0不做操作 1 跳转错误面板 2返回json信息
     * @param $_isPost {string} 是否必须用post
     * @return  Object
     */
    public function getParamInfo($param,$nullTipsType=1,$_isPost=false){
        if(isset($_GET[$param])&&$_GET[$param]!="undefined"&&$_GET[$param]!="null"&&!$_isPost)
        {
            return I('get.'.$param);
        }
        else if(isset($_POST[$param])){
            return I('post.'.$param);
        }
        else{
            if($nullTipsType==1){//跳转错误页面
            }
            else if($nullTipsType==2) {//返回json信息
                $this->returnGameData(0,null,$param."为空");
            }
        }
        return null;
    }


    /**
     * 返回数据
     * 统一定义 给前台返回的数据
     * @param $state {int} 1成功 2失败
     * @param $message {String} 失败原因或其他提示
     * @param $info {Object} 成功时 返回的数据
     * @param $other {Object} 其他数据 （mysql报错）
     */
    public function returnGameData($state,$info=null,$message="",$other=null){
        //基础信息
        $rData=new \stdClass();
        $rData->state=$state;
        $rData->message=$message;
        $rData->info=$info;
        //错误信息
        $mysqlErrorStr=mysql_error();
        if($mysqlErrorStr!="")$other.="#mysql报错".$mysqlErrorStr;
        $rData->other=$other;
        //打印错误日志
        if(!empty($other)){
//            Log::record("后端报错".$other);
        }
        //返回前台数据
        $this->ajaxReturn($rData);
        exit;
    }


    /***
     *登陆操作时查看用户是否有登陆次数超限限制 登陆异常
     **/
    public function lookUserLoginAbnormal($last_failure=null,$init_id=null){
        /*实例化 表model*/
        if(!$last_failure){
            $admin_model=new AdminModel();
            $info=$admin_model->init_id_get_user_info($init_id);
            Log::record(json_encode($info));
            if(!$info){
                $this->returnGameData(0,null,"非法操作");
            }
            $last_failure=$info["last_failure"];
        }
        $lTime=strtotime($last_failure);
        $time=time()-$lTime;
        if($time<=300){
            $this->returnGameData(0,null,"账号异常请".(300-$time)."s后尝试操作");
        }
    }
    /**
     *MD5的密码生成
     **/
    public function createMD5Password($password,$char){
        return md5(C($char).$password.C($char));
    }

    /***
     *权限限定
     */
    public function jurisdiction_limit($level,$tipsType=0){
        /*安全操作校验*/
        $this->verify_token();
        $admin_init_id=$this->getParamInfo("token_userId",2,true);
        /*校验管理员级别*/
        $admin_model=new AdminModel();
        $admin_info=$admin_model->user_id_get_user_info($admin_init_id);
        if($admin_info&&$admin_info["grade"]<$level){
            if($tipsType==0)
                $this->returnGameData(0,null,"权限不足");
            else if($tipsType==1)$this->returnGameData(1,null,"权限不足");
        }
        return $admin_info["grade"];
    }

}