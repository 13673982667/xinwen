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

    public function news(){
        if(!($news_id = input('news_id')) || !($user_id = input('user_id'))){

        }

         //根据文章获取企业广告
        $guanggao = $this->getNewsGuanggao1($news_id);
        //获取app广告
        $appguanggao = $this->getappguanggao();
        if($guanggao && $appguanggao){
            $this->assign('is_guanggao',1);
            $this->assign('guanggao',$guanggao);
            $this->assign('appguanggao',$appguanggao);
            $this->assign('address_arr',explode(',', $guanggao['arr'][0]['str_address']));
        }
        //获取文章信息
        $newsInfo = (new News(1))->getNewsInfo();
        if(!$newsInfo){
            return '没有这篇文章';
        }
        $this->assign('newsInfo',$newsInfo[0]);
        $SignPackage = self::$getWx->getSignPackage();
        $this->assign('SignPackage',$SignPackage);
        //获取顶部广告列表
        $AppTitleGg = $this->getNewsGg(0);
        $this->assign('AppTitleGg',$AppTitleGg);
        //获取中部广告
        $getNewsGg = $this->getNewsGg(1);
        $this->assign('getNewsGg',$getNewsGg);
        //获取底部广告
        $getNewsbottom = $this->getNewsGg(2);
        $this->assign('getNewsbottom',$getNewsbottom);
        //广告次数+1
        $appconfig = Loader::model('AppConfig');
        $appconfig->where('config_name','news_body_number')->setInc('config_canshu');
        return $this->fetch();
    }

    //拿app文章顶部广告列表
    public function getNewsTitleGg($type = 0){
        $AppTitleGg = Loader::model('AppTitleGg');
        $where['type'] = $type;
        $where['status'] = 1;
        $res = $AppTitleGg->where($where)->select();
        return $res;
    }
    //拿文章中部广告列表
    public function getNewsGg($type = 1){
        $AppTitleGg = Loader::model('AppTitleGg');
        $where['type'] = $type;
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
            return $res[0];
        }
        return false;
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
                $limit = $pagenum ? ($pagenum % $num) : 0;
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