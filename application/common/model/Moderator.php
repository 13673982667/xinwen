<?php
namespace app\common\model;

use think\Model;

class Moderator extends Model
{
    // 指定表名,不含前缀
    protected $name = 'moderator';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    /**
     * 关联用户
     */
    public function Users(){
        $resource = $this->hasOne('Users', 'id', 'user_id');
        return $resource;
    }
}
