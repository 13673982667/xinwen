<?php
namespace app\api\controller;
use think\Controller;
use think\Loader;
use think\assign;
use think\View;
use think\Request;
/**
 * API模块 公共的控制器
 * Class Common
 * @package app\api\controller
 */
class Common extends Controller {

	public $page = 1;
    public $size = 10;
    public $from = 0;
    public $map = [];

    public function __construct(){
        parent::__construct();
    	//初始化分业数据
    	$pagearr = [
    		'size' => input('size') ? input('size') : config('apiconfig.list_rows'),
    		'page' => input('page') ? input('page') : 1,
    	];
    	$this->getPageAndSize($pagearr);
    }

    /**
     * 获取分页page size 内容
     */
    public function getPageAndSize($data) {
        $this->page = !empty($data['page']) ? $data['page'] : 1;
        $this->size = !empty($data['size']) ? $data['size'] : config('apiconfig.list_rows');
        $this->from = ($this->page - 1) * $this->size;
    }

    /**
     * 查询条件
     * @param  [type] &$map 
     * @return array $map
     */
    // public function filter(&$map)
    // {
    //     if(!empty($this->map)){
    //         $map = array_merge($map,$this->map);
    //     }
    // }


}