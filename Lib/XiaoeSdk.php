<?php

/**
 * Created by PhpStorm.
 * User: vinceyu
 * Date: 2017/4/8
 * Time: 上午9:33
 *
 * 小鹅通分销平台sdk
 */

namespace Workerman\Lib;

class XiaoeSdk
{

    static private $appId="xxxx"; //小鹅通分配给平台的app_id
    static private $appSecret="xxxx"; //小鹅通分配给平台的appSecret
    static private $useType=0; //数据的使用场景 可根据实际实际情况传入0-服务端自用，1-iOS，2-android，3-pc浏览器，4-手机浏览器
    static private $version="1.0";    //接口版本数

    /**
     * 发送请求
     * @param $cmd string 请求命令字
     * @param $paramsArray array 请求业务参数
     * @param $use_type //数据的使用场景 可根据实际实际情况传入0-服务端自用，1-iOS，2-android，3-pc浏览器，4-手机浏览器
     * @param null $customNetSender function 使用自定义的网络发送模块
     * @return mixed|null 请求结果
     */
    static public function send($cmd, $paramsArray, $use_type = 0, $version=self::version)
    {
        self::$useType = $use_type;
        $url = "http://api.xiaoe-tech.com/open/";

        // 设置好接口命令字
        $url = $url . $cmd . '/' . $version;
        // 参数进行加密操作
        $paramsArray = self::getEncodeData($paramsArray);
        $paramStr = json_encode($paramsArray);
        $resultJson = null;
        try {
            //调用sdk自动发送
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);//30秒超时
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramStr);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array('Content-Type: application/json; charset=utf-8','Content-Length:' . strlen($paramStr)));
            $resultJson = curl_exec($ch);
            curl_close($ch);
            $resultJson = json_decode($resultJson, true);
        } catch (Exception $e) {
        }

        if ($resultJson != null && $resultJson['code'] == 0 && is_array($resultJson['data'])) {
            $resultJson = self::getDecodeData($resultJson);
        }
        return $resultJson;
    }

    /**
     * 获取签名后的数据数组
     * @param $params
     * @return mixed
     */
    static private function getEncodeData($params)
    {
        //业务数据
        $paramsArray['data'] = $params;
        // 时间戳，添加一个
        $paramsArray['timestamp'] = time();
        // appid，添加一个
        $paramsArray['app_id'] = self::$appId;
        //使用方式
        $paramsArray['use_type'] = self::$useType;
        // 生成校验sign
        $md5 = self::createSign($paramsArray);
        $paramsArray['sign'] = $md5;
        return $paramsArray;
    }

    /**
     * 获取解码结果
     * 非业务字段：sign（仅此字段不参与加密串）、timestamp、appid
     * 异常时返回错误原因，正常返回业务数据数组
     * @param $paramsArray array 服务器返回的数据数组
     * @return string
     */
    static private function getDecodeData($paramsArray)
    {
        if (key_exists('app_id', $paramsArray) && $paramsArray['app_id'] == self::$appId) {
            if(!key_exists('sign', $paramsArray)){
                return ['code'=>'100','msg'=>'加密串校验出错','data'=>[]];
            }
            $serverReturnSignString = $paramsArray['sign'];//服务器返回的加密串
            unset($paramsArray['sign']);    //sign不参与加密
            $md5 = self::createSign($paramsArray);
            // 校验
            if ($serverReturnSignString == $md5) {
                // 校验通过
                return ['code'=>$paramsArray['code'],'msg'=>$paramsArray['msg'],'data'=>$paramsArray['data']];
            } else {
                return ['code'=>'100','msg'=>'加密串校验出错','data'=>[]];
            }
        } else {
            return ['code'=>'100','msg'=>'服务器返回app_id异常','data'=>[]];
        }
    }

    /**加密方法
     * @param $params
     * @return string
     */
    static private function createSign($params){
        // 根据键名对字典序进行排序
        ksort($params);
        $rawString = '';
        $data_raw = [];
        foreach ($params as $key => $value) {
            if (is_array($value)||is_object($value)) {
                //如果是数组，将数据json一下
                $returnedValue = json_encode($value,JSON_UNESCAPED_UNICODE );
            }else{
                //默认字符串取原值
                $returnedValue = (string)$value;
            }
            $data_raw[] = $key . '=' . $returnedValue;
        }
        $data_raw[] = 'app_secret='.self::$appSecret;//添加app_secret
        $rawString = join('&',$data_raw);//拼接成字符串
        // 校验
        $sign = strtolower(md5($rawString));
        return $sign;
    }
}