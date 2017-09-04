<?php
namespace Common\Lib;

class SpParams {
	
	public $filterParams = NULL;
	protected $allowedFilterParams = NULL;
	
	public function __construct($allowedFilterParams) {
		$this->allowedFilterParams = $allowedFilterParams;
	}
	
	public function get() {
		if($this->allowedFilterParams) {
			$this->filterParams = array();
			foreach($this->allowedFilterParams as $v) {
				if(strpos($v, ':') !== FALSE) {
					list($v, $tmp) = explode(':', $v);
					$val = trim(I($v));
					if(!$val) $val = $tmp;
				}else {
					$val = trim(I($v));
				}
				//if($_GET['name'] == 'ltotal') echo 's: ' . "$v, $val" . '<br />';
				if($val || $val != '') $this->filterParams[$v] = $val;
			}
			return $this->filterParams;
		}
	}
	
	public function makeStringConditions($params = array()) {
		$str = '';
		if(empty($params)) {
			$params = $this->filterParams? $this->filterParams : $this->get();
		}
		foreach($params as $k => $v) {
			$str .= "$k:$v;";
		}
		return $str;
	}
	
}