<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2019/3/6
 * Time: 7:24 PM
 */

use Workerman\Worker;
use \Workerman\Lib\Timer;
use \Workerman\Lib\Requests;
use \Workerman\Lib\XiaoeSdk;
use \Workerman\Lib\logger;

require_once __DIR__ . '/Autoloader.php';

header("content-Type: text/html; charset=Utf-8");

date_default_timezone_set('PRC');

class Mail
{
    private static $timer_id;
    private static $config;

    public function _check_order()
    {
        //获取订单列表
        $cmd      = static::$config['order']['cmd'];
        $use_type = static::$config['order']['use_type'];
        $version  = static::$config['order']['version'];

        $params = array(
            'page_size' => static::$config['order']['page_size']
        );

        $result = XiaoeSdk::send($cmd,$params,$use_type,$version);

        if($result['code'] == 0 && $result['msg'] == 'success')
        {

            foreach ($result['data'] as $value)
            {
                //已支付订单,触发邮件、短信提醒
                if($value['order_state'] == 1)
                {
                    $created_at = strtotime($value['created_at']);

                    //30秒之内的订单发送邮件,定时器每三十秒拿一次订单数据
                    if((time() - $created_at) < 30)
                    {
                        $con = sprintf('监测新订单:%s,user_id:%s,created_at:%s',$value['order_id'],$value['user_id'],$value['created_at']);
                        logger::add($con,'news_order_success.log','info');
                        static::send_email($value['user_id'],$value['title']);
                    }
                    else
                    {
                        continue;
                        //echo "暂时没有新订单~".PHP_EOL;
                    }
                }
            }
        }
    }

    public function send_email($user_id = 1,$title)
    {

        $email_user_config = static::$config['email']['focusUser'];

        $SoapClient = new SoapClient(static::$config['soap_client'],array('trace' => 1));

        $FocusUser = array(
            'Email'     => $email_user_config['Email'],
            'Password'  => $email_user_config['Password'],
        );


        $FocusEmail = array(
            'Body'=>'Halo baby,This is demo verison!'.date('Y-m-d H:i:s',time()),
            'IsBodyHtml'=> $email_user_config['IsBodyHtml']
        );


        $email_task_config = static::$config['email']['focusTask'];

        $FocusTask = array(
            'TaskName'    => $email_task_config['TaskName'],
            'SenderEmail' => $email_task_config['SenderEmail'],
            'ReplyName'   => "Reply",
            'ReplyEmail'  => $email_task_config['ReplyEmail'],
            'SendDate'    => date('Y-m-d\TH:m:s',time()),
        );

        $subject="【幕后圈课堂】感谢您购买{$title}混音系列课程。{$FocusTask['SendDate']}";

        $FocusReceiver  = array(
            'Email'=>'465360967@qq.com',
        );


        //send one email
        $result= $SoapClient->SendOne(array("user"=>$FocusUser,"email"=>$FocusEmail,"subject"=>$subject,"receiver"=>$FocusReceiver));

        print_r($result) ;
    }

    public function _init()
    {
        static::$config = include_once "Lib/Config.php";
        static::$timer_id = Timer::add(3, array($this, '_check_order'), array(), true);
    }
}

$task = new Worker();
$task->count = 10;
$task->onWorkerStart = function($task)
{

    if($task->id === 0)
    {
        $mail = new Mail();
        $mail->_init();
    }

};
// 运行worker
Worker::runAll();