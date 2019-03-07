<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2019/3/6
 * Time: 7:24 PM
 */

use Workerman\Worker;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/Autoloader.php';

class Mail
{
    // 注意，回调函数属性必须是public
    public function send($to, $content)
    {
        echo "send mail".PHP_EOL;
    }

    public function sendLater($to, $content)
    {
        // 回调的方法属于当前的类，则回调数组第一个元素为$this
        Timer::add(1, array($this, 'send'), array($to, $content), false);
    }
}

$task = new Worker();
$task->count = 5;
$task->onWorkerStart = function($task)
{
    // 10秒后发送一次邮件
    $mail = new Mail();
    $to = 'workerman@workerman.net';
    $content = 'hello workerman';
    $mail->sendLater($to, $content);
};

// 运行worker
Worker::runAll();