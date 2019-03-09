<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2019/3/9
 * Time: 2:08 PM
 */

  header("content-Type: text/html; charset=Utf-8");

  date_default_timezone_set('PRC');

  $SoapClient = new SoapClient("https://edm-api.focussend.com/webservice/focussendWebservice.asmx?WSDL",array('trace' => 1));

  $FocusUser = array(
      'Email'     => 'xxx',
      'assword'  =>  sha1("xxx")
  );

  $FocusEmail = array(
      'Body'=>'Halo baby,This is demo verison!'.date('Y-m-d H:i:s',time()),
      'IsBodyHtml'=>true
  );


  $FocusTask = array(
      'TaskName'    => "xxxx".time(),
      'SenderEmail' => "xxxx",
      'ReplyName'   => "Reply",
      'ReplyEmail'  => "xxx",
      'SendDate'    => date('Y-m-d\TH:m:s',time()),
  );


//  $FocusTask->Subject="zhe";
//  $FocusTask->SenderName="abc"; //发件人名称


  $subject="【xxxx】{$FocusTask['SendDate']}";

  $FocusReceiver  = array(
      'Email'=>'xxxx',
  );


  //send one email
  $result= $SoapClient->SendOne(array("user"=>$FocusUser,"email"=>$FocusEmail,"subject"=>$subject,"receiver"=>$FocusReceiver));

  print_r($result) ;
?>