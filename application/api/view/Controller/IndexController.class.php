<?php
namespace Home\Controller;

class IndexController extends CommonController {


/*
 * 首页数据控制器
 */
    public function index(){
    	//公司简介

        $profile = M('column')->where(array('c_id'=>2))->find();
        $this->assign('profile',$profile);
        // var_dump($profile);
        $profile_img=M('Plug_ad')->where(array('plug_ad_id'=>9))->find();
        $this->assign('profile_img',$profile_img);
    
        //投资加盟
        $investment1 =M('news')->where(array('news_columnid'=>5,'news_back'=>0,'news_open'=>1,'news_flag'=>1))->order('n_id asc')->limit(0,7)->select();
        $this->assign('investment1',$investment1);
        
        $investment3 =M('column')->where(array('column_leftid'=>5,'column_open'=>1))->order('c_id asc')->limit(1,4)->select();
        $this->assign('investment3',$investment3);
        
        
        $investment1_img=M('Plug_ad')->where(array('plug_ad_id'=>10))->find();
        $this->assign('investment1_img',$investment1_img);
        $investment2_img=M('Plug_ad')->where(array('plug_ad_id'=>11))->find();
        $this->assign('investment2_img',$investment2_img);
        
        //加盟优势
        $strengths_img=M('Plug_ad')->where(array('plug_ad_adtypeid'=>7))->select();
        $this->assign('bstrengths_img',$strengths_img);
        
        //谷太新闻
        $news = M('news')->where(array('news_columnid'=>29,'news_back'=>0,'news_open'=>1))->limit(0,4)->order('news_time desc')->select();
        $this->assign('news',$news);
        
        //新闻主图片
         $news_img=M('Plug_ad')->where(array('plug_ad_id'=>23))->find();
        $this->assign('news_img',$news_img);


   
    	
		$this->theme(C('DEFAULT_THEME'))->display();
    }

    public function about(){
        
        //内页图片
        $about_img=M('Plug_ad')->where(array('plug_ad_id'=>4))->find();
        $this->assign('about_img',$about_img);
        $about_img1=M('Plug_ad')->where(array('plug_ad_id'=>5))->find();
        $this->assign('about_img1',$about_img1);
        $c_id = I('c_id');
        $this->assign('c_id',$c_id);
        
        $column_about =M('column')->where(array('c_id'=>$c_id))->find();
        $this->assign('column_about',$column_about);
        
        $this->theme(C('DEFAULT_THEME'))->display();
    }
    
     public function brand(){
        $brand_img=M('Plug_ad')->where(array('plug_ad_id'=>6))->find();
        $this->assign('brand_img',$brand_img);
          
            $c_id = I('c_id');
           
            
            $column_about =M('column')->where(array('c_id'=>$c_id))->find();
            $this->assign('column_about',$column_about);
            
            $this->theme(C('DEFAULT_THEME'))->display();
        }
    
    public function contact(){
         
      
        $c_id = I('c_id');
        $this->assign('c_id',$c_id);
    
       
    
        $this->theme(C('DEFAULT_THEME'))->display();
    }
    
	public function ditu(){
	    $c_id = I('c_id');
	    $this->assign('c_id',$c_id);
	    
	    
	    $this->theme(C('DEFAULT_THEME'))->display();
	}
	
public function plug_sug_runadd(){
	   $name = I('name');
	   $phone = I('phone');
	   $content= I('content');
	    $sl_data=array(
	        'plug_sug_title'=>$name,
	        'plug_sug_email'=>$phone,
	        'plug_sug_content'=>$content,
	        'plug_sug_addtime'=>time(),
	        'plug_sug_ip'=>get_client_ip(),
	    );
	    if($name == null){
	        $this->error('请输入姓名',U('contact',array('c_id'=>14)),0);
	    }elseif ($phone == null){
	        $this->error('请输入电话',U('contact',array('c_id'=>14)),0);
	    }elseif ($content == null){
	        $this->error('请输入内容',U('contact',array('c_id'=>14)),0);
	    }else {
	        M('plug_sug')->add($sl_data);
	        $this->success('留言成功，等待管理员回复',U('index'),1);
	
	    }
	}
	
	
	
	
	
	
	
	
}