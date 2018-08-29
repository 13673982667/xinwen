<?php
namespace app\common\model;

use think\Model;
use think\Config;
class ModeratorApply extends Model
{
    // 指定表名,不含前缀
    protected $name = 'moderator_apply';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    /**
     * 关联用户
     */
    public function Users(){
        $resource = $this->hasOne('Users', 'id', 'user_id');
        return $resource;
    }

    public function getStartAttr($value)
    {
        $start = Config::get('webconfig')['start'];
        return $start[$value];
    }

}
