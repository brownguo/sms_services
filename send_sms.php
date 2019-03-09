<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2019/3/9
 * Time: 1:08 PM
 */


date_default_timezone_set('PRC');

include './helper/requests.php';


//echo date('YmdHis',time()).PHP_EOL;


$time_stamp = date('YmdHis',time());


$url = 'http://39.104.141.71:8088/v2sms.aspx?action=send';


$sign = 'jy865122jy865122';
$params = array(
    'userid'=>183,
    'timestamp'=>$time_stamp,
    'sign'=>md5($sign.$time_stamp),
    'mobile'=>'18513558982',
    'content'=>'Halo baby,This is the demo version!',
    'sendTime'=>'',
    'action'=>'send',
    'extno'=>''
);


$res = requests::post($url,$params,false,false,null);

var_dump($res);

print_r($params);