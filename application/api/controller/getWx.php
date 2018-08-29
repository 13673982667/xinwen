<?php

namespace app\api\controller;
/**
 * 
 * 获取微信的一些接口
 * @version $Id$
 */

class getWx{
    
    function __construct(){
        
    }


    /**
     * curl方法
     * @param  string $url 
     * @param  string $type 
     * @return [type]      [description]
     */
    public function http_curl($url , $type = 'json' ){
        //获取
        //1.初始化curl
        $ch = curl_init();
        //2.设置curl参数
        curl_setopt($ch, CURLOPT_URL, $url); //设置url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在  
        //因为“https”是加密的，所以要在curl设置参数里面加上上面两句话，才能得到access_token吧， 不然会得到null！ 
        //3.采集
        $output = curl_exec($ch);                                             
        //如果有错  要在curl_close($_ch); 之后，要不然就失去资源句柄了
        if(curl_errno($ch)){
            curl_error($ch);
        }
        //4.关闭
        curl_close($ch);
        //如果是json
        if($type == 'json'){
            return json_decode($output, true);
        }else if($type == 'xml'){
            return simplexml_load_string($output);
        }
    }//


    /**
     * 获取微信AccessToken
     * @return [type] [description]
     */
    public function getWxAccessToken(){
        if(isset($_COOKIE['access_token']) && isset($_COOKIE['expires_in']) && $_COOKIE['expires_in'] >= time()){
            return $_COOKIE['access_token'];
        }else{//没有再获取
        	//1.请求地址
        	$appid = 'wx3a2b9b509efeb6fd';
        	$appsecret = '25482736bf21a34ca8f65a26d8eb0d14';
        	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
        	
            $arr = self::http_curl($url, 'json');
            print_r( $arr );die();
            //保存cookie
            setcookie('access_token', $arr['access_token'] , time()+$arr['expires_in']);
            setcookie('expires_in', $arr['expires_in'] + time(), time()+$arr['expires_in']);
            return $arr['access_token'];
        }
    }

    /**
     * 获取微信的ip地址
     * @return [type] [description]
     */
    public function getWxServerIp(){
    	$AccessToken = self::getWxAccessToken();

    	$url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token='.$AccessToken;
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$res = curl_exec($ch);
		if(curl_errno($ch)){
		    curl_error($ch);
		}
        curl_close($ch);
		$arr = json_decode($res, true);
        print_r($arr);
		return $arr;

    }

}