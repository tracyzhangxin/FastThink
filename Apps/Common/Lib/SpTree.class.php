<?php
namespace Common\Lib;
	
class SpTree {

	public $rows;
	private $selfMarker = 'id';
	private $parentMarker = 'pid';
	private $childMarker = 'children';
	private $childrenArr = array();
	
	public function init($rows) {
		$this->rows = $rows;
	}

	public function setSelfMarker($selfMarker) {
		$this->selfMarker = $selfMarker;
	}

	public function setParentMarker($parentMarker) {
		$this->parentMarker = $parentMarker;
	}

	public function setChildMarker($childMarker) {
		$this->childMarker = $childMarker;
	}

	private function findChild(&$arr, $id) {
		$childs = array();
		foreach ($arr as $k => $v){
			if($v[$this->parentMarker] == $id) {
				$childs[] = $v;
			}
			 
		} 
		return $childs;
	}

	public function buildTree($rootId, $level = 0) {
		$level += 1;
		$childs = $this->findChild($this->rows, $rootId);
		if(empty($childs)){
			return null;
		}
		foreach ($childs as $k => $v){
			$this->childrenArr[] = $v['id'];
			$rescurTree = $this->buildTree($v['id'], $level);
			if( null != $rescurTree){ 
				$childs[$k][$this->childMarker] = $rescurTree;
			}else {
				$childs[$k]['lastLv'] = true;
				//$level = 0;
			}
			$childs[$k]['level'] = $level;
		}
		return $childs;
	}

	public function getChildrenArr() {
		return $this->childrenArr;
	}

	public function getParent() {
		;
	}
}