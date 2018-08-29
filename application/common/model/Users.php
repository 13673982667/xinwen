<?php
namespace app\common\model;
use think\Model;
/**
 * 用户模型
 */
class Users extends Model{

    // 指定表名,不含前缀
    protected $name = 'users';
	// 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';




    //获取用户信息
    public function getUserInfo($user_id){
    	return $this
    	// ->field(['user_id', 'user_name', 'status', 'personality', 'is_bliacklist', 'headimgurl'])
    	->where('id', $user_id)
    	->find();
    }

	// 关联版主申请表
	public function ModeratorApply(){
        $resource = $this->belongsTo('ModeratorApply', 'user_id', 'id');
        return $resource;
    }

    //根据openid判断用户是否存在
    public function isUser($open_id){
    	$isUser = $this->where('w_open_id', $open_id)->find();
    	if($isUser){
    		return $isUser;
    	}
    	return false;
    }
	
}