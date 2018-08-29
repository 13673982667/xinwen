<?php
namespace app\common\model;

use think\Model;

class NewsCategory extends Model
{
    // 指定表名,不含前缀
    protected $name = 'news_category';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
}
