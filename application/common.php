<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 模拟tab产生空格
 * @param int $step
 * @param string $string
 * @param int $size
 * @return string
 */
function tab($step = 1, $string = ' ', $size = 4) {
	return str_repeat($string, $size * $step);
}

/**
 * 打印数据
 * @param  [type] $var    [description]
 * @param  int  是否结束
 */
function p($var, $is_die = 0) {
	if (is_array($var)) {
		echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#f5f5f5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>" . print_r($var, true) . "</pre>";
	} else {
		var_dump($var);
	}
	if ($is_die == 1) {
		exit;
	}
}

/**
 * 通用化API接口数据输出
 * @param int $code 业务状态码
 * @param string $message 信息提示
 * @param [] $data  数据
 * @param int $httpCode http状态码
 * @return array
 */
function show($code, $message = '', $data = [], $httpCode = 200) {

	$data = [
		'code' => $code,
		'message' => $message == '' ? 'ok' : $message,
		'data' => $data,
	];

	return json($data, $httpCode);
}

/**
 * 字符串截取，支持中文和其他编码
 * static
 * access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists("mb_substr")) {
		$slice = mb_substr($str, $start, $length, $charset);
	} elseif (function_exists('iconv_substr')) {
		$slice = iconv_substr($str, $start, $length, $charset);
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("", array_slice($match[0], $start, $length));
	}
	return $suffix ? $slice . '...' : $slice;
}

/**
 * ping一个地址是否正确
 * @param string $address 域名地址
 * return boolean
 */
function pingAddress($address) {
	$status = -1;
	if (strcasecmp(PHP_OS, 'WINNT') === 0) {
		// Windows 服务器下
		$pingresult = exec("ping -n 1 {$address}", $outcome, $status);
	} elseif (strcasecmp(PHP_OS, 'Linux') === 0) {
		// Linux 服务器下
		$pingresult = exec("ping -c 1 {$address}", $outcome, $status);
	}
	if (0 == $status) {
		$status = true;
	} else {
		$status = false;
	}
	// p($pingresult);
	return $status;
}

/**
 *几分钟前、几小时前、几天前的几个函数
 *  @param string "2014-7-8 19:22:01"
 */
function time_tran($the_time) {
	$now_time = date("Y-m-d H:i:s", time());
	//echo $now_time;
	$now_time = strtotime($now_time);
	$show_time = strtotime($the_time);
	$dur = $now_time - $show_time;
	if ($dur < 0) {
		return $the_time;
	} else {
		if ($dur < 60) {
			return $dur . '秒前';
		} else {
			if ($dur < 3600) {
				return floor($dur / 60) . '分钟前';
			} else {
				if ($dur < 86400) {
					return floor($dur / 3600) . '小时前';
				} else {
					if ($dur < 259200) {
//3天内
						return floor($dur / 86400) . '天前';
					} else {
						return '';
					}
				}
			}
		}
	}
}

/**
 * 将base64转图片并保存
 * @param $base64_image_content string base64编码
 * @param $url
 */
function uploadbase64($base64_image_content, $url = '') {
	//匹配出图片的格式
	if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {

		$type = $result[2];
		$new_file = './public/uploads/jiancai/' . date('Ymd', time()) . "/";
		if (!file_exists($new_file)) {
			//检查是否有该文件夹，如果没有就创建，并给予最高权限
			mkdir($new_file, 0700);
		}
		$url = empty($url) ? time() : $url;
		$new_file = $new_file . $url . ".{$type}";
		if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
			return $new_file;
		} else {
			return false;
		}
	}
}

//图片处理  路径的形式  处理成微信分享支持的大小 32k
function imgcl($file) {
	$image = \think\Image::open($file);
	$width = $image->width(); // 返回图片的宽度
	$height = $image->height(); // 返回图片的高度
	$size = filesize($file); //文件大小
	$wxsize = 32 * 1024; //微信支持的大小
	//不大于的话就不处理
	if ($size < $wxsize) {
		return $file;
	}
	// $surplus = ($size - $wxsize) / $size;  //计算超出的百分比 大小
	$zb = $wxsize / $size; //微信占图片百分比
	$p = $width * $height; //计算面积
	//按百分比算出面积
	$x = $p * $zb;
	//计算出正方形的宽高
	$wxwidth = sqrt($x); //平方根。

	//生成图片名称
	$path = './public/uploads/weixinfenxiang/' . date('Ymd', time()) . "/";
	if (!file_exists($path)) {
		//检查是否有该文件夹，如果没有就创建，并给予最高权限
		mkdir($path, 0700);
	}
	$path = $path . time() . rand(1000, 9999) . '.png';
	// p($path,1);
	//宽高都大于计算出来的宽高的话
	if ($width >= $wxwidth && $height >= $wxwidth) {
		//多余的宽高
		$dw = $width - $wxwidth;
		$dh = $height - $wxwidth;
		//将图片剪裁成计算出来的宽高
		// p($path,1);
		$image->crop($wxwidth, $wxwidth, ($dw / 2), ($dh / 2))->save($path);
		return $path;
	} else {
//否则 哪个大剪裁哪个 按比例剪裁

		$bl = $width > $height ? $width : $height; //剪裁哪个
		$jc = $bl * $zb; //
		$ww = $width > $height ? $jc : $width;
		$wh = $width > $height ? $height : $jc;

		$dw = $width > $height ? ($width - $jc) / 2 : 0;
		$dh = $width > $height ? 0 : ($width - $jc) / 2;

		//将图片剪裁成计算出来的宽高
		$image->crop($ww, $wh, $dw, $dh)->save($path);
		return $path;
	}
	return false;
}

//抓取url 地址的title
function getUrlTitle($url) {
	$c = curl_init();
	$url = $url;
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
	//因为“https”是加密的，所以要在curl设置参数里面加上上面两句话，才能得到
	$data = curl_exec($c);
	curl_close($c);
	$pos = strpos(strtolower($data), 'utf-8');
	// p($pos,1);

	if ($pos === false) {
		$data = iconv("gbk", "UTF-8//IGNORE", $data);
	}
	preg_match("/<title>(.*)<\/title>/i", $data, $title);
	// p($title,1);
	if ($title[1]) {
		return $title[1];
	}
	return false;
}
//抓取url 地址的content
function getUrlContent($url) {
	$c = curl_init();
	$url = $url;
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
	//因为“https”是加密的，所以要在curl设置参数里面加上上面两句话，才能得到
	$data = curl_exec($c);
	curl_close($c);

	return $data;
}

/**
 * 图片压缩处理
 * @param string $sFile 图片路径
 * @param int $iWidth 自定义图片宽度
 * @param int $iHeight 自定义图片高度
 */
function getThumb($sFile, $iWidth, $iHeight) {
	//判断该图片是否存在
	if (!file_exists(public_path() . $sFile)) {
		return $sFile;
	}

	//判断图片格式
	$attach_fileext = get_filetype($sFile);
	if (!in_array($attach_fileext, array('jpg', 'png', 'jpeg'))) {
		return $sFile;
	}
	//压缩图片
	$sFileNameS = str_replace("." . $attach_fileext, "_" . $iWidth . '_' . $iHeight . '.' . $attach_fileext, $sFile);
	//判断是否已压缩图片，若是则返回压缩图片路径
	if (file_exists(public_path() . $sFileNameS)) {
		return $sFileNameS;
	}
	//解决手机端上传图片被旋转问题
	if (in_array($attach_fileext, array('jpeg'))) {
		adjustPicOrientation(public_path() . $sFile);
	}
	//生成压缩图片，并存储到原图同路径下
	resizeImage(public_path() . $sFile, public_path() . $sFileNameS, $iWidth, $iHeight);
	if (!file_exists(public_path() . $sFileNameS)) {
		return $sFile;
	}
	return $sFileNameS;
}

/**
 *获取文件后缀名
 */
function get_filetype($filename) {
	$extend = explode(".", $filename);
	return strtolower($extend[count($extend) - 1]);
}

/**
 * 解决手机上传图片被旋转问题
 * @param string $full_filename 文件路径
 */
function adjustPicOrientation($full_filename) {
	$exif = exif_read_data($full_filename);
	if ($exif && isset($exif['Orientation'])) {
		$orientation = $exif['Orientation'];
		if ($orientation != 1) {
			$img = imagecreatefromjpeg($full_filename);

			$mirror = false;
			$deg = 0;

			switch ($orientation) {
			case 2:
				$mirror = true;
				break;
			case 3:
				$deg = 180;
				break;
			case 4:
				$deg = 180;
				$mirror = true;
				break;
			case 5:
				$deg = 270;
				$mirror = true;
				break;
			case 6:
				$deg = 270;
				break;
			case 7:
				$deg = 90;
				$mirror = true;
				break;
			case 8:
				$deg = 90;
				break;
			}
			if ($deg) {
				$img = imagerotate($img, $deg, 0);
			}

			if ($mirror) {
				$img = _mirrorImage($img);
			}

			//$full_filename = str_replace('.jpg', "-O$orientation.jpg",  $full_filename);新文件名
			imagejpeg($img, $full_filename, 95);
		}
	}
	return $full_filename;
}

// resizeImage(public_path().$sFile, public_path().$sFileNameS, $iWidth, $iHeight);
function public_path() {
	return '';
}
/**
 * 生成图片
 * @param string $im 源图片路径
 * @param string $dest 目标图片路径
 * @param int $maxwidth 生成图片宽
 * @param int $maxheight 生成图片高
 */
function resizeImage($im, $dest, $maxwidth, $maxheight) {
	$img = getimagesize($im);
	switch ($img[2]) {
	case 1:
		$im = @imagecreatefromgif($im);
		break;
	case 2:
		$im = @imagecreatefromjpeg($im);
		break;
	case 3:
		$im = @imagecreatefrompng($im);
		break;
	}

	$pic_width = imagesx($im);
	$pic_height = imagesy($im);
	$resizewidth_tag = false;
	$resizeheight_tag = false;
	if (($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)) {
		if ($maxwidth && $pic_width > $maxwidth) {
			$widthratio = $maxwidth / $pic_width;
			$resizewidth_tag = true;
		}

		if ($maxheight && $pic_height > $maxheight) {
			$heightratio = $maxheight / $pic_height;
			$resizeheight_tag = true;
		}

		if ($resizewidth_tag && $resizeheight_tag) {
			if ($widthratio < $heightratio) {
				$ratio = $widthratio;
			} else {
				$ratio = $heightratio;
			}

		}

		if ($resizewidth_tag && !$resizeheight_tag) {
			$ratio = $widthratio;
		}

		if ($resizeheight_tag && !$resizewidth_tag) {
			$ratio = $heightratio;
		}

		$newwidth = $pic_width * $ratio;
		$newheight = $pic_height * $ratio;

		if (function_exists("imagecopyresampled")) {
			$newim = imagecreatetruecolor($newwidth, $newheight);
			imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
		} else {
			$newim = imagecreate($newwidth, $newheight);
			imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
		}

		imagejpeg($newim, $dest);
		imagedestroy($newim);
	} else {
		imagejpeg($im, $dest);
	}
}