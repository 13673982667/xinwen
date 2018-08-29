<?php
namespace app\admin\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\admin\Controller;
use think\Loader;
class Users extends Controller
{
    use \app\admin\traits\controller\Controller;
    // 方法黑名单
    protected static $blacklist = [];

    protected function filter(&$map)
    {
        if ($this->request->param("user_name")) {
            $map['user_name'] = ["like", "%" . $this->request->param("user_name") . "%"];
        }
        if ($this->request->param("province")) {
            $map['province'] = ["like", "%" . $this->request->param("province") . "%"];
        }
        if ($this->request->param("city")) {
            $map['city'] = ["like", "%" . $this->request->param("city") . "%"];
        }
        if ($this->request->param("country")) {
            $map['country'] = ["like", "%" . $this->request->param("country") . "%"];
        }
    }

      //获取没有加入圈子的用户
    public function notModeratorUserList(){
        //查询圈主的id    圈子会员id
        $Moderator = Loader::model('Moderator');
        $quanzhu_id = $Moderator->column('user_id');
        $str_id = '';
        if($quanzhu_id){
            $str_id .= implode(',',$quanzhu_id);
        }

        $ModeratorInfo = Loader::model('ModeratorInfo');
        $chengyuan_id = $ModeratorInfo->column('user_id');
        if($chengyuan_id){
            $str_id .= ','.implode(',',$chengyuan_id);
        }

        $Users =  Loader::model('Users');
        $UserList = $Users
        ->where('id','not in',$str_id)
        ->select();

        if($UserList){
            return show(1, '', $UserList);
        }
        return show(0);

    }
}
