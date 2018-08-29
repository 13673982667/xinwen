<?php
namespace app\api\controller;
use think\Loader;
use think\Db;
use app\common\lib\exception\ApiException;
use think\Config;

/**
 * 文章
 */
class News extends Common
{   
    
    public $com_where = ['status'=>0]; //公共where条件 状态0 没有被删除
    public $is_or = false;
    public $order = ' create_time desc ';
    public $is_return = false; //是否直接returns数据 


    function __construct($is_return = ''){
        if(!empty($is_return)){
            $this->is_return = true;
        }
    }

    //添加文章
    public function news_add(){
        // file_put_contents('text1.php', input(''));
        if(!($user_id = input('user_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        //检查权限  有没有发文章的权限
        // if(!$this->check_isAddNews($user_id)){
        //     return show(-1, '您没有权限！');
        // }

        //拿自己所属的企业（圈子） id
        $moderator = $this->getParentInfo($user_id);
        if(!$moderator){
            return show(-1, '您没有权限！');
        }
        //有没有手机号
        // $Users = Loader::model('Users');
        // $phone = $Users->where('id',$user_id)->value('phone');
        // if(!$phone){
        //     return show(-2, '亲！绑定一下手机号吧');
        // }



        //权限
        $checkUser = (new Moderator)->checkUser($user_id);
        if(!$checkUser){
            return show(-1, '您没有权限！');
        }
        if($checkUser[0] == 1){ //在圈子里有没有被禁用
            $ModeratorInfo = Loader::model('ModeratorInfo');
            $where['user_id'] = $user_id;
            $where['moderator_id'] = $checkUser[1];
            $status = $ModeratorInfo->where($where)->value('status');
            if(!$status || ($status != 1)){
                return show(-1, '您没有权限！');
            }
        }

         // p(input('post.'),1);
        if($post = input('post.')){

            // if(!empty($post['news_cover'])){
            //     //将剪裁的base64保存
            //     $news_cover = uploadbase64($post['news_cover']);
            //     if($news_cover){
            //         //剪裁微信分享的图片
            //         $w_cover = imgcl($news_cover);
            //     }
            // }

            //格式化数据
            $data = [
                // 'cover'    => empty($news_cover) ? '' : $news_cover,
                'cover'    => empty($post['news_cover']) ? '' : $post['news_cover'],
                'content'  => empty($post['news_content']) ? '' : $post['news_content'],
                'news_url' => empty($post['news_url']) ? '' : $this->checkSelfUrl($post['news_url']),
                'moderator_id' => $moderator['id'],
                'title'    => empty($post['news_title']) ? '' : $post['news_title'],
                'user_id'  => empty($post['user_id']) ? '' : $post['user_id'],
                'is_look'  => empty($post['is_look']) ? '' : $post['is_look'],
                // 'w_cover'  => empty($w_cover) ? '' : $w_cover,
                'w_cover'  => empty($post['news_cover']) ? '' : $post['news_cover'],
            ];
            $News = Loader::model('News');
            if($News->data($data)->save()){
                return show(1, '', $News->id);
            }else{
                return show(0, '发布失败');
            }
        }
       return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    }
 
    //检测这个链接是不是自己域名的链接 是的话返回已有的文章链接 不是的话返回
    public function checkSelfUrl($url = ''){
        $server_name = $_SERVER['SERVER_NAME'];
        if(preg_match("/".$server_name."/i",$url)){ 
            preg_match('/news_id=(\d*)/i', $url, $news_idArr);
            if(count($news_idArr) < 1){
                return $url;
            }
            $news_id = $news_idArr[1];
            $News = Loader::model('News');
            $where = [
                'id' => $news_id
            ];
            $newsArr = $News->where($where)->find();
            if($newsArr){
                return $newsArr['news_url'];
            }
        }
        return $url;
    }

    //文章列表
    public function getNewsList(){
        
        // if(input('isdelete')){//没有传入的话默认显示未删除的  传入-1就没有这个条件 默认全部显示
        //     if(input('isdelete') != '-1'){
        //         $this->map['is_delete'] = input('isdelete');
        //     }
        // }else{
            
        //     $this->map['is_delete'] = 0;
        // }
        if(!input('is_look')){  //没有这个属性的话  默认只显示所有人可见的
            $this->map['is_look'] = 1;
        }
        $News = Loader::model('News');
        
        $res = $News->alias('n')
            ->field('n.*,u.user_name,u.headimgurl')
            ->join('tp_users u', 'n.user_id = u.id')
            ->order($this->order)
            ->where($this->map)
            ->limit($this->from, $this->size)
            ->where($this->is_or)
            // ->fetchSql(true)
            ->select();
            // die($res);
        if($this->is_return){
            return $res;
        }
        if($res){
            $res = $this->eachData($res);
            
            return show(1, '', $res);
        }else{
            return show(10, '已经没有了');
        }
        return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    } 
    //遍历数据  找到父级
    public function eachData($res){
        if(!is_array($res)){
            return false;
        }
        $Users_collect =  Loader::model('Users_collect');
        foreach ($res as $k => $v) {
            //格式时间
            if($v['create_time'] < time() && $v['create_time'] > 0){
                //$res[$k]['create_time1'] = time_tran(date('Y-m-d H:i:s',$v['create_time']));
            }
            //判断是否关注
            $res[$k]['is_guanzhu'] = $Users_collect->is_collect(input('user_id'),$v['user_id']);
            //找这个用户的父级
            $res[$k]['parent'] = $this->getNewsParentInfo($v['moderator_id']);
            $res[$k]['title'] = htmlspecialchars_decode($res[$k]['title']);
            $res[$k]['content'] = $res[$k]['content'];
            // $res[$k]['create_time'] = date('Y.m.d',$v['create_time']);
            $res[$k]['cover'] = empty($res[$k]['cover']) ? '' : $res[$k]['cover'];
            $res[$k]['w_cover'] = empty($res[$k]['w_cover']) ? '' : $res[$k]['w_cover'];
        }
        return $res;
    }

    //获取文章详细信息
    public function getNewsInfo(){
        if(!($news_id = input('news_id'))){
            //return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $this->map['n.id'] = $news_id;
        $News = Loader::model('News');
        //修改点击量
        $News->where('id',$news_id)->inc('dianjiliang')->update();
        //是不是分享  是分享的话修改分享次数
        if(input('get_type') && input('get_type') == 'share'){
            $user_id = input('user_id');
            $this->fenxiang($news_id,$user_id);
        }
        return $this->getNewsList();
    }
    //分享
    public function fenxiang($news_id,$user_id){
        $News = Loader::model('News');
        //记录分享信息
        $data = [
        'news_id' => $news_id,
        'user_id' => $user_id
        ];
        if(!(Loader::model('FenxiangNews')->where($data)->find())){
            $News->where('id',$news_id)->inc('share')->update();
            Loader::model('FenxiangNews')->fetchSql(false)->insert($data);
        }
    }
    //获取一个文章的分享人信息
    public function getNewsfenxiang(){
        if(!$news_id = input('news_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $FenxiangNews = Loader::model('FenxiangNews');
        $userArr = $FenxiangNews->where('news_id',$news_id)->column('user_id');
        if($userArr){
            $Users = Loader::model('Users')
                            ->field('user_name,headimgurl')
                            ->where('id','IN',$userArr)
                            ->select();
            if($Users){
                return show(1,'',$Users);
            }
        }
        return show(0);
    }

    //用户文章列表
    public function getUserNewsList(){
        if(!$user_id = input('user_id')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        // if(input('isdelete')){//没有传入的话默认显示未删除的  传入-1就没有这个条件 默认全部显示
        //     if(input('isdelete') != '-1'){
        //         $this->map['is_delete'] = input('isdelete');
        //     }
        // }else{
            
        //     $this->map['is_delete'] = 0;
        // }
        // if(input('isdelete')){//没有传入的话默认显示自己文章  传入显示企业下的所有文章
        //     //获取企业id
        //      $M_ = (new Moderator)->checkUser($user_id);
        //      $this->map['moderator_id'] = is_bool($M_) ? -1 : $M_[1];
        // }else{
        //     $this->map['user_id'] = $user_id;
        // }
        $M_ = (new Moderator)->checkUser($user_id);
        if($M_[0] == 2){//没有传入的话默认显示自己文章  传入显示企业下的所有文章
            //获取企业id
             $M_ = (new Moderator)->checkUser($user_id);
             $this->map['moderator_id'] = is_bool($M_) ? -1 : $M_[1];
        }else{
            $this->map['user_id'] = $user_id;
        }

        if(!input('is_look')){  //没有这个属性的话  默认只显示所有人可见的
            $this->map['is_look'] = 1;
        }
        
        return $this->getNewsList();
    }

    //获取推荐文章
    // public function getNewsTuijianList(){
    //     $this->map['is_tuijian'] = 1;
    //     return $this->getNewsList();
    // }

    //获取推荐用户（修改为获取推荐的企业）
    // public function getTuijianUserList(){
    //     $where1 = '';
    //     if($user_id = input('user_id')){
    //         $where['id'] = ['neq',$user_id];
    //         //查询用户已关注的
    //         $Collect = Loader::model('UsersCollect');
    //         $cu = $Collect->where('user_id',$user_id)->column('collect_id');
    //     }
    //     $where['is_tuijian'] = 1;
    //     $Users = Loader::model('Users');
    //     $UsersArr = $Users
    //     ->field(Config::get('apiconfig.user_field'))
    //     ->order('create_time desc')
    //     ->limit($this->from, $this->size)
    //     ->where($where)
    //     ->select();
    //     if($UsersArr){
    //         if($cu){ //如果有关注的
    //             foreach($UsersArr as $k=>$v){
    //                 if(in_array($v['id'],$cu)){ //如果这个用户是已关注的 
    //                     $UsersArr[$k]['is_guanzhu'] = true;
    //                 }
    //             }
    //         }
    //         return show(1, '', $UsersArr);
    //     }
    //     return show(0);
    // }

    //（修改为获取推荐的企业）
    public function getTuijianUserList(){
        $user_id = input('user_id');
        $Moderator = Loader::model('Moderator');
        $where['is_tuijian'] = 1;
        $where['user_id'] = ['neq',$user_id];
        $res = $Moderator
                ->where($where)
                ->order('create_time desc')
                ->limit($this->from, $this->size)
                // ->fetchSql(true)
                ->select();
        if($res){
            //查询用户是否关注
            if($user_id = input('user_id')){
                $guanzhuqiye = Loader::model('Guanzhuqiye');
                $cres = $guanzhuqiye->where('user_id',$user_id)->column('moderator_id');
                if($cres){
                    foreach($res as $k => $v){
                        if(in_array($v['id'], $cres)){
                            $res[$k]['is_guanzhu'] = true;
                        }
                    }
                }
            }
            return show(1, '', $res);
        }
        return show(0);
    }

    // //获取关注文章 (修改为获取关注的企业下的所有用户的文章)
    // public function getNewsCollectList(){
    //     $user_id = input('user_id');
    //     if(!$user_id){
    //         return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    //     }
    //     $Users_collect =  Loader::model('Users_collect');
    //     //获取我关注的人
    //     $collect = implode(',',$Users_collect->getCollectIdList($user_id));
    //     $this->map['user_id'] = ['in',$collect];
    //     return $this->getNewsList();
    // }

    //(修改为获取关注的企业下的所有用户的文章)
    public function getNewsCollectList(){
        if(!($user_id = input('user_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        //获取用户关注的企业(圈子)id、
        $Guanzhuqiye = Loader::model('Guanzhuqiye');
        $qiye_id = $Guanzhuqiye->where('user_id',$user_id)->column('moderator_id');
        if($qiye_id){
            // $this->map['moderator_id'] = ['IN',$qiye_id];
            $this->is_or = ' moderator_id IN ('.implode(',',$qiye_id).') OR user_id = '.$user_id;
        }else{
            $this->is_or = ' moderator_id = -1 OR user_id = '.$user_id;
        }
        // $this->is_or['user_id'] = $user_id;
        // $this->map['moderator_id|user_id'] = 1 ;
        $Users_collect =  Loader::model('Users_collect');
        //获取我关注的人
        // $collect = implode(',',$Users_collect->getCollectIdList($user_id));
        // $this->map['user_id'] = ['in',$collect];
        return $this->getNewsList();
    }

    //获取企业下的文章
    public function getQiyeNews(){
        if(!($moderator_id = input('moderator_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $this->map['moderator_id'] = $moderator_id;
        return $this->getNewsList();
    }

    //获取置顶企业下的文章
    public function getzhiding(){
        $Moderator = Loader::model('Moderator');
        $M_ = $Moderator->where('zhiding', 1)->column('id'); //置顶的企业
        if($M_){
            $arr = [];
            //循环这个企业id数组  查找这个企业id对应的管理员id 和他指定的置顶人id
            foreach($M_ as $kk=>$vv){
                //查找这个企业下的管理员id、
                $m_id = $Moderator->where('id',$vv)->value('user_id');
                //查找企业指定的置顶人id （数组）
                $ModeratorInfo = Loader::model('ModeratorInfo');
                $uwhere['moderator_id'] = ['IN',$M_];
                $uwhere['is_zhiding'] = 1;
                $userIdArr = $ModeratorInfo->where($uwhere)->column('user_id');    
                $userIdArr[] = $m_id;
                if($userIdArr){
                    $News = Loader::model('News');
                    // foreach($userIdArr as $k=>$v){
                    $NewsArr = $News->alias('n')
                        ->field('n.*,u.user_name,u.headimgurl')
                        ->join('tp_users u', 'n.user_id = u.id')
                        ->where('user_id','in',$userIdArr)
                        ->order('create_time desc')
                        // ->fetchSql(true)
                        ->find();
                    if($NewsArr){
                        $arr[] = $NewsArr;
                    }
                    // }
                }
            }
            if(!empty($arr)){
                $arr = $this->eachData($arr);
            }
            return show(1,'',$arr);
            // $ModeratorInfo = Loader::model('ModeratorInfo');
            // $userIdArr = $ModeratorInfo->where('moderator_id','IN',$M_)->column('user_id');
            // if($userIdArr){
            //     $News = Loader::model('News');
            //     $arr = [];
            //     foreach($userIdArr as $k=>$v){
            //         $NewsArr = $News->alias('n')
            //             ->field('n.*,u.user_name,u.headimgurl')
            //             ->join('tp_users u', 'n.user_id = u.id')
            //             ->where('user_id',$v)
            //             ->order('create_time desc')
            //             ->find();
            //         if($NewsArr){
            //             $arr[] = $NewsArr;
            //         }
            //     }
            //     if($arr){
            //         $arr = $this->eachData($arr);
            //     }
            //     return show(1,'',$arr);
            // }
        }
        return show(0,'错误');
        // if($M_){
        //     $News = Loader::model('News');
        //     $arr = [];
        //     // p($M_);
        //     foreach($M_ as $k=>$v){
        //         $NewsArr = $News->alias('n')
        //                 ->field('n.*,u.user_name,u.headimgurl')
        //                 ->join('tp_users u', 'n.user_id = u.id')
        //                 ->where('user_id',$v)
        //                 ->order('create_time desc')
        //                 ->find();
        //         if($NewsArr){
        //             $arr[] = $NewsArr;
        //         }
        //     }
        //     if($arr){
        //         $arr = $this->eachData($arr);
        //     }
        //     return show(1,'',$arr);
        // }   
    }

    //删除用户的一个文章 到回收站
    public function deleteNews(){
        $user_id = input('user_id');
        $news_id = input('news_id');
        if(!$user_id || !$news_id){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $News = Loader::model('News');
        $where['id'] = $news_id;
        $where['user_id'] = $user_id;
        if($News->where($where)->update(['is_delete'=>1])){
            return show(1,'');
        }
        return show(0,'错误');
    }
    //彻底删除用户的一个文章
    public function deleteNews1(){
        $user_id = input('user_id');
        $news_id = input('news_id');
        if(!$user_id || !$news_id){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $News = Loader::model('News');
        //是不是企业管理员
        $M_ = (new Moderator)->checkUser($user_id);
        if(!$M_ ){
            $where['user_id'] = $user_id;
        }else{
            $where['moderator_id'] = $M_[1];
        }
        $where['id'] = $news_id;
        if($News->where($where)->delete()){
            return show(1,'');
        }
        return show(0,'错误');
    }

    //检查用户有没有发文章权限
    public function check_isAddNews($user_id){
        //查询圈主的会员列表 
        $ModeratorInfo = Loader::model('ModeratorInfo');
        $ModeratorInfoUser = $ModeratorInfo->where('user_id',$user_id)->find();
        if($ModeratorInfoUser){
            return true;
        }
        //查询圈主列表
        $Moderator = Loader::model('Moderator');
        $ModeratorUser = $Moderator->where('user_id',$user_id)->find();
        if($ModeratorUser){
            return true;
        }
        return false;
    }
    //获取用户的父级资料   (更换为查找自己所在的企业（圈子）)
    // private function getParentInfo($user_id){
        // $Users = Loader::model('Users');
        // //查询圈主列表 
        // $Moderator = Loader::model('Moderator');
        // $ModeratorUser = $Moderator->where('user_id',$user_id)->find();
        // if($ModeratorUser){
        //     $user_arr = $Users->field('user_name,id')->where('id',$ModeratorUser['user_id'])->find();
        //     return false; //如果自己是父级就不返回了
        //     return $user_arr;
        // }
        // //查询圈主的会员列表
        // $ModeratorInfo = Loader::model('ModeratorInfo');
        // $ModeratorInfoUser = $ModeratorInfo->where('user_id',$user_id)->find();
        // if($ModeratorInfoUser){
        //     $user_arr = $Users->alias('u')->field('u.user_name,u.id')
        //     ->join('tp_moderator m',' m.user_id = u.id ')
        //     ->where('m.id',$ModeratorInfoUser['moderator_id'])->find();
        //     return $user_arr;
        // }
    //     return false;
    // }

    // (更换为查找自己所在的企业（圈子）)
    private function getParentInfo($user_id){
        
        $Moderator = Loader::model('Moderator');//圈主列表 
        $ModeratorInfo = Loader::model('ModeratorInfo');//圈主的会员列表
        //查询圈主的会员列表
        $ModeratorInfoUser = $ModeratorInfo->where('user_id',$user_id)->find();

        if($ModeratorInfoUser){
            $res = $Moderator->where('id',$ModeratorInfoUser['moderator_id'])->find();
            if($res){
                return $res;
            }
        }
        //查询圈主列表 
        $res = $Moderator->where('user_id',$user_id)->find();
        if($res){
            return $res;
        }
        return false;
    }

    //查找文章的所属企业
    public function getNewsParentInfo($moderator_id){
        $Moderator = Loader::model('Moderator');
        return $Moderator->where('id',$moderator_id)->find();
    }

    //获取轮播图
    public function getLunbotu(){
        $Guanggao = Loader::model('Guanggao');
        $where = $this->com_where;
        $where['is_lunbo'] = 1;
        $res = $Guanggao
        ->order('order_')
        ->where($where)
        ->select();
        if($res){
            return show(1,'',$res);
        }
        return show(10);
    }

    //检查地址 是否合法
    public function checkurl(){
        return show(1); //这个有问题
        if(!$url = input('url')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        if(pingAddress($url)){
            return show(1);
        }else{
            return show(0);
        }
    }

    //根据链接抓取链接标题
    public function getUrlTitle(){
        if(!$url = input('url')){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        if($title = getUrlTitle($url)){
            return show(1,'',$title);
        }
        return show(0);
    }

    // //获取文章内容
    // public function getNewsInfo($news_id){
    //     if(!$news_id){
    //         return false;
    //     }
    //     //修改点击量
    //     Loader::model('News')->where('id',$news_id)->inc('dianjiliang')->update();
    //     return Loader::model('News')->where('id',$news_id)->value('news_url');
    // } 

}//
