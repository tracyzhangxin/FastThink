<?php
namespace Common\Lib;
	
class Logs {
	
	private $controllerName, $actionName, $params;

	public function test() {
		$mMember = D('Logs');
		$params = I('get.');
		echo ACTION_NAME;
		print_r($params);
	}
	
	public function addLog($controllerName, $actionName, $params) {
		$mLog = D('Log');
		$mLogRules = D('LogRules');
		
		$data = $mLogRules->where( "controller_name='$controllerName' AND action_name='$actionName'" )->select();
		foreach($data as $k => $v) {
			$allMatched = true;
			if( $v['params'] ) {
				if( !empty($params) ) {
					$arr = explode(',', $v['params']);
					foreach($arr as $i => $j) {
						list($paramName, $paramVal) = explode(':', $j);
						if( isset($params[$paramName]) && $params[$paramName] != $paramVal ) {
							$allMatched = false;
							break;
						}
					}
				}else {
					$allMatched = false;
				}
			}
			if($allMatched) {
				//$logFormat = str_replace(, , );
				//echo 'Writing log! => Matched id: ' . $v['id'] . ' => Content: ' .  . '<br />';
			}
		}
		
	}
}