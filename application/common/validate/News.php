<?php
namespace app\common\validate;

use think\Validate;

class News extends Validate
{
    protected $rule = [
        "title|文章标题" => "require",
        "content|文章内容" => "require",
    ];
}
