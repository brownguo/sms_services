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


$url = 'xxxx';


$sign = 'xxx';
$params = array(
    'userid'=>183,
    'timestamp'=>$time_stamp,
    'sign'=>md5($sign.$time_stamp),
    'mobile'=>'xxxx',
    'content'=>'Halo baby,This is the demo version!',
    'sendTime'=>'',
    'action'=>'send',
    'extno'=>''
);


$res = requests::post($url,$params,false,false,null);

var_dump($res);

print_r($params);