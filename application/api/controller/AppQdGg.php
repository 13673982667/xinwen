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
 * 文章
 */
class AppQdGg extends Common
{   
		
	//获取app启动广告
	public function getAppQdGg(){
		// $AppQdGg = Loader::model('AppQdGg');
		$where = [
			'status' => 1,
		];
		// $res = $AppQdGg->where($where)->select();
		$res = $this->getappqdguanggao();
		$time = $this->getAppGgTime();
		if($res){
			return show(1,'',['res'=>$res,'time'=>$time]);
		}
		return show(0);
	}

	//获取app启动广告时间
	public function getAppGgTime(){
		$AppConfig = Loader::model('AppConfig');
		$where = [
		'config_name' => 'lunbo_time',
		];
		$res = $AppConfig->where($where)->value('config_canshu');
		if($res){
			return $res;
		}
		return false;
	}

	 //获取app广告
    public function getappqdguanggao(){
        $AppQdGg = Loader::model('AppQdGg');
        $appconfig = Loader::model('AppConfig');
        //拿当前广告显示次数 
        $pagenum = $appconfig->where('config_name','lunbo_time')->value('pagenum');
        //广告条数
        $Gwhere = ['status'=>1];
        $ggnum = $AppQdGg->where($Gwhere)->count();
        $limit = ($pagenum && $ggnum) ? ($pagenum % $ggnum) : 1;
        // p($pagenum,1);
        //取那条广告
        $res = $AppQdGg->where($Gwhere)->limit($limit, 1)->select();
        if($res){
            //广告次数+1
            $appconfig->where('config_name','lunbo_time')->setInc('pagenum');
            return $res[0];
        }
        return false;
    }

}