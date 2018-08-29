<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:74:"D:\phpstudy\PHPTutorial\WWW\www.xw.com/application/api\view\test\news.html";i:1524737373;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $newsInfo['title']; ?></title>
	<!-- <link rel="stylesheet" type="text/css" href="__PUBLIC__/static/api/css/reset.css" /> -->
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
</head>
<style type="text/css">
	.mui-content{
		height:100%;
		padding-top:;
	}
	.tel{
		width: 45%;
		height:  25%;
		position: absolute;
		bottom: 15%;
		left:0;
		color: #fff;
		/*background-color: #ff0000;*/
		background: url('__PUBLIC__/uploads/img/tel.png');
		background-repeat: no-repeat;
		background-size: 100% 100%;

	}
	.address{
		width: 45%;
		height: 25%;
		position: absolute;
		bottom: 12%;
		right:0;
		color: #fff;
		background: url('__PUBLIC__/uploads/img/address.png');
		background-repeat: no-repeat;
		background-size: 100% 100%;
	}
	.gg{
		position: fixed;
		width: 100%;
		z-index: 999;
		height: 100%;
		top:0;

	}
	/*.mui-content{
		position: absolute;
		height: 100%;
	}*/
	.iframe{
		position: relative;
		top: 0;
		z-index: 1;
	}
	.bottom{
		/*height: 12%;*/
	}
	.bottom-img{
		display: flex;

	}
	.yidong{
		height: 100%;
		width: 100%;
		position: fixed;
		top: 0;
		z-index: 555;
	}
</style>
<link rel="stylesheet" type="text/css" href="__PUBLIC__/static/mui/mui.css">
<link rel="stylesheet" type="text/css" href="__PUBLIC__/static/swiper/swiper-4.2.0.min.css">
<link rel="stylesheet" type="text/css" href="__PUBLIC__/static/api/css/guanggao_news.css">
<script src="__PUBLIC__/static/common/js/jquery-2.1.0.js"></script>

<script>
//上滑
// $(window).on('swipeup',function(event){
// 	event.preventDefault();
// 	// hei = $('.new-title').height();
// 	$('.new-title').height(h);
// 	mui.toast(0);
// });
// //下滑
// $(window).on('swipedown',function(){
// 	mui.toast(h);
// 	$('.new-title').height(0);
// });
</script>
<body>
<!-- <div class="yidong"></div> -->
<!-- <img src="__PUBLIC__/uploads/img/tel.png" alt=""> -->
	<div class="mui-content">
		<?php if($AppTitleGg): ?>
		<div class="new-title" style="height: <?php echo $AppTitleGg['height']; ?>px;">
			<a href="<?php echo $AppTitleGg['url']; ?>" class="bottom-img"  style="width: 100%; height: 100%; display :inline-block; ">
    			<img src="<?php echo $AppTitleGg['img']; ?>"  style="position: absolute;top:0;width: 100%;  display :inline-block; " alt="">
    		</a href="">
    		<span class="chac"><img src="__PUBLIC__/uploads/img/chac.png" alt=""></span>
		</div>
		<?php endif; if($getNewsGg): ?>
		<div class="body-">
			<div class="body-img">
				<a class="img" href="<?php echo $getNewsGg['url']; ?>">
					<img width="100%" height="100%" src="<?php echo $getNewsGg['img']; ?>">
				</a>
				<div class="back"><img src="__PUBLIC__/uploads/img/cha.png" alt=""></div>
			</div>
		</div>
		<?php endif; if($getNewsbottom): ?>
		<div class="bottom" style="height: <?php echo $getNewsbottom['height']; ?>px;">
    		<a href="<?php echo $getNewsbottom['url']; ?>" class="bottom-img"  style="width: 100%; height: 100%; display :inline-block; ">
    			<img src="<?php echo $getNewsbottom['img']; ?>"  style="width: 100%; height: 100%; display :inline-block; " alt="">
    		</a href="">
    		<div class="btback"><img src="__PUBLIC__/uploads/img/chac.png" alt=""></div>
		</div>
		<?php endif; ?>


		<div class="gg">
			<?php if(!empty($guanggao)): ?>
			<a <?php if($guanggao['arr'][0]['ggurl'] != ''): ?>href="<?php echo $guanggao['arr'][0]['ggurl']; ?>"<?php endif; ?> class="ad-img">
				<div class="tiaoguo">跳过</div>
				<img src="<?php echo $guanggao['arr'][0]['ggimg']; ?>" />
			</a>
			<div class="ad-logo">
				<img src="<?php echo (isset($appguanggao['appguanggaoimg']) && ($appguanggao['appguanggaoimg'] !== '')?$appguanggao['appguanggaoimg']:'./public/uploads/ad-logo.png'); ?>" />
			</div>
				<!-- 联系方式 -->
				<?php if(!empty($guanggao['arr'][0]['tel'])): ?>
					<a href="tel:<?php echo $guanggao['arr'][0]['tel']; ?>" class="tel"></a>
				<?php endif; ?>
				<!-- 联系地址 -->
				<?php if(!empty($guanggao['arr'][0]['lat'])&&!empty($guanggao['arr'][0]['lng'])): ?>
				<div class="address_">
					<a  class="address"></a>
				</div>
				<?php endif; endif; ?>
		</div>

		<div class="iframe">
			<iframe src="<?php echo $newsInfo['news_url']; ?>" id="iframetest"  scrolling="yes" allowtransparency="yes" frameborder="0" ></iframe>
		</div>

	</div>
</body>

</html>
<script src="__PUBLIC__/static/mui/mui.js"></script>
<!-- <script src="__PUBLIC__/static/common/js/zepto.js"></script> -->
<!-- <script src="__PUBLIC__/static/common/js/arttmpl.js"></script> -->
<script src="__PUBLIC__/static/swiper/swiper-4.2.0.min.js"></script>
<!-- 百度地图包 -->
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=bE7mlBvGPyQf8yUkL4HHknvPB3WzTSUC"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8" ></script>
<!-- <script src="__PUBLIC__/static/swiper/swiper3.js"></script> -->
<script type="text/javascript">

mui.init({

})

var h = $('.new-title').height();

var address;
	setTimeout(function(){
		contentshow();

	},(<?php echo (isset($guanggao['ggtime'] ) && ($guanggao['ggtime']  !== '')?$guanggao['ggtime'] :'0'); ?> * 1000));
	$('.tiaoguo').click(function(e){
		e.preventDefault(); // -- 阻止元素的默认事件。
		contentshow();
	});

	//显示文章内容
	function contentshow(){
		// $('.mui-content').html(template('mui-content',{}));
		$('.gg').remove();
		//关闭中部广告
		$('.back').on('click',function(){
			$('.body-').remove();
		})
		//关闭底部广告
		$('.btback').on('click',function(){
			$('.bottom').remove();
		});
		//关闭上部广告
		$('.chac').on('click',function(){
			$('.new-title').remove();
		});
		<?php if($AppTitleGg): ?>
		console.log(h);
		$('.mui-content').css({"padding-top":h});
		<?php endif; ?>
	}


<?php if(!empty($guanggao['arr'][0]['lat'])&&!empty($guanggao['arr'][0]['lng'])): ?>
$('.address').on('tap',function(){
	Map()
});
function Map(){
	// 百度地图API功能
	var map = new BMap.Map("allmap");
	var geolocation = new BMap.Geolocation(); //获取当前坐标
	geolocation.getCurrentPosition(function(r){
		if(this.getStatus() == BMAP_STATUS_SUCCESS){
			//默认当前的坐标
			// console.log(JSON.stringify(r));
			address = {"lng":r.point.lng,"lat":r.point.lat};
			var url = 'http://api.map.baidu.com/direction?'+
				'origin=latlng:'+address.lat+','+address.lng+'|name:我的位置&'+
				"destination=latlng:<?php echo $guanggao['arr'][0]['lat']; ?>,<?php echo $guanggao['arr'][0]['lng']; ?>|name:<?php echo (isset($address_arr[3]) && ($address_arr[3] !== '')?$address_arr[3]:'终点'); ?>"+
				'&destination_region=<?php echo (isset($address_arr[1]) && ($address_arr[1] !== '')?$address_arr[1]:''); ?>'+
				'&origin_region='+r.address.city+
				'&mode=driving&output=html&src=yourCompanyName|yourAppName';
				// console.log(url);
				window.location.href=url;
				// $('.address_').append('<a class="address" href="'+url+'"></a>');
		}
	},{enableHighAccuracy: true})
}
<?php else: ?>
$('.address').on('tap',function(){
	mui.toast('用户还没有添加地址哦！')
});
<?php endif; ?>











wx.ready(function(){
		//朋友圈
	wx.onMenuShareTimeline({
	    title: $('title').html(), // 分享标题
	    link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
	    imgUrl: '<?php echo empty($newsInfo['w_cover'])?$newsInfo['headimgurl']:$newsInfo['w_cover']; ?>', // 分享图标
	    success: function () {
	    // 用户确认分享后执行的回调函数
		},
		cancel: function () {
	    // 用户取消分享后执行的回调函数
	  	}
	});
	//分享朋友
	wx.onMenuShareAppMessage({
		title: $('title').html(), // 分享标题
		desc: '', // 分享描述
		link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
		imgUrl: '<?php echo empty($newsInfo['w_cover'])?$newsInfo['headimgurl']:$newsInfo['w_cover']; ?>', // 分享图标
		type: '', // 分享类型,music、video或link，不填默认为link
		dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
		success: function () {
		// 用户确认分享后执行的回调函数
		},
		cancel: function () {
		// 用户取消分享后执行的回调函数
		}
	});
    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
});

wx.error(function(res){
    // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
});






</script>