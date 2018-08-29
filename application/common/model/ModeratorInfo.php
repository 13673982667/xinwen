<?php
namespace app\common\model;

use think\Model;
use think\Config;
class ModeratorInfo extends Model
{
    // 指定表名,不含前缀
    protected $name = 'moderator_info';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    /**
     * 关联用户
     */
    public function Users(){
        $resource = $this->hasOne('Users', 'id', 'user_id');
        return $resource;
    }

    /**
     * 关联圈子
     */
    public function Moderator(){
        $resource = $this->hasOne('Moderator', 'id', 'moderator_id');
        return $resource;
    }

    //获取器
    public function getModeratorStatusAttr($value)
    {
        $start = Config::get('webconfig')['moderator_status'];
        return $start[$value];
    }
}
