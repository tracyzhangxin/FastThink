<?php
/**
  * Project ~ EasyAdmin
  * FileName: CommonController.class.php
  * 
  * 公共控制器 主要是
  * 
  * @author zhangxin 
  * @version 1.0
  * @since 1.0 
  * 
  * Date			2017/9/4
  */
namespace Common\Controller;

use Think\Controller;
class CommonController extends Controller
{
	private $menuRows=array();
	private $menuNav=array();
	private $systemId=null;

	protected $mMenu=null;
	protected $mSystem=null;

	/**
	* @Method Name: 全局初始化函数
	* @Author: zhangxin
	* @Return: void 
	*/ 
	public function _initialize(){
		//初始化菜单对象
		$this->mMenu=M();
		$this->mSystem=M();

		//初始化菜单
		$this->initMenu();
	}

	/**
	* @Method Name: 初始化全局菜单
	* @Author: zhangxin
	* @Return: void
	*/ 
	private function initMenu(){
		$moduleName=MODULE_NAME;
		$this->systemId=
		$this->menuRows=

		foreach ($this->menuRows as $k => $v) {
			$v['my_p']=html_entity_decode();
			if ($v['is_navi_class']==1) {
				$this->menuNav[]=$v;
			}
		}

		$this->assign('menuNav',$this->menuNav);
	}

	/**
	* @Method Name: 返回菜单json字符串
	* @Param: $getNavi 是否返回导航分层
	* @Author: zhangxin
	* @Return: array() 
	*/ 
	 public function menu($getNavi=false){
	 	$spTree=new \Common\Lib\SpTree;
	 	$spTree->init($this->menuRows);
	 	$spTree->setParentMarker('pid');
	 }
	 
	 
}


?>