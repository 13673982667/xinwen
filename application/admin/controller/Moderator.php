<?php
namespace app\admin\controller;

use Think\Loader;
\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\admin\Controller;

class Moderator extends Controller
{
    use \app\admin\traits\controller\Controller;
    // 方法黑名单
    protected static $blacklist = [];

    protected function filter(&$map)
    {
        if ($this->request->param("ban_name")) {
            $map['ban_name'] = ["like", "%" . $this->request->param("ban_name") . "%"];
        }
    }

        //圈子列表
    public function getModeratorList(){
        $Moderator = Loader::model('Moderator');

        $ModeratorList = $Moderator->select();
        if($ModeratorList){
            return show(1, '', $ModeratorList);
        }
        return show(0);
    }

}
