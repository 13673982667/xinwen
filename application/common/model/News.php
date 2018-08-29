<?php
namespace app\common\model;

use think\Model;

class News extends Model
{
    // 指定表名,不含前缀
    protected $name = 'news';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';


	//获取用户文章数
    public function getUserNewsNum($user_id){
        return $this->where('user_id',$user_id)->count();
    }

    //获取用户文章总访问次数
    public function getUserNewsCountDianjiliang($user_id){
    	return $this->where('user_id',$user_id)->sum('dianjiliang');
    }

    //获取用户文章总被点击次数
    public function getNewsShareCount($user_id){
    	return $this->where('user_id',$user_id)->sum('share');
    }
}
