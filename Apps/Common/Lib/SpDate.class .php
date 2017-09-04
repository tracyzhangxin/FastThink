<?php
namespace Common\Lib;

class SpDate {
	
	public function getLastNDay($date, $n = 1, $type = 'before') {
		if(empty($date)) {
			$day = date("Y-m-d", strtotime("-$n day"));
		}else {
			$arr = explode('-', $date);
			$year = $arr[0];
			$month = $arr[1];
			$day = $arr[2];
			$unixtime = mktime(0, 0, 0, $month, $day, $year) - 86400 * $n;
			$day = date('Y-m-d', $unixtime);
		}
		return $day;
	}
	
}