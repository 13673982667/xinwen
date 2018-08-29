<?php
namespace app\api\controller;
use app\common\lib\exception\ApiException;
use think\Config;

/**
 * 上传类
 */
class Upload extends Common {

	//封面图片上传
	public function upload_file_fengmian() {
		if ($_FILES) {
			$img_path = $this->upload_file('uploadkey', Config::get('apiconfig.img_path'));
			if ($img_path) {
				$data = [
					'strings' => $_POST,
					'error' => 1,
					'files' => [
						'uploadkey' => [
							'url' => Config::get('apiconfig.this_host') . Config::get('apiconfig.img_path') . '/' . str_replace('\\', '/', $img_path),
						],
					],
				];
			}
			die(json_encode($data));

			//return show(1,'',array('sad',$img_path));
		}
		throw new ApiException(Config::get('apiconfig.errormessage'), Config::get('apiconfig.errorcode'));
	}

	//封面图片上传 (剪裁后的)
	public function upload_file_fengmian_jiancai() {
		if ($_FILES) {

			$image = \think\Image::open(request()->file('uploadkey'));
			// 返回图片的宽度
			$width = $image->width();
			// 返回图片的高度
			$height = $image->height();
			// $jc = 500;
			// $dw = $width - $jc;
			// $dh = $height - $jc;
			$jiancai = $width < $height ? $width : $height;
			$jiancai = $jiancai - ($jiancai * (0.05));
			$start = $width > $height ? $width - $jiancai : $height - $jiancai;

			//生成图片名称
			$path = './public/uploads/weixinfenxiang/' . date('Ymd', time()) . "/";
			// $path1 = './public/uploads/'.date('Ymd',time())."/";
			if (!file_exists($path)) {
				//检查是否有该文件夹，如果没有就创建，并给予最高权限
				mkdir($path, 0700);
			}
			// $path1 = $path1.time().rand(1000,9999).'.GIF';
			$path = $path . time() . rand(1000, 9999) . '.jpg';
			//将图片剪裁成计算出来的宽高
			$image->crop($jiancai, $jiancai, ($width < $height ? 0 : $start / 2), ($width > $height ? 0 : $start / 2))->save($path, 'jpg', 100);
			if (filesize($path) > (1024 * 32)) {
				$img2 = \think\Image::open($path);
				$img2->thumb(310, 310)->save($path);
			}

			// $img_path = $this->upload_file('uploadkey', Config::get('apiconfig.img_path'));
			if ($path) {
				//将图片进行剪裁
				// $image = \think\Image::open(Config::get('apiconfig.img_path').'/'.$img_path);
				// $image->crop(200, 200)->save(Config::get('apiconfig.img_path').'/'.$img_path);
				$data = [
					'strings' => $_POST,
					'error' => 1,
					'files' => [
						'uploadkey' => [
							'url' => Config::get('apiconfig.this_host') . $path,
						],
					],
				];
				die(json_encode($data));
			}

			//return show(1,'',array('sad',$img_path));
		}
		throw new ApiException(Config::get('apiconfig.errormessage'), Config::get('apiconfig.errorcode'));
	}
	//后台app广告图片上传
	public function upload_file_img() {
		// p($_SERVER,1);
		if ($_FILES) {
			$img_path = $this->upload_file('file', Config::get('apiconfig.img_path'));
			if ($img_path) {
				return show(1, '', Config::get('apiconfig.this_host') . Config::get('apiconfig.img_path') . '/' . str_replace('\\', '/', $img_path));
			}
			return show(0);
			//return show(1,'',array('sad',$img_path));
		}
		throw new ApiException(Config::get('apiconfig.errormessage'), Config::get('apiconfig.errorcode'));
	}

	static function upload_file($file, $path) {
		//获取表单上传文件 例如上传了001.jpg
		$file = request()->file($file);

		// 移动到框架应用根目录/public/uploads/ 目录下
		if ($file) {
			// $info = $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif'])->move($path);
			$info = $file->move($path);
			if ($info) {
				// 成功上传后 获取上传信息
				// 输出 jpg
				// echo $info->getExtension();
				// 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
				return $info->getSaveName();
				// 输出 42a79759f284b767dfcb2a0197904287.jpg
				// echo $info->getFilename();
			} else {
				return false;
				// 上传失败获取错误信息
				// return $file->getError();
			}
		}
	}

	public function aaa() {
		// $ret = array('msg'=>'Unsupport GET request!');
		//      echo json_encode($ret) ;
		// return;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$ret = array('strings' => $_POST, 'error' => '0');
			$fs = array();
			foreach ($_FILES as $name => $file) {
				$img = $file['name'];
				$dian = strrchr($img, '.');
				$imgname = "img_" . time() . mt_rand(100, 999) . $dian;
				$fn = $imgname;
				$ft = strrpos($fn, '.', 0);
				$fm = substr($fn, 0, $ft);
				$fe = substr($fn, $ft);
				//缩略图名称
				$h_name = "head_" . time() . mt_rand(100, 999) . $dian;
				$h_n = $h_name;
				$fp = './' . $fn;
				move_uploaded_file($file['tmp_name'], $fp);
				$fp = $fn;
				//  $slt = $this->slt($fp, $h_n);
				$slt = '/head_img/' . $fp;
				//unlink($fp);
				$fs[$name] = array('name' => $h_n, 'url' => $slt, 'type' => $file['type'], 'size' => $file['size']);
			}
			$ret['files'] = $fs;
			echo json_encode($ret);exit;

			//生成略缩图
		} else {
			$ret = array('msg' => 'Unsupport GET request!');
			echo json_encode($ret);exit;
			//echo "{'error':'Unsupport GET request!'}";
		}
	}

	public function slt($fp, $h_n) {
		$w = isset($_GET['w']) ? $_GET['w'] : 200;
		$h = isset($_GET['h']) ? $_GET['h'] : 200;
		$img = $fp;
		$filename = $h_n;
		$this->image_resize($img, $filename, $w, $h);
		return $filename;
	}

	//unlink($img);

	//header("content-type:image/png");//设定生成图片格式

	// 按指定大小生成缩略图，而且不变形，缩略图函数

	public function image_resize($f, $t, $tw, $th) {
		$temp = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');
		list($fw, $fh, $tmp) = getimagesize($f);
		if (!$temp[$tmp]) {
			return false;
		}
		$tmp = $temp[$tmp];
		$infunc = "imagecreatefrom$tmp";
		$outfunc = "image$tmp";
		$fimg = $infunc($f);
		//      $fw = 10;
		//      $fh = 4;
		//      $tw = 4;
		//      $th = 2;
		// 把图片铺满要缩放的区域
		if ($fw / $tw > $fh / $th) {
			$zh = $th;
			$zw = $zh * ($fw / $fh);
			$_zw = ($zw - $tw) / 2;
		} else {
			$zw = $tw;
			$zh = $zw * ($fh / $fw);
			$_zh = ($zh - $th) / 2;
		}
		//        echo $zw."<br>";
		//        echo $zh."<br>";
		//        echo $_zw."<br>";
		//        echo $_zh."<br>";
		//        exit;
		$zimg = imagecreatetruecolor($zw, $zh);
		// 先把图像放满区域
		imagecopyresampled($zimg, $fimg, 0, 0, 0, 0, $zw, $zh, $fw, $fh);
		// 再截取到指定的宽高度
		$timg = imagecreatetruecolor($tw, $th);
		imagecopyresampled($timg, $zimg, 0, 0, 0 + $_zw, 0 + $_zh, $tw, $th, $zw - $_zw * 2, $zh - $_zh * 2);
		if ($outfunc($timg, $t)) {
			return true;
		} else {
			return false;
		}

	}

} //
