<?php
namespace app\api\controller;
use think\Loader;
use think\Db;
use app\common\lib\exception\ApiException;
use think\Config;
/**
 * 用户
 */
class Register extends Common
{   

	public function login (){
		if(!($password = input('password')) || !($phone = input('phone'))){
			return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
		}
		$password = md5($password);
		$Users = Loader::model('Users');
		$where = [
			'password' => $password,
			'phone'  => $phone
		];
		if($res = $Users->where($where)->find()){
			return show(1,'',$res['id']);
		}
		return show(0);
	}

	public function send(){
	    $phone=$_GET['phone'];
	    $rands=rand(1000,9999);
	    $content='您的注册验证码为：'.$rands.'。验证码有效期为5分钟，请尽快填写！';
	//  $url ="http://106.ihuyi.com/webservice/sms.php?method=Submit&account=cf_huke&password=wyx037798&mobile=".$phone."&content=".$content;
	    $url ="http://106.ihuyi.com/webservice/sms.php?method=Submit&account=cf_huke&password=wyx037798&mobile$phone&content=$content";

	    // $url = "";
	    $data = file_get_contents($url);
	    $xml = simplexml_load_string($data);
	    $arr=array('code'=>0,'rand'=>$rands,'phone'=>$phone);
	    echo json_encode($arr);exit;
	}
	public function register(){
		$data = [
			'phone' => input('phone'),
			'password' => md5(input('password'))
		];
		$Users = Loader::model('Users');
		if($res = $Users->where('phone',$data['phone'])->find()){
			return show(-1,'','手机号被注册');
		}
		if($user_id = $Users->insertGetId($data)){
			return show(1,'',$user_id);
		}
		return show(0);
	}

}