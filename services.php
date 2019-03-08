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
    private static $time_interval = 0.5;
    private static $timer_id;

    public function send($to, $content)
    {
        echo "send mail".date('Y-m-d H:i:s',time()).PHP_EOL;
    }

    public function sendLater($to, $content)
    {
        self::$timer_id = Timer::add(self::$time_interval, array($this, 'send'), array($to, $content), true);
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