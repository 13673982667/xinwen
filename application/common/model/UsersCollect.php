<?php
namespace app\common\model;
use think\Model;
use think\Loader;

/**
 * 用户关注
 */
class UsersCollect extends Model{


    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    
	/**
	 * 获取用户关注人数
	 * @param  number $u_id 用户id
	 */
	public function getCollectionsNum($user_id){
		if($user_id){
			return $this->where('user_id', $user_id)->count();			
		}
	}

	/**
	 * 获取我关注的人id
	 */
	public function getCollectIdList($user_id = ''){
		if(empty($user_id)){
			return false;
		}

		return $this->where('user_id', $user_id)->column('collect_id');

	}

	/**
	 * 获取用户粉丝数
	 */
	public function getFansNum($user_id){
		if($user_id){
			return $this->where('collect_id', $user_id)->count();			
		}
	}
	
	/**
	 * 有没有关注这个用户
	 * @param 
	 * @return bool
	 */
	public function is_collect($user_id = '', $collect_id = ''){
		if(empty($user_id)||empty($collect_id)){
			return false;
		}
		$collect = $this->select();
		foreach ($collect as $k => $v) {
			if($v['user_id'] == $user_id && $v['collect_id'] == $collect_id){
				return true;
			}
		}

		return false;
	}
	
}