<?php
/**
 * Project ~ Hprose/Swoole
 * FileName: BaseController.class.php
 * 
 * 基础控制器(主要是对基础服务初始化封装)
 * 
 * @author zhangxin 
 * @version 1.0
 * @since 1.0 
 * 
 * Date			2017/8/24
 */
namespace Common\Controller;
use Think\Controller;
class BaseController extends Controller
{
	//token服务器缓存时间
	protected $defaultCache=3600;
	//token有效期内请求次数
	protected $defaultCount=10000;
	//是否开启调试
	protected $debug=true;
	//是否发送P3P的http头，以使浏览器可以跨域接收Cookie
	protected $P3P=true;
	//是否允许服务器接收get请求
	protected $get=true;
	//是否支持JavaScript、ActionScript和SilverLight等客户端的跨域请求
	protected $crossDomain=true;
	//允许子类服务暴露的父类方法
	protected $inheritMethods=array();

	public function __construct(){
		parent::__construct();
		Vendor('Hprose.Hprose');
	}

	/**
	* @Method Name:HproseHttpServer服务初始化方法 
	* @Author: zhangxin
	* @Return: void
	*/ 
	public function initHproseService(){
		//实例化HproseHttpServer对象
		$server=new \HproseHttpServer();
		if ($this->allowMethodList) {
			$methods=$this->allowMethodList;
		}else{
			//获取当前类可用方法（包括父类）
			$methods=get_class_methods($this);
			//获取父类继承过来的方法
			$parentMethods=get_class_methods(get_parent_class($this));

			//如果检测到除了本类(服务)定义的方法外定义了需要继承发布的父类方法，
			//则将这些方法从所有父类继承方法中排除
			if (!empty($this->inheritMethods)) {
				$notInherits=array_diff($parentMethods, $this->inheritMethods);
			}

			//得到最终的需过滤方法数组
			$finalDiffMethods=!empty($notInherits)?$notInherits:$parentMethods;
			//用过滤数组过滤发布方法，获得最终需要服务发布的方法
			$mothods=array_diff($methods, $finalDiffMethods);

		}

		$server->addMethods($methods,$this);
		if ($this->debug) {
			$server->setDebugEnabled(true);
		}

		//Hprose设置
		//是否跨域访问
		$server->setCrossDomainEnabled($this->crossDomain);
		//是否发送P3P的http头，这个头的作用是让IE允许跨域接收Cookie
		$server->setP3PEnabled($this->P3P);
		//禁止服务器接收GET请求 参数设置为false即可
		$server->setGetEnabled($this->get);
		//启动server
		$server->start();
	}

	public function initSwooleService(){

	}
}
?>