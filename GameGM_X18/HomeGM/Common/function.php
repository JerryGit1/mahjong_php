<?php
/**
* 创建者 伟大的周鹏斌大王 
* 时间 2017/6/1 17:35
*/


/*添加代理给玩家充值统计*/
function statistic_user_pay($s_count,$uId="user_pay"){
    $param=new \stdClass();
    $param->p_name=C('STATISTIC_NAME');
    $param->o_name="a_pay";
//    $param->u_id=$uId;
    $param->s_count=$s_count;
    $info=service_http_post(C('STATISTIC_ADD_URL'),$param);
    AH_trace($info);
}
/*添加代理购买统计*/
function statistic_admin_buy($uId="admin_pay",$s_count){
    $param=new \stdClass();
    $param->p_name=C('STATISTIC_NAME');
    $param->o_name="a_buy";
//    $param->u_id=$uId;
    $param->s_count=$s_count;
    service_http_post(C('STATISTIC_ADD_URL'),$param);
}

//异步请求数据
function service_http_post($url,$data){
    $curl = curl_multi_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);//超时
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl,CURLOPT_BINARYTRANSFER,true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
    curl_setopt($curl, CURLOPT_URL, $url);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

//请求数据
function service_http_post_sync($url,$data){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);//超时
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl,CURLOPT_BINARYTRANSFER,true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
    curl_setopt($curl, CURLOPT_URL, $url);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

/*打印特定日志*/
function AH_trace($str){
    \Think\Log::write($str,null,null,C('LOG_PATH')."local.log");
}


