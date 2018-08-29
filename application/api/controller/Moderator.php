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
class Moderator extends Common
{   
    

    //添加会员  （关注自己的   没有被别人添加的才能添加）
    public function tianjiahuiyuan(){
    	if(!($user_id = input('user_id')) || !($user_str = input('res'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        // die($user_str);
        //查询邀请的这个用户的圈子id
        if(!($quanzi_id = $this->is_quanzhu($user_id))){
        	return show(2,'您没有这个权限');
        }

        // //查询圈主的会员列表 
        $ModeratorInfo = Loader::model('ModeratorInfo');
        $ModeratorInfoUser = $ModeratorInfo->column('user_id');
        // //查询圈主列表
        $Moderator = Loader::model('Moderator');
        $ModeratorUser = $Moderator->column('user_id');
        // //查询粉丝列表
        // $UsersCollect = Loader::model('UsersCollect');
        // $UsersCollectUser = $UsersCollect->where('collect_id',$user_id)->column('user_id');
        $user_list = array_merge($ModeratorUser,$ModeratorInfoUser);
        //

        $user_ = explode(',', trim($user_str,','));
        $zai_user = [];
        $bushifensi = [];
        foreach ($user_ as $k=>$v) {
			if(!in_array($v, $user_list)){  //这个用户要没有加入圈子
			// 	$zai_user[] = $v;
			// }else{  //两个都没有的话  插入数据库
			// 	if(in_array($v, $UsersCollectUser)){//查询是不是这个用户的粉丝  是的话才添加
					$data = [
						'moderator_id'     => $quanzi_id,  //圈子id
						'user_id'          => $v,          //被邀请用户id
						'moderator_status' => 0,           //身份  普通会员
					];
					$ModeratorInfo->fetchSql(false)->insert($data);
			// 	}else{
			// 		$bushifensi[] = $v;
			// 	}
				
				
			}
        }
        return show(1, '', $bushifensi);
    }

    //查询这个用户有没有权限邀请人
    private function is_quanzhu($user_id){
		$Moderator = Loader::model('Moderator');

        if($quanzi = $Moderator->where('user_id',$user_id)->find()){
        	return $quanzi['id'];
        }

    	return false;
    }

    //获取当前组下会员列表
    public function getmoderatorUserList(){
        if(!($user_id = input('user_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        //查这个用户的身份
        $checkUser = $this->checkUser($user_id);  //0普通会员 1已经加入圈子的 2圈主
        if(!$checkUser){
            return show(0,'您还没有被被邀请成为会员！');
        }

        //是不是置顶的企业  是置顶的企业有指定置顶的会员
        $Moderator = Loader::model('Moderator');
        $M_zhiding = $Moderator->where('id',$checkUser[1])->value('zhiding');
        $ModeratorInfo = Loader::model('ModeratorInfo');
        $where['moderator_id'] = $checkUser[1];
        $where['user_id'] = ['not in', $user_id];
        $ModeraList = $ModeratorInfo->where($where)->column('user_id'); 
        if($ModeraList){
            $userarr = Loader::model('Users')
                        ->alias('u')
                        ->join('tp_moderator_info i',' i.user_id = u.id ')
                        ->field('u.id,u.user_name,u.create_time,u.update_time,u.personality,province,city,country,headimgurl,sex,year,day,month,is_zhiding')
                        ->where('u.id','in',$ModeraList)
                        ->select();
            return show(1, '', [$userarr,$checkUser[0],'zhiding'=>$M_zhiding,'moderator_id'=>$checkUser[1]]);
        }
        return show(1, '', [$ModeraList,$checkUser[0],'zhiding'=>$M_zhiding,'moderator_id'=>$checkUser[1]]);

    }

    /**
    *当前用户的身份
    * @return array ['当前身份','圈子id']
    */
    public function checkUser($user_id){
        //查询圈主的会员列表 
        $ModeratorInfo = Loader::model('ModeratorInfo');
        $Moderator = Loader::model('Moderator');
        if($arr = $Moderator->where('user_id',$user_id)->find()){
            return [2,$arr['id']];
        }else if($arr = $ModeratorInfo->where('user_id',$user_id)->find()){
            return [1,$arr->moderator_id];
        }else{
            return 0;
        }
    }

    //将会员从圈里移除
    public function deleteMuser(){
        if(!($user_id = input('user_id')) || !($del_id = input('del_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        //查这个用户的身份
        $checkUser = $this->checkUser($user_id);  //0普通会员 1已经加入圈子的 2圈主
        if(!$checkUser || $checkUser[0] != 2){
            return show(0, '您没有这个权限');
        }

        $ModeratorInfo = Loader::model('ModeratorInfo');
        $where['moderator_id'] = $checkUser[1];
        $where['user_id']      = $del_id;
        if($ModeratorInfo->where($where)->delete()){
            return show(1);
        }
        return show(0,'error!');

    }

    //添加广告图片
    public function addguanggaoimg(){
        // file_put_contents(, data)
        if(!($user_id = input('user_id')) || !($moderator_id = input('moderator_id')) || !($ggimg = input('ggimg'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Guanggao = Loader::model('Guanggao');
        //判断添加的广告数量有没有超过限制
        $Moderator = Loader::model('Moderator');
        $M_arr = $Moderator->where('id',$moderator_id)->find();
        if(!$M_arr){
            return show(0);
        }
        $rank = $M_arr['rank']; //会员等级
        $g_num = $Guanggao->where('moderator_id',$moderator_id)->count();  //以添加的广告数
        if($rank == 0){ //普通会员
            if($g_num >= 1){
                return show(2,'广告数量超过限制');
            }
        }else if($rank == 1){ //牛逼的会员  33

        }else{ //...

        }

        //保存base64成图片
        $ggimg = Config::get('apiconfig.this_host').uploadbase64($ggimg);

        $data = [
            'moderator_id' => $moderator_id,
            'ggimg'        => $ggimg,
            'status'       => 1,
        ];
        $m_id = $Guanggao->insertGetId($data);
        if($m_id){
            return show(1,'',$m_id);
        }
        return show(0,$m_id);
    }

    //获取广告列表
    public function getguanggaolist(){
        if(!($user_id = input('user_id')) || !($moderator_id = input('moderator_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Guanggao = Loader::model('Guanggao');
        $guanggaolist = $Guanggao->where('moderator_id', $moderator_id)->select();
        if($guanggaolist){
            return show(1,'',$guanggaolist);
        }else{
            return show(10,'没有了');
        }
        return show(0,'没有了');
    }

    //根据广告id查广告
    public function getGuanggaoInfo(){
        if(!($ggid = input('ggid'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Guanggao = Loader::model('Guanggao');
        $guanggaolist = $Guanggao->where('id', $ggid)->find();
        if($guanggaolist){
            return show(1,'',$guanggaolist);
        }else{
            return show(10,'没有了');
        }
    }

    //删除广告
    public function delguanggao(){
        if(!($user_id = input('user_id')) || !($ggid = input('ggid'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Guanggao = Loader::model('Guanggao');
        $res = $Guanggao->where('id',$ggid)->delete();
        if($res){
           return show(1,$res);
        }
        return show(0);
    }
    //修改广告图片
    public function upggimg(){
        if(!($user_id = input('user_id')) || !($ggid = input('ggid')) || !($ggimg = input('ggimg'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Guanggao = Loader::model('Guanggao');
        if($Guanggao->where('id',$ggid)->setField('ggimg',$ggimg)){
            return show(1);
        }
        return show(0,'error',$Guanggao->where('id',$ggid)->setField('ggimg',$ggimg));
    }
    //修改广告链接
    public function upggurl(){
        if(!($user_id = input('user_id')) || !($ggid = input('ggid'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $ggurl = input('ggurl');
        $Guanggao = Loader::model('Guanggao');
        if($Guanggao->where('id',$ggid)->setField('ggurl',$ggurl)){
            return show(1);
        }
        return show(0,'error');
    }
    //

    function test(){
        return 'asdd';
    }
    // //根据文章id返回广告信息
    // public function getNewsGuanggao(){
    //     if(!($news_id = input('news_id'))){
    //         return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    //     }
    //     //查询文章的用户id
    //     $user_id = Loader::model('News')->where('id',$news_id)->value('user_id');
    //     if($user_id){
    //         //根据user_id查广告信息
    //         $ggArr = $this->getUserGuanggao($user_id);
    //         if($ggArr){
    //             return show(1,'',$ggArr);
    //         }else{
    //             return show(1,'');
    //         }
    //     }
    //     return show(0);
    // } 
    //根据文章id返回广告信息 (改为一次只显示一条)
    public function getNewsGuanggao(){
        if(!($news_id = input('news_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        //查询发送文章时的企业id
        // $moderator_id = Loader::model('News')->where('id',$news_id)->value('moderator_id');
        // if($moderator_id){
        //     $Guanggao = Loader::model('Guanggao');
        //     //拿当前广告显示次数
        //     $Moderator = Loader::model('Moderator');
        //     $pagenum = $Moderator->where('id',$moderator_id)->value('pagenum');
        //     //修改次数 +1
        //     $Moderator->where('id',$moderator_id)->setInc('pagenum'); 
        //     //广告条数
        //     $num = $Guanggao->where('moderator_id', $moderator_id)->count();

        //     $limit = $pagenum ? ($pagenum % $num) : 1;
        //     // p($limit,1);
        //     $arr = $Guanggao->where('moderator_id', $moderator_id)->limit($limit, 1)->select();
        //     if($arr){
        //         return show(1,'',$arr);
        //     }
        // }
        if($arr = (new Guanggao())->getNewsGuanggao1($news_id)){
            return show(1,'',$arr);
        }
        return show(0);
    }
    //根据文章id返回广告信息 (改为一次只显示一条)
    public function getNewsGuanggao1($news_id){
        if(!$news_id){
            return false;
        }
        //查询发送文章时的企业id
        $moderator_id = Loader::model('News')->where('id',$news_id)->value('moderator_id');
        if($moderator_id){
            $Guanggao = Loader::model('Guanggao');
            //拿当前广告显示次数   广告时间
            $Moderator = Loader::model('Moderator');
            $M_ = $Moderator->where('id',$moderator_id)->find();
            $pagenum = $M_['pagenum'];  //广告次数
            //修改次数 +1
            $Moderator->where('id',$moderator_id)->setInc('pagenum'); 
            //广告条数
            $num = $Guanggao->where('moderator_id', $moderator_id)->count();
            if($num){
                $limit = $pagenum ? ($pagenum % $num) : 1;
            }else{
                return false;
            }
            
            // p($limit,1);
            $arr = $Guanggao->where('moderator_id', $moderator_id)->limit($limit, 1)->select();
            if($arr){
                return ['arr'=>$arr,'ggtime'=>$M_['ggtime']];
            }
        }
        return false;
    }

    //根据用户id返回 这个用户所属圈子的广告信息
    public function getUserGuanggao($user_id){
        if(!$user_id){
            return false;
        }
        $M_id = $this->checkUser($user_id);
        if($M_id){
            $Guanggao = Loader::model('Guanggao');
            return $Guanggao->where('moderator_id',$M_id[1])->select();
        }
        return false;
    }

    //获取企业信息
    public function getQiyeInfo(){
        if(!($moderator_id = input('moderator_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Moderator = Loader::model('Moderator');
        $News = Loader::model('News');
        $res = $Moderator->where('id',$moderator_id)->find();
        //获取企业下的全部文章
        $news_num = $News->where('moderator_id', $moderator_id)->count();
        //
        if($res){
            return show(1,'',$res);
        }
        return show(0);
    }   
    //修改企业信息
    public function upQiyeInfo(){
        if(!($moderator_id = input('moderator_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $get = input('get.');
        // file_put_contents('test.php',json_encode($get));
        unset($get['moderator_id']);
        $Moderator = loader::model('Moderator');
        if($Moderator->where('id',$moderator_id)->data($get)->update()){
            return show(1);
        }else{
            return show(0, '修改失败');
        }
    }

    // //广告跳转页面 
    // public function GuanggaoView(){
    //     if(!($news_id = input('news_id')) || !($user_id = input('user_id'))){

    //     }
    //     // $news_id = 422;
    //     //根据文章获取企业广告
    //     $guanggao = $this->getNewsGuanggao1($news_id);
    //     //获取app广告
    //     $appguanggao = $this->getappguanggao();
    //     // p(json_encode($appguanggao),1);
    //     if($appguanggao){
    //         $this->assign('appguanggao',$appguanggao);
    //     }
    //     $this->assign('user_id',$user_id);
    //     // $this->assign('news_url',$news_url);
    //     $this->assign('news_id',$news_id);
    //     if($guanggao){
    //         $this->assign('guanggao',$guanggao);
    //         return $this->fetch(); 
    //     }
    //     return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
    //     // view('asda');
           
    // }
    
    // //获取app广告
    // public function getappguanggao(){
    //     // $GuanggaoConfig = Loader::model('GuanggaoConfig');
    //     // $res = $GuanggaoConfig->where('id',1)->find();
    //     // if($res){
    //     //     return $res;
    //     // }
    //     // return false;
    //     $GuanggaoConfig = Loader::model('GuanggaoConfig');
    //     $appconfig = Loader::model('AppConfig');
    //     //拿当前广告显示次数 
    //     $pagenum = $appconfig->where('id',1)->value('pagenum');
    //     //广告条数
    //     $Gwhere = ['type'=>0,'status'=>0];
    //     $ggnum = $GuanggaoConfig->where($Gwhere)->count();
    //     $limit = ($pagenum && $ggnum) ? ($pagenum % $ggnum) : 0;
    //     // p($pagenum,1);
    //     //取那条广告
    //     $res = $GuanggaoConfig->where($Gwhere)->limit($limit, 1)->select();
    //     if($res){
    //         //广告次数+1
    //         $appconfig->where('id',1)->setInc('pagenum');
    //         return $res[0];
    //     }
    //     return false;
    // }

   

    //搜索企业
    public function getmoderatorsousuo(){
        if(!($val = input('val'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $where['ban_name'] = ['like','%'.$val.'%'];
         $Moderator = Loader::model('Moderator');
        $res = $Moderator
                ->where($where)
                ->order('create_time desc')
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
            return show(1,'',$res);
        }
        return show(0);
    }

    //设置或取消会员置顶
    public function setUserZhiding(){
        if(!($moderator_id = input('moderator_id')) || !($user_id = input('user_id'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $Moderator = Loader::model('Moderator');
        $M_zhiding = $Moderator->where('id',$moderator_id)->value('zhiding');
        if($M_zhiding != 1){
            return show(-1,'您没有权限');
        }
        $ModeratorInfo = Loader::model('ModeratorInfo');
        $where['moderator_id'] = $moderator_id;
        $where['user_id'] = $user_id;
        $U_zhiding = $ModeratorInfo->where($where)->value('is_zhiding');
        if($U_zhiding != 1){
            $ModeratorInfo->where($where)->update(['is_zhiding' => '1']);
        }else{
            $ModeratorInfo->where($where)->update(['is_zhiding' => '0']);
        }
        $U_zhiding = $ModeratorInfo->where($where)->value('is_zhiding');
        return show(1,'',$U_zhiding);
    }

    //修改企业权限  key=>val的形式
    public function upModeratorAuth(){
        if(!($mid = input('mid')) || !($key = input('key'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $val = input('val');

        $Moderator = Loader::model('Moderator');
        $where = [
            'id' => $mid
        ];
        $data = [
            $key => $val
        ];
        $res = $Moderator->where($where)->update($data);
        return show(1,'',$res);
    }

    //修改企业广告信息  key=>val的形式
    public function upgg(){
        if(!($ggid = input('ggid'))){
            return show(Config::get('apiconfig.errorcode'),Config::get('apiconfig.errormessage'),[],Config::get('apiconfig.errorhttpcode'));
        }
        $get = input('get.');
        // file_put_contents('test.php',json_encode($get));
        unset($get['ggid']);
        $Guanggao = loader::model('Guanggao');
        if($Guanggao->where('id',$ggid)->data($get)->update()){
            return show(1);
        }else{
            return show(0, '修改失败');
        }
    }

}//