<?php
namespace Home\Controller;

class JoinController extends CommonController {

    
    public function join(){
        //var_dump($join);exit();
        $c_id=I('c_id');
        $this->assign('c_id',$c_id);
        
        
        
        $join_about =M('news')->where(array('news_columnid'=>$c_id))->limit(0,7)->select();
        $this->assign('join_about',$join_about);
        
        //投资加盟图片
        $join_img = M('column')->where('c_id=5')->find();
        $this->assign('join_img',$join_img);

        $this->theme(C('DEFAULT_THEME'))->display();

    }
    public function cases(){
        //var_dump($join);exit();
        $c_id=I('c_id');
        $this->assign('c_id',$c_id);
    
        $map['news_open']= array('eq',1);
        $map['news_back']= array('eq',0);
        $map['news_columnid']= array('eq',$c_id);
      
        $count= M('news')->where($map)->count();// 查询满足要求的总记录数
    
      
        $Page= new \Think\Pagetwo($count,9);// 实例化分页类 传入总记录数和每页显示的记录数
       // $Page -> setConfig('header','共%totalRow%条');
        $Page -> setConfig('first','首页');
      //  $Page -> setConfig('last','共%totalPage%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('next','下一页');
    
        $Page -> setConfig('theme','%header% %first% %upPage% %linkPage% %downPage% %end%');
 
       $show = $Page->show();
       
        /*
         * 获取该栏目文章列表
        */
        $cases_list=M('news')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('news_flag desc,news_time desc')->select();
 	$this->assign('cases_list',$cases_list);
        $this->assign('page',$show);
         
    
        $this->theme(C('DEFAULT_THEME'))->display();
    }
    
    public function cases_about(){
      
        $n_id=I('n_id');
    
      $arr=D('news')->where(array('n_id'=>$n_id))->find();
        $c_id = $arr['news_columnid'];
        $this->assign('c_id',$c_id);
        $news_content=D('News')->where(array('n_id'=>$n_id))->relation(true)->find();
       
        $this->assign('news_content',$news_content);
        $news_body=M('news_content')->where(array('news_content_nid'=>$n_id))->find();
        $this->assign('news_body',$news_body);

        
        /*
         * 上一篇
        */
        
        $front=M('news')->where("n_id<$n_id and news_columnid=$c_id and news_back = 0 and news_open= 1" )->order('news_time desc')->limit('1')->find();
        $f=!$front?'0':$front;
        $this->assign('front',$f);
        
        /*
         * 下一篇
        */
        $after=M('news')->where("n_id>$n_id and news_columnid=$c_id and news_back = 0 and news_open= 1")->order('news_time asc')->limit('1')->find();
        $a=!$after?'0':$after;
        $this->assign('after',$a);
    
    
        $this->theme(C('DEFAULT_THEME'))->display();
    }
    
    
    public function about(){
      
    
        
        //内容 C
        $column_about =M('column')->where(array('c_id'=>I('c_id')))->find();
        $this->assign('column_about',$column_about);
        //图片
        

        $this->theme(C('DEFAULT_THEME'))->display();
    }
    
    
    
    
    
    
    
    
}
