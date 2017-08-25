<?php
/**
 * Project ~ FastThink
 * FileName: IndexController.class.php
 * 
 * Index控制器
 * 
 * @author zhangxin 
 * @version 1.0
 * @since 1.0 
 * 
 * Date            2017/8/25
 */
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->display();
    }
    public function getError(){
        //抛出错误,给前端ajax接收处理
    	throw new \Think\Exception('新增失败',-1);
    }
}