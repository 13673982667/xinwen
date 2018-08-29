<?php
namespace app\admin\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\admin\Controller;

class ModeratorInfo extends Controller
{
    use \app\admin\traits\controller\Controller;
    // 方法黑名单
    protected static $blacklist = [];

    protected static $isdelete = false;


    protected function filter(&$map)
    {
        if(!empty($this->maparr)){
            $map = array_merge($map,$this->maparr);
        }
    }

   public function userlist(){

   		// $this->maparr['moderator_id'] = input('id');
   		$this->view_html = 'index';
   		return $this->index();
   }
}
