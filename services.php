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
                    print_r(static::_get_user_info($value['user_id']));exit();
                    $created_at = strtotime($value['created_at']);

                    //30秒之内的订单发送邮件,定时器每三十秒拿一次订单数据
                    if((time() - $created_at) < 30)
                    {
                        $con = sprintf('监测到新订单:%s,user_id:%s,created_at:%s',$value['order_id'],$value['user_id'],$value['created_at']);
                        logger::add($con,'news_order_success.log','info');
                        static::send_email($value['user_id'],$value['title']);
                    }
                    else
                    {
                        continue;
                    }
                }
            }
        }
    }

    public function _get_user_info($user_id)
    {
        $cmd    = static::$config['information']['cmd'];

        $params = array(
            'user_id'   =>  $user_id
        );

        $result = XiaoeSdk::send($cmd,$params,0,'1.0');

        $user_info = array();

        if($result['code'] == 0  && $result['msg'] == 'ok')
        {
            foreach ($result['data']['list'] as $key => $val)
            {
                foreach ($val['information_collections'] as $collection)
                {
                    switch ($collection['component_type'])
                    {
                        case 'email':
                            $user_info['email'] = $collection['component_answer'];
                            break;
                        case 'phone':
                            $user_info['phone'] = $collection['component_answer'];
                            break;
                        default:
                            $user_info = array(
                                'email'=> '',
                                'phone'=> ''
                            );
                    }
                }
            }
        }
        return $user_info;
    }

    public function send_email($title)
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

        $subject="【xxx】感谢您购买{$title}混音系列课程。{$FocusTask['SendDate']}";

        $FocusReceiver  = array(
            'Email'=>'465360967@qq.com',
        );


        //send one email
        $result= $SoapClient->SendOne(array("user"=>$FocusUser,"email"=>$FocusEmail,"subject"=>$subject,"receiver"=>$FocusReceiver));

        print_r($result) ;
    }

    public function send_sms()
    {
        $time_stamp = date('YmdHis',time());

        $url  = static::$config['sms']['sms_url'];
        $sign = static::$config['sms']['sign'];

        $params = array(
            'userid'=>static::$config['sms']['userid'],
            'timestamp'=>$time_stamp,
            'sign'=>md5($sign.$time_stamp),
            'mobile'=>'xxx',
            'content'=>'Halo baby,This is the demo version!',
            'sendTime'=>'',
            'action'=>'send',
            'extno'=>''
        );


        $res = Requests::post($url,$params,false,false,null);

        var_dump($res);

        print_r($params);
    }

    public function _init()
    {
        static::$config   = include_once "Lib/Config.php";
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