<?php
namespace app\admin\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\admin\Controller;

class GuanggaoConfig extends Controller
{
    use \app\admin\traits\controller\Controller;
    // 方法黑名单
    protected static $blacklist = [];

    protected static $isdelete = false;



    //打开文章时广告底部的app广告
    public function dibu(){
    	$type = 0;
    	$this->view->assign('type',$type);
		$this->maparr['type'] = $type;
    	$this->view_html = 'index';
   		return $this->index();
    }

    //app文章顶部广告
    public function news_title_gg(){

    	$type = 1;
		$this->view->assign('type',$type);
		$this->maparr['type'] = $type;
    	$this->view_html = 'index';
   		return $this->index();
    }
}
