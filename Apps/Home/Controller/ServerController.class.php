<?php
/**
 * Project ~ Server控制器 实现基础服务demo
 * FileName: ServerController.class.php 
 * Demo控制器
 * 
 * @author zhangxin 
 * @version 1.0
 * @since 1.0 
 * Date			2017/8/24
 */
namespace Home\Controller;
use Common\Controller\BaseController;
class ServerController extends BaseController
{
	public function __construct(){
		parent::__construct();
		$this->initHproseService();
	}

	/**
	 * 魔术方法 有不存在的操作的时候执行,若没有index方法时用，否则会报错
	 * 
	 * @access public 
	 * @param string $method 方法名
	 * @param array $args 参数
	 * @return mixed 
	 */
	public function __call($method, $args) {

	}


	public function hello(){
		return "this is Hprose Service!!!";
	}

	public function push(){
		return "this is push";
	}
}
?>