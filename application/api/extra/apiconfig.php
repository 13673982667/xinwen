<?php

return [
    // 
    'user_info_list' => ['user_id', 'user_name', 'status', 'personality', 'is_bliacklist', 'headimgurl'], //获取用户的字段  公共的
    'list_rows'      => 10, //默认分页大小
    'errormessage'   => '参数不合法', //错误提示信息
    'errorcode'      => 0,         		//错误的状态码
    'errorhttpcode'  => 403,              //错误的http状态码
    'img_path'       => './public/uploads', //默认文件上传地址  根目录\public\uploads
    'jiancai_path'	 => './public/uploads/jiancai',
    'this_host'      =>  'http://'.$_SERVER['HTTP_HOST'].'/',//当前请求域名地址
    // 'this_host'      =>  'http://'.$_SERVER['SERVER_ADDR'].'/',//当前请求域名地址
    'user_field'     =>  'id,user_name,create_time,update_time,personality,province,city,country,headimgurl,sex,year,day,month',    //获取用户的默认字段
];