<?php
namespace Home\Controller;

use Think\Controller;
class CommonController extends Controller
{
    
    /*
     * 公共控制器，输出导航以及底部提交信息
     */
    Public function _initialize(){
        /*
         * 公共模块
         * 主要作用于顶部导航
         */
        $column=M('column');
        $map['column_leftid'] = array('NEQ',0);
        $map['column_open'] = 1;
        $nav = new \Org\Util\Leftnav;
        $column_one=$column->where(array('column_leftid'=>0,'column_open'=>1))->order('column_order')->select();//顶级数据
        $column_two=$column->where($map)->order('column_order')->select();//所有数据
        $arr = $nav::index_top($column_one,$column_two);
        $this->assign('arr',$arr);
        $sys=M('sys')->where(array('sys_id'=>1))->find();
        $this->assign('sys',$sys);
        
        //banner图
        $banner_img=M('Plug_ad')->where(array('plug_ad_adtypeid'=>1))->select();
        $this->assign('banner_img',$banner_img); 
        
        //内页标题图
        $biaoti_img = M('column')->where(array('c_id'=>I('c_id')))->find();
        $this->assign('biaoti_img',$biaoti_img['column_img']);
        $this->assign('biaoti',$biaoti_img['column_name']);
        //关于谷太
        $gutai= M('column')->where(array('c_id'=>1,'column_open'=>1))->find();
        $this->assign('gutai',$gutai);
        $about = M('column')->where(array('column_leftid'=>1,'column_open'=>1))->select();
        $this->assign('about',$about);
        
        //关于谷太内页图片
        $gutai_img = M('plug_ad') ->where(array('plug_ad_id'=>4))->find();
        $this->assign('gutai_img',$gutai_img);
        //主页头条关于谷太信息
        $gutai_index = M('news')->where(array('news_columnid'=>1,'news_open'=>1))->find();
        $this->assign('gutai_index',$gutai_index);
        //招商加盟
        $join1= M('column')->where(array('c_id'=>5,'column_open'=>1))->find();
        $this->assign('join1',$join1);
        $join = M('column')->where(array('column_leftid'=>5,'column_open'=>1))->select();
        $this->assign('join',$join);
        //招商加盟内页图片
        $join_img = M('plug_ad') ->where(array('plug_ad_id'=>5))->find();
        $this->assign('join_img',$join_img);
        //品牌合作
        $product1= M('column')->where(array('c_id'=>8,'column_open'=>1))->find();
        $this->assign('product1',$product1);
        $product = M('column')->where(array('column_leftid'=>8,'column_open'=>1))->select();
        $this->assign('product',$product);
        //品牌合作内页图片
        $product_img = M('plug_ad') ->where(array('plug_ad_id'=>6))->find();
        $this->assign('product_img',$product_img);
        //形象展示
        $show1= M('column')->where(array('c_id'=>10,'column_open'=>1))->find();
        $this->assign('show1',$show1);
        $show = M('column')->where(array('column_leftid'=>10,'column_open'=>1))->select();
        $this->assign('show',$show);
        //主页形象展示
        $gutai_xingxiang = M('news')->where(array('news_columnid'=>10,'news_open'=>1))->find();
        $this->assign('gutai_xingxiang',$gutai_xingxiang);
        //品牌合作内页图片
        $show_img = M('plug_ad') ->where(array('plug_ad_id'=>7))->find();
        $this->assign('show_img',$show_img);
        //新闻中心
        $news1= M('column')->where(array('c_id'=>11,'column_open'=>1))->find();
        $this->assign('news1',$news1);
        $news = M('column')->where(array('column_leftid'=>11,'column_open'=>1))->select();
        $this->assign('news',$news);
        //新闻中心
        $growing= M('column')->where(array('c_id'=>34,'column_open'=>1))->find();
        $this->assign('growing',$growing);
      
        //品牌合作内页图片
        $news_img = M('plug_ad') ->where(array('plug_ad_id'=>8))->find();
        $this->assign('news_img',$news_img);
        //友情链接
        $link = M('plug_link')->where(array('plug_link_typeid'=>1,'plug_link_open'=>1))->select();  //dump($link);
        $this->assign('link',$link);
        //成功案例   
        $map['news_open']= array('eq',1);
        $map['news_back']= array('eq',0);
        $map['news_columnid']= array('eq',18);
        $news_list = M('news')->where($map)->limit('0,4')->order('news_flag desc,news_time desc')->select();//成功案例
        
        $this->assign('news_list',$news_list);  
        //形象展示 每三个一页
        $count = M('news')->where($map)->count();
        if(((int)$count)>3){
            $index = 0;
            $arr = array();
            for($i=1;$i<=$count;$i++){
                 $arr[$index] = M('news')->where($map)->limit($index*3,3)->order('news_flag desc,news_time desc')->select();
                 if($i%3==0)$index++;
            }

        }else{
            $arr[0]=M('news')->where($map)->limit('0,4')->order('news_flag desc,news_time desc')->select();
           
        }
        $this->assign('arr_list',$arr);
        
        //底部
        $column_arr = $column->select();
        $fornum = 0;
        $column_list = array();
        if( !empty($column_arr) ){
            //找一级分类
            foreach ($column_arr as $k => $v) {
                if($v['column_leftid']==0){
                    $column_list[] = $v;
                    unset($column_arr[$k]);
                }
            }
        }
        if( !empty($column_list) ){
            //找二级分类
            foreach ($column_list as $k => $v) {
                foreach ($column_arr as $key => $value) {
                    if( $v['c_id'] == $value['column_leftid'] ){
                        $column_list[$k]['zilei'][] = $value;
                        unset($column_arr[$key]); 
                    }
                }
                if( $v['c_id'] == 34 ){
                        $column_list[$k]['zilei'] = M('column')->where('c_id = 34')->select();
                } 
            }
        }
        $this->assign('column_arr',$column_list);
        //内页轮播图
        $neiye = array();
        $neiye[] = M('plug_ad')->where('plug_ad_id = 33')->select();
        $neiye[] = M('plug_ad')->where('plug_ad_id = 34')->select();
        $neiye[] = M('plug_ad')->where('plug_ad_id = 35')->select();
        $this->assign('neiimg',$neiye);
        //谷太商城
        $gtsc = M('column')->where('c_id = 34')->select();
        $this->assign('gtsc',$gtsc);
        /*
         * 头部标题
         * */
        $c_id = I('c_id');
        $column_title =M('column')->where(array('c_id'=>$c_id))->find();
        $this->assign('column_title',$column_title);
        
    }
   
    
    
}