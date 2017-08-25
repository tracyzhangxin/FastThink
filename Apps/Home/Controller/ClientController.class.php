<?php
/**
 * Project ~ Client控制器 实现调用基础服务demo
 * FileName: ClientController.class.php
 * 
 * demo控制器
 * 
 * @author zhangxin 
 * @version 1.0
 * @since 1.0 
 * Date			2017/8/24
 */
namespace Home\Controller;
use Think\Controller;
class ClientController extends Controller
{
	
	function __construct()
	{
		parent::__construct();
		Vendor('Hprose.HproseHttpClient');
	}

	public function getHello(){
		$client=new \HproseHttpClient('http://www.FastThink.com/index.php?m=Home&c=Server');
		$res=$client->hello();
		echo $res;
	}

	public function getPush(){
		$client=new \HproseHttpClient('http://www.FastThink.com/index.php?m=Home&c=Server');
		$res=$client->push();
		echo $res;
	}
}
?>