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


require_once __DIR__ . '/Autoloader.php';

class Mail
{
    private static $time_interval = 1;
    private static $timer_id;
    private static $version  = '2.0';
    private static $use_type = '0';


    public function check_orders($to, $content)
    {
        //获取订单列表
        $cmd    = "order.list.get";
        $params = array();

        $result = XiaoeSdk::send($cmd,$params,self::$use_type,self::$version);

        if($result['code'] == 0 && $result['msg'] == 'success')
        {

            print_r($result);
//            foreach ($result['data'] as $value)
//            {
//
//                //已支付订单,触发邮件、短信提醒
//                if($value['order_state'] == 1)
//                {
//                    $created_at = strtotime($value['created_at']);
//
//                    //30秒之内付款的发送邮件
//                    if((time() - $created_at) < 30)
//                    {
//
//                    }
//                }
//            }
        }
    }

    public function sendLater($to, $content)
    {
        self::$timer_id = Timer::add(self::$time_interval, array($this, 'check_orders'), array($to, $content), true);
    }
}

$task = new Worker();
$task->count = 10;
$task->onWorkerStart = function($task)
{

    if($task->id === 0)
    {
        $mail = new Mail();
        $to = 'workerman@workerman.net';
        $content = 'hello workerman';
        $mail->sendLater($to, $content);
    }

};
// 运行worker
Worker::runAll();