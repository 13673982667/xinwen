<?php
namespace app\api\controller;
use think\Loader;
use think\Db;
use app\common\lib\exception\ApiException;
use think\Config;
use app\common\Pinyin\Pinyin;
// use Moderator;
/**
 * 用户
 */
class Users extends Common
{   
    protected $timestr = 'Y-m-d H-i-s';

    //获取用户信息
    public function info()
    {   
        if(!input('get.user_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $user_id = input('get.user_id');
    	$UsersCollect = Loader::model('UsersCollect');  //用户关注 实例化
        $Users = Loader::model('Users');                //用户 实例化
        $News = Loader::model('News');                  //文章
    	//获取用户关注人数
    	$CollectionsNum = $UsersCollect->getCollectionsNum($user_id);
        $CollectionsNum = $this->getUserGuanzhuQiYe($user_id);
        //获取用户粉丝数 
        $FansNum = $UsersCollect->getFansNum($user_id);
        //获取用户文章数
        // $NewsNum = $News->getUserNewsNum($user_id);
        //获取用户文章总点击量
        // $NewsDianjiliangCount = $News->getUserNewsCountDianjiliang($user_id);
        //获取用户全部文章被分享次数
        // $NewsShareCount = $News->getNewsShareCount($user_id);
        //获取用户信息
        $UserInfo = $Users->getUserInfo($user_id);
        //获取Moderator表
        $Moderator = Loader::model('Moderator')->field('id,user_id,create_time,rank')->where('user_id',$user_id)->find();
        
        $MFansNum = 0;
        $NewsNum = 0;
        $NewsShareCount = 0;
        $NewsDianjiliangCount = 0;
        if($Moderator){
            //获取企业粉丝数
            $MFansNum = $this->getMFansNum($Moderator['id']);
            //获取用户在当前企业下的文章数
            $NewsNum = $this->getUserNewsNum($user_id,$Moderator['id']);
            //获取用户全部文章被分享次数
            $NewsShareCount = $this->getNewsShareCount($user_id,$Moderator['id']);
            //获取用户文章总点击量
            $NewsDianjiliangCount = $this->getUserNewsCountDianjiliang($user_id,$Moderator['id']);
        }
        //ModeratorInfo
        $ModeratorInfo = Loader::model('ModeratorInfo')->field('id,user_id,create_time,moderator_status')->where('user_id',$user_id)->find();

        if($UserInfo){
            $data = [
                'CollectionsNum' => $CollectionsNum,        //关注数
                'FansNum'        => $FansNum,               //粉丝数
                'MFansNum'       => $MFansNum,              //企业粉丝数
                'NewsNum'        => $NewsNum,               //文章数
                'NewsDianjiliangCount' => isset($NewsDianjiliangCount) ? $NewsDianjiliangCount : 0, //总点击量
                'NewsShareCount' => $NewsShareCount,        //被分享次数
                'user_id'        => $UserInfo['id'],
                'status'         => $UserInfo['status'],        //0普通会员 1管理员 2版主  作废
                'user_name'      => $UserInfo['user_name'],     //昵称
                'personality'    => $UserInfo['personality'],   //个性签名
                'status'         => $UserInfo['status'],        //0正常  1被拉入黑名单
                'headimgurl'     => $UserInfo['headimgurl'],    //头像
                'moderator_status' => $UserInfo['moderator_status'],  //用户状态  3 app管理员
                'Moderator'      => $Moderator ? $Moderator : false,  //
                'ModeratorInfo'  => $ModeratorInfo ? $ModeratorInfo : false,
                'ModeratorUserNum' => $this->ModeratorUserNum($user_id),   //这个用户下的所有编辑者
            ]; 
            return show(1, '', $data);
        }else{
            return show(10, '没有这个用户');
        }
    	return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    }//

    //获取用户信息
    public function getInfo($user_id){
        if(empty($user_id)){
            return false;
        }
        $Users = Loader::model('Users');                //用户 实例化
        //获取用户信息
        $UserInfo = $Users->getUserInfo($user_id);
        return $UserInfo;
    }


    //获取企业粉丝数
    public function getMFansNum($moderator_id){
        $num = Loader::model('Guanzhuqiye')->where('moderator_id',$moderator_id)->count();
        return $num;
    }
    //获取用户文章数
    public function getUserNewsNum($user_id,$m_id){
        $News = Loader::model('News');
        $where['user_id'] = $user_id;
        $where['moderator_id'] = $m_id;
        return $News->where($where)->count();
    }
    //获取用户文章总访问次数
    public function getUserNewsCountDianjiliang($user_id,$m_id){
        $News = Loader::model('News');
        $where['user_id'] = $user_id;
        $where['moderator_id'] = $m_id;
        return $News->where($where)->sum('dianjiliang');
    }
    //获取用户所属企业下文章总被点击次数
    public function getNewsShareCount($user_id,$m_id){
        $News = Loader::model('News');
        $where['user_id'] = $user_id;
        $where['moderator_id'] = $m_id;
        return $News->where($where)->sum('share');
    }

    //获取用户关注的企业数
    public function getUserGuanzhuQiYe($user_id){
        $Guanzhuqiye = Loader::model('Guanzhuqiye');
        $num = $Guanzhuqiye->where('user_id',$user_id)->count();
        return $num;
    }

    //获取用户所属企业下的全部人数 （所有编辑者）
    public function ModeratorUserNum($user_id){
        $M_ = (new Moderator)->checkUser($user_id);
        $where['moderator_id'] = $M_[1];
        $where['user_id'] = ['neq',$user_id];
        return Loader::model('ModeratorInfo')
                ->alias('m')
                ->join('tp_users u','m.user_id = u.id')
                ->where($where)
                ->count();
    }

    //获取用户详细信息
    public function getUserInfo(){
        if(!$user_id = input('get.user_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Users = Loader::model('Users');                //用户 实例化
        $UsersArr = $Users->field('id,user_name,moderator_status,create_time,update_time,personality,province,city,country,headimgurl,sex,year,day,month')->where('id',$user_id)->find();
        $UsersArr->create_time = date('Y-m-d',$UsersArr->create_time);
        $UsersArr->update_time = date('Y-m-d',$UsersArr->update_time);
        return show(1, '', $UsersArr);
    }

    //修改用户信息
    public function upUserInfo(){
        if(!$user_id = input('get.user_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $get = input('get.');
        // file_put_contents('test.php',json_encode($get));
        unset($get['user_id']);
        $Users = loader::model('Users');
        if($Users->where('id',$user_id)->data($get)->update()){
            return show(1);
        }else{
            return show(0, '修改失败');
        }
    }

    //用户登陆
    public function userLogin(){
        // file_put_contents('text.php', json_encode(input('')));
        if($post = input('post.')){
            $data = [
                'w_open_id'      => $post['open_id'],
                'w_access_token' => $post['token'],
                'headimgurl'     => empty($post['headimgurl']) ? '': $post['headimgurl'],
                'w_headimgurl'   => empty($post['headimgurl']) ? '': $post['headimgurl'],
                'user_name'      => empty($post['nickname']) ? '火星的用户' : $post['nickname'],
                'sex'            => empty($post['gender']) ? '男' : $post['gender'],
                'w_name'         => empty($post['nickname']) ? '火星的用户' : $post['nickname'],
            ];
            // file_put_contents('test.php', json_encode($data));
            //判断用户是否存在数据库
            $Users = Loader::model('Users');
            $is_users = $Users->isUser($data['w_open_id']);
            if($is_users){ //存在就更新
                           //
                // $Users->where('w_open_id', $data['w_open_id'])->update($data);
                $user_id = $is_users['id'];
            }else{ //添加
                $user_id = $Users->insertGetId($data);
            }

            return show(1, '', $user_id);
        }else{
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
    }

    //获取用户粉丝数
    public function getFansNum(){
        return Loader::model('UsersCollect')->getFansNum();
    }

    //获取企业粉丝信息
    public function getQiyeFansInfo(){
        if($user_id = input('get.user_id')){
            $M_ = (new Moderator)->checkUser($user_id);
        }
        if($moderator_id = $M_[1]){
            $sql = ' SELECT u.id, user_name, personality, headimgurl, g.create_time, g.user_id FROM tp_users as u
            LEFT JOIN tp_guanzhuqiye as g on u.id = g.user_id
            WHERE g.moderator_id = '.$moderator_id;
            // LIMIT '.$this->from.' , '.$this->size;
            // die($sql);
            $FansInfo = Db::query($sql);
            // p($sql);
            //查询有没有关注这个粉丝
            $where['moderator_id'] = $moderator_id;
            foreach ($FansInfo as $k => $v) {
                $where['moderator_id'] = $v['id'];

                $is_collect = Db::name('guanzhuqiye')->where($where)->find();
                $FansInfo[$k]['is_collect'] = empty($is_collect) ? 0 : 1;
                $FansInfo[$k]['create_time'] = date($this->timestr, $v['create_time']);
            }
            if($FansInfo){
                return show(1, '', $FansInfo);
            }
             return show(2, '没有数据了');
        }else{
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
    }

        //获取用户粉丝信息
    public function getFansInfo(){
        if($user_id = input('get.user_id')){
            $sql = ' SELECT u.id, user_name, personality, status, headimgurl, c.* FROM tp_users as u
            LEFT JOIN tp_users_collect as c on u.id = c.user_id
            WHERE c.collect_id = '.$user_id.'
            LIMIT '.$this->from.' , '.$this->size;
            // die($sql);
            $FansInfo = Db::query($sql);
            //查询有没有关注这个粉丝
            $where['user_id'] = $user_id;
            foreach ($FansInfo as $k => $v) {
                $where['collect_id'] = $v['id'];
                $is_collect = Db::name('users_collect')->where($where)->find();
                $FansInfo[$k]['is_collect'] = empty($is_collect) ? 0 : 1;
                $FansInfo[$k]['create_time'] = date($this->timestr, $v['create_time']);
            }
            if($FansInfo){
                return show(1, '', $FansInfo);
            }
             return show(2, '没有数据了');
        }else{
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
    }

   

    //获取用户关注人数
    public function getCollectionsNum(){
    	return Loader::model('UsersCollect')->getCollectionsNum();
    }

    //获取用户关注人列表
    // public function getCollectionsList(){
    //     if(!$user_id = input('user_id')){
    //         return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    //     }
    //     $user_arr = Loader::model('Users')
    //             ->field(Config::get('apiconfig.user_field'))
    //             ->where('id','in',Loader::model('UsersCollect')->getCollectIdList($user_id))
    //             ->select();
    //     if($user_arr){
    //         return show(1,'',$user_arr);
    //     }
    //     return show(10);
    // }

    //获取用户关注的企业列表
    public function getCollectionsList(){
        if(!$user_id = input('user_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        // $user_arr = Loader::model('Users')
        //         ->field(Config::get('apiconfig.user_field'))
        //         ->where('id','in',Loader::model('UsersCollect')->getCollectIdList($user_id))
        //         ->select();
        $Moderator = Loader::model('Moderator')
                    ->where('id','in',Loader::model('Guanzhuqiye')->where('user_id',$user_id)->column('moderator_id'))
                    ->select();
        if($Moderator){
            return show(1,'',$Moderator);
        }
        return show(10);
    }

    //关注用户 取消关注
    public function onCollect(){
        $collect_id = input('collect_id');
        $user_id = input('user_id');
        if(!$user_id || !$collect_id){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $where = [
            'user_id' => $user_id,
            'collect_id' => $collect_id,
        ];
        $UsersCollect = Loader::model('UsersCollect');
        if($UsersCollect ->where($where)->find()){
            $UsersCollect ->where($where) ->delete();
            return show(-1, '成功');
        }else{
            if($UsersCollect->data($where)->save()){
                return show(1, '关注成功');
            }
        }
        return show(2, '出错了，刷新试试');
    }

    //关注企业 取消关注
    public function onGuanzhuqiye(){
        $moderator_id = input('moderator_id');
        $user_id = input('user_id');
        if(!$user_id || !$moderator_id){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $where = [
            'user_id' => $user_id,
            'moderator_id' => $moderator_id,
        ];
        $Guanzhuqiye = Loader::model('Guanzhuqiye');
        if($Guanzhuqiye ->where($where)->find()){
            $Guanzhuqiye ->where($where) ->delete();
            //如果在当前企业下  删除在当前企业下的会员身份
            Loader::model('ModeratorInfo')->where($where)->delete();
            return show(-1, '成功');
        }else{
            if($Guanzhuqiye->data($where)->save()){
                return show(1, '关注成功');
            }
        }
        return show(2, '出错了，刷新试试');
    }

    //判断用户是否合法
    public function is_User(){
        $user_id = input('get.user_id');
        if(!$user_id){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Users = Loader::model('Users');
        $usersarr = $Users->where('id', $user_id)->find();
        if($usersarr){
            return show(1, '', $usersarr);
        }
        return show(-1, '没有这个用户');
    }

    //修改用户头像
    public function upHeaderImg(){
        if($_FILES){

        }
        return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    }

    //获取没有加入圈子的用户 （必须是自己的粉丝）
    public function notModeratorUserList(){
        if(!$user_id = input('user_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
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
        // p($str_id,1);
        $M_ = (new Moderator)->checkUser($user_id);
        $where['moderator_id'] = $M_[1];
        $where['user_id'] = array('not in', $str_id);
        //查询粉丝列表
        $Guanzhuqiye = Loader::model('Guanzhuqiye');
        $UsersCollectUser = $Guanzhuqiye->where($where)->column('user_id');

        

        $Users =  Loader::model('Users');
        $UserList = $Users
        ->field('id,user_name,create_time,update_time,personality,province,city,country,headimgurl,sex,year,day,month')
        ->where('id','in',$UsersCollectUser)
        // ->fetchSql(true)
        ->select();
        if($UserList){
            $newArr = [];
            foreach ($UserList as $k=>$v) {
                //返回名字首字母大写  如果是数字直接返回
                $pinyin = is_numeric($v['user_name']) ? substr($v['user_name'],0,1) : Pinyin::getFirstCharter($v['user_name']);
                $newArr[$pinyin][] = $v; 
                // echo $pinyin;die;    
                // $UserList[$k]['char'] = $pinyin; 
            }
            ksort($newArr);
            return show(1, '', $newArr);
        }
        return show(0);

    }

    // public function getUserModeratorStatus(){
    //     if(!$user_id = input('user_id')){
    //         return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    //     }
    //     $Users = Loader::model('Users');
    //     $res = $Users->where('id',$user_id)->value('Moderator');
    // }

    //修改广告时间 图片config
    public function upggtime(){
        $data = [];
        if($time = input('time')){
            $data['time'] = $time;
        }
        if($appguanggaoimg = input('appguanggaoimg')){
            $data['appguanggaoimg'] = $appguanggaoimg;
        }
        $GuanggaoConfig = Loader::model('GuanggaoConfig');
        if($GuanggaoConfig->where('id',1)->data($data)->update()){
            return show(1);
        }
        return show(0);
    }

    //获取广告配置参数
    public function getggconfig(){
        $res = (new Moderator())->getappguanggao();

        // $GuanggaoConfig = Loader::model('GuanggaoConfig');
        // $res = $GuanggaoConfig->where('id',1)->find();
        if($res){
            return show(1, '', $res);
        }
        return show(0);
    }

    //没有看过的粉丝信息
    public function getNoLook(){
        if(!$user_id = input('user_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $M_ = (new Moderator)->checkUser($user_id);
        if(!$M_ || ($M_[0] == 1)){
            return show(0);
        }
        $Guanzhuqiye = Loader::model('Guanzhuqiye');
        $where = [
            'moderator_id' => $M_[1],
            'is_look' => 0,
            'user_id' => ['neq',$user_id]
        ];
        $res = $Guanzhuqiye->where($where)->column('user_id');

        if($res){
            //如果是点击
            if(input('is_look')){
                $userArr = Loader::model('Users')->where('id','in',$res)->select();
                if($userArr){
                   //将所有的数据都变成已看
                    $Guanzhuqiye->where($where)->update(['is_look'=>1]); 
                    return show(1,'',$userArr);
                }
            }else{
                return show(1,'',$res);
            }
        }
        return show(0);
    }

    public function getMfansInfo(){
        if(!$user_id = input('user_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $M_ = (new Moderator)->checkUser($user_id);
        if(!$M_ || ($M_[0] == 1)){
            return show(0);
        }
        $Guanzhuqiye = Loader::model('Guanzhuqiye');
        $where = [
            'moderator_id' => $M_[1],
            // 'user_id' => ['neq',$user_id],
        ];
        $res = $Guanzhuqiye->where($where)->fetchSql(false)->column('user_id');

        if($res){
            $userArr = Loader::model('Users')->where('id','in',$res)->select();
            if($userArr){
               //将所有的数据都变成已看
                $Guanzhuqiye->where($where)->update(['is_look'=>1]); 
                return show(1,'',$userArr);
            }
        }
        return show(0);
    }

}//
