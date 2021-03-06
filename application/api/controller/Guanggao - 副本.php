<?php
namespace app\api\controller;
use think\Loader;
use app\common\lib\exception\ApiException;
use think\Config;
use think\Controller;
use think\assign;
use think\View;
use think\Request;
/**
 * 广告
 */
class Guanggao extends Common
{   
    static $getWx;
    // static $postObj; //微信推送过来的POST数据(对象);
    function __construct(){
        parent::__construct();
        $appid = 'wx7fdb0e3e354c5567';
        $appSecret = '3326e00a8c64fe1b65e8f6265eb8d960';
        self::$getWx = new JSSDK($appid,$appSecret);                            

    }

	//新闻页面
	public function news(){
		if(!($news_id = input('news_id')) || !($user_id = input('user_id'))){

        }
        $news_url = input('news_url'); //照顾没升级的用户
        $this->assign('news_url',$news_url);
        
         //根据文章获取企业广告
        // $ModeratorController = new Moderator();
        $guanggao = $this->getNewsGuanggao1($news_id);
        //获取app广告
        $appguanggao = $this->getappguanggao();

        if($guanggao && $appguanggao){
        	$this->assign('is_guanggao',1);
        	$this->assign('guanggao',$guanggao);
        	$this->assign('appguanggao',$appguanggao);
        }
        // $this->assign('user_id',$user_id);
        // // $this->assign('news_url',$news_url);
        // $this->assign('news_id',$news_id);

        //获取文章信息
        $newsInfo = (new News(1))->getNewsInfo();
        if(!$newsInfo){
        	return '没有这篇文章';
        }
        $this->assign('newsInfo',$newsInfo[0]);
        // p($newsInfo['w_cover']);

        // $userInfo = (new Users())->getInfo($user_id);
        // $this->assign('userInfo',$userInfo);

        //文章顶部广告
        // $AppTitleGg = $this->getNewsTitleGg();
        // $this->assign('AppTitleGg',$AppTitleGg);
        
        //中部广告
        // $getNewsGg = $this->getNewsGg();
        // $this->assign('getNewsGg',$getNewsGg);

        //app下载地址
        // $AppUpdateUrl = $this->getAppUpdateUrl();
        // $this->assign('AppUpdateUrl',$AppUpdateUrl);

		// $str = getUrlContent($news_url);
		// $this->assign('str',file_get_contents($news_url));
        // $this->assign('news_url',\think\Url::build('Guanggao/news_content').'?news_url='.urlencode($news_url));
        // $this->assign('news_url',urldecode($news_url));
        return $this->fetch(); 
	}

    public function news1(){

        return $this->fetch(); 
    }

	//拿app文章顶部广告列表
	public function getNewsTitleGg(){
		$AppTitleGg = Loader::model('AppTitleGg');
		$where['type'] = 0;
		$where['status'] = 1;

		$res = $AppTitleGg->where($where)->select();
		return $res;
	}
	//拿文章中部广告列表
	public function getNewsGg(){
		$AppTitleGg = Loader::model('AppTitleGg');
		$where['type'] = 1;
		$where['status'] = 1;

		//拿当前广告显示次数 
		$appconfig = Loader::model('AppConfig');
        $pagenum = $appconfig->where('config_name','news_body_number')->value('config_canshu');
        //广告条数
        $ggnum = $AppTitleGg->where($where)->count();
		$limit = ($pagenum && $ggnum) ? ($pagenum % $ggnum) : 0;
		//取那条广告
    	$res = $AppTitleGg->where($where)->limit($limit, 1)->fetchSql(false)->select();

        if($res){
            //广告次数+1
            $appconfig->where('config_name','news_body_number')->setInc('config_canshu');
            return $res[0];
        }
		return false;
	}


	public function news_content(){
		if(!($news_url = urldecode(input('news_url')))){
			return show(0,'error');
        }
		$str = file_get_contents($news_url);
		return $str;
	}

	//获取版本信息
	public function getversion(){
		$appconfig = Loader::model('AppConfig');
		$version = $appconfig->where('config_name','version')->value('config_canshu');
		return show(1,'',$version);
	}

	//获取app的下载地址
	public function getAppUpdateUrl(){
		$appconfig = Loader::model('AppConfig');
		$url = $appconfig->where('config_name','appUpdateUrl')->value('config_canshu');
		return $url;
	}
	//获取app广告
    public function getappguanggao(){
        // $GuanggaoConfig = Loader::model('GuanggaoConfig');
        // $res = $GuanggaoConfig->where('id',1)->find();
        // if($res){
        //     return $res;
        // }
        // return false;
        $GuanggaoConfig = Loader::model('GuanggaoConfig');
        $appconfig = Loader::model('AppConfig');
        //拿当前广告显示次数 
        $pagenum = $appconfig->where('id',1)->value('pagenum');
        //广告条数
        $Gwhere = ['type'=>0,'status'=>0];
        $ggnum = $GuanggaoConfig->where($Gwhere)->count();
        $limit = ($pagenum && $ggnum) ? ($pagenum % $ggnum) : 0;
        // p($pagenum,1);
        //取那条广告
        $res = $GuanggaoConfig->where($Gwhere)->limit($limit, 1)->select();
        if($res){
            //广告次数+1
            $appconfig->where('id',1)->setInc('pagenum');
            return $res[0];
        }
        return false;
    }

	//根据文章id返回广告信息 (改为一次只显示一条)
    public function getNewsGuanggao1($news_id){
        if(!$news_id){
            return false;
        }
        //查询发送文章时的企业id
        $moderator_id = Loader::model('News')->where('id',$news_id)->value('moderator_id');
        if($moderator_id){
            $where = [
                'moderator_id'=>$moderator_id,
                'status' => 1
            ];
            $Guanggao = Loader::model('Guanggao');
            //拿当前广告显示次数   广告时间
            $Moderator = Loader::model('Moderator');
            $M_ = $Moderator->where('id',$moderator_id)->find();
            $pagenum = $M_['pagenum'];  //广告次数
            //修改次数 +1
            $Moderator->where('id',$moderator_id)->setInc('pagenum'); 
            //广告条数
            $num = $Guanggao->where($where)->count();
            if($num){
                $limit = $pagenum ? ($pagenum % $num) : 1;
            }else{
                return false;
            }
            
            // p($limit,1);
            $arr = $Guanggao->where($where)->limit($limit, 1)->select();
            if($arr){
                return ['arr'=>$arr,'ggtime'=>$M_['ggtime']];
            }
        }
        return false;
    }
}