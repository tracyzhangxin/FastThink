<?php
/**
 * Project ~ 自定义异常处理类
 * FileName: InvalidException.class.php
 * 
 * 异常控制器(主要是抛出异常错误给Ajax)
 * 
 * @author zhangxin 
 * @version 1.0
 * @since 1.0 
 * Date			2017/8/25
 */
 
 class InvalidException extends Exception
 {
 	//自定义错误状态码
 	private $code;
 	//错误信息描述
 	private $msg;
 	//错误行数
 	private $line;

 	
 }

 ?>