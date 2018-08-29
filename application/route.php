<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
//get
// Route::get('api/:ver/Users', 'api/:ver.Users/test'); //版本

//users
Route::get('userInfo', 'api/Users/info');			 //获取用户信息
Route::get('getUserInfo', 'api/Users/getUserInfo');	 //获取用户详细信息
Route::get('upUserInfo', 'api/Users/upUserInfo');	 //修改用户信息
Route::post('userLogin', 'api/Users/userLogin');	 //用户微信登陆
Route::get('getFansInfo', 'api/Users/getFansInfo');  //用户粉丝
Route::get('onCollect', 'api/Users/onCollect'); 	 //关注 取消关注 用户
Route::get('onGuanzhuqiye', 'api/Users/onGuanzhuqiye');//关注 企业 取消关注
Route::get('is_User', 'api/Users/is_User');     	 //验证用户合法性
Route::get('notModeratorUserList', 'api/Users/notModeratorUserList');     	 //获取没有加入圈子的用户
Route::get('getCollectionsList', 'api/Users/getCollectionsList');			 //获取用户信息
// Route::get('getUserModeratorStatus', 'api/Users/getUserModeratorStatus'); //获取用户权利
Route::get('upggtime', 'api/Users/upggtime');	     //修改广告参数
Route::get('getggconfig', 'api/Users/getggconfig');	 //获取广告参数 （获取广告页下的app广告）
Route::get('getNoLook', 'api/Users/getNoLook');	     //获取企业下 关注的 没有提示的消息
Route::get('getMfansInfo', 'api/Users/getMfansInfo');////企业粉丝


//news
Route::post('news_add', 'api/News/news_add');     				  //添加文章
Route::get('getNewsList', 'api/News/getNewsList'); 				  //文章列表
Route::get('getUserNewsList', 'api/News/getUserNewsList');        //用户文章列表
// Route::get('getNewsTuijianList', 'api/News/getNewsTuijianList');  //推荐文章列表
Route::get('getTuijianUserList', 'api/News/getTuijianUserList');  //推荐用户列表
Route::get('getNewsCollectList', 'api/News/getNewsCollectList');  //关注文章列表
Route::get('deleteNews', 'api/News/deleteNews'); 				  //删除用户的一个文章
Route::get('deleteNews1', 'api/News/deleteNews1'); 				  //彻底删除用户的一个文章
Route::get('getNewsInfo', 'api/News/getNewsInfo'); 				  //获取文章详细信息
Route::get('getLunbotu', 'api/News/getLunbotu'); 				  //获取轮播图
Route::get('checkurl', 'api/News/checkurl'); 				  //检查地址 是否合法
Route::get('getUrlTitle', 'api/News/getUrlTitle'); 				  //拿取地址标题
Route::get('getQiyeNews', 'api/News/getQiyeNews');   		  	  //企业下的所有用户文章
Route::get('getzhiding', 'api/News/getzhiding');   		  		  //获取置顶企业下的文章
Route::get('getNewsfenxiang', 'api/News/getNewsfenxiang');   	  //获取一个文章的分享人信息


//Upload
Route::post('upload_file_fengmian', 'api/Upload/upload_file_fengmian_jiancai');   //上传文章封面
Route::post('upload_file_fengmian_jiancai', 'api/Upload/upload_file_fengmian_jiancai');   //上传文章封面 (剪裁)
Route::post('upload_file_img', 'api/Upload/upload_file_img');  			 //上传图片
//圈子  Moderator
Route::get('tianjiahuiyuan', 'api/Moderator/tianjiahuiyuan'); 				  //添加会员
Route::get('getmoderatorUserList', 'api/Moderator/getmoderatorUserList'); 	  //获取当前组下会员列表
Route::get('deleteMuser', 'api/Moderator/deleteMuser'); 	 				  //将会员从圈里移除
Route::post('addguanggaoimg', 'api/Moderator/addguanggaoimg'); 	              //添加广告图片
Route::get('getguanggaolist', 'api/Moderator/getguanggaolist'); 	          //获取广告列表
Route::get('delguanggao', 'api/Moderator/delguanggao'); 	         		  //根据广告id删除广告
Route::get('upggimg', 'api/Moderator/upggimg'); 	         		 		  //修改广告图片
Route::get('upggurl', 'api/Moderator/upggurl');
Route::get('upgg', 'api/Moderator/upgg'); 	         		 		  		  //修改企业广告信息  key=>val的形式
Route::get('getNewsGuanggao', 'api/Moderator/getNewsGuanggao'); 	          //根据文章id返回广告信息
Route::get('getGuanggaoInfo', 'api/Moderator/getGuanggaoInfo'); 	          //根据广告id返回广告信息
Route::get('delGuanggao', 'api/Moderator/delGuanggao'); 	         		  //根据广告id删除广告
Route::get('getQiyeInfo', 'api/Moderator/getQiyeInfo'); 	         		  //获取企业信息
Route::get('getmoderatorsousuo', 'api/Moderator/getmoderatorsousuo'); 		  //搜索企业
Route::get('upQiyeInfo', 'api/Moderator/upQiyeInfo'); 		      			  //修改企业信息
Route::get('setUserZhiding', 'api/Moderator/setUserZhiding');   	 		  //企业设置一个用户置顶


//分享后广告显示地址
// Route::get('GuanggaoView', 'api/Moderator/GuanggaoView');
Route::get('GuanggaoView', 'api/Guanggao/news');
Route::get('GuanggaoView1', 'api/Guanggao/news');
Route::get('GuanggaoView2', 'api/Test/news');  //测试

//AppQdGg
Route::get('getAppQdGg', 'api/AppQdGg/getAppQdGg');

//获取app版本
Route::get('getversion', 'api/Guanggao/getversion');


// Route::put('test', 'api/Index/put');
// Route::delete('test', 'api/Index/delete');

// Route::resource('test', 'api/Index/');
// return [
//     '__pattern__' => [
//         'name' => '\w+',
//     ],
//     '[hello]'     => [
//         ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
//         ':name' => ['index/hello', ['method' => 'post']],
//     ],

// ];
